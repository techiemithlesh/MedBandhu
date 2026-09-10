<?php

namespace App\Support;

use App\Mail\PlatformPaymentReceipt;
use App\Models\Hospital;
use App\Models\Plan;
use App\Models\PlatformInvoice;
use App\Models\PlatformPayment;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubscriptionService
{
    public const GST_RATE = 18.0;

    /** Months in one billing cycle. */
    public static function cycleMonths(string $cycle): int
    {
        return match ($cycle) {
            'monthly' => 1,
            'half_yearly' => 6,
            default => 12,
        };
    }

    /** Create or replace a hospital's subscription on a plan. */
    public function subscribe(Hospital $hospital, Plan $plan, array $opts = []): Subscription
    {
        $cycle = $opts['billing_cycle'] ?? 'yearly';
        $branches = max(1, (int) ($opts['branches'] ?? max(1, $hospital->branchesUsed())));
        $licenceType = $opts['licence_type'] ?? 'subscription';
        $startTrial = $opts['trial'] ?? ($licenceType === 'subscription' && $plan->trial_days > 0);
        $today = CarbonImmutable::today();

        return DB::transaction(function () use ($hospital, $plan, $cycle, $branches, $licenceType, $startTrial, $today, $opts) {
            // Perpetual: the recurring amount is the yearly AMC, not a subscription fee.
            $amount = $licenceType === 'perpetual'
                ? (float) ($plan->price_amc ?? $plan->priceFor('yearly', $branches))
                : $plan->priceFor($cycle, $branches);

            $sub = Subscription::updateOrCreate(
                ['hospital_id' => $hospital->id],
                [
                    'plan_id' => $plan->id,
                    'licence_type' => $licenceType,
                    'billing_cycle' => $licenceType === 'perpetual' ? 'yearly' : $cycle,
                    'branches' => $branches,
                    'amount' => $amount,
                    'grace_days' => $opts['grace_days'] ?? 7,
                    'cancel_at_period_end' => false,
                    'notes' => $opts['notes'] ?? null,
                ]
            );

            if ($licenceType === 'perpetual') {
                $sub->update([
                    'status' => 'active',
                    'trial_ends_at' => null,
                    'current_period_start' => null,
                    'current_period_end' => null,
                    'amc_valid_until' => $opts['amc_valid_until'] ?? $today->addYear(),
                    'licence_key' => $sub->licence_key ?: strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4)),
                ]);
            } elseif ($startTrial) {
                $sub->update([
                    'status' => 'trialing',
                    'trial_ends_at' => $today->addDays($plan->trial_days),
                    'current_period_start' => null,
                    'current_period_end' => null,
                ]);
            } else {
                $sub->update([
                    'status' => 'active',
                    'trial_ends_at' => null,
                    'current_period_start' => $today,
                    'current_period_end' => $today->addMonthsNoOverflow(self::cycleMonths($cycle)),
                ]);
            }

            $this->syncHospital($hospital->fresh('subscription'));

            return $sub->fresh();
        });
    }

    public function suspend(Subscription $sub, ?string $reason = null): void
    {
        $sub->update(['status' => 'suspended', 'notes' => trim(($sub->notes ? $sub->notes.' | ' : '').'Suspended: '.$reason)]);
        $this->syncHospital($sub->hospital);
    }

    public function resume(Subscription $sub): void
    {
        $sub->update(['status' => $sub->isPerpetual() || $sub->current_period_end ? 'active' : 'trialing']);
        $this->syncHospital($sub->hospital);
    }

    public function cancel(Subscription $sub): void
    {
        $sub->update(['status' => 'cancelled', 'cancel_at_period_end' => false]);
        $this->syncHospital($sub->hospital);
    }

    /** Raise a platform invoice for the next period (or an ad-hoc amount). */
    public function raiseInvoice(Subscription $sub, array $opts = []): PlatformInvoice
    {
        $subtotal = round((float) ($opts['amount'] ?? $sub->amount), 2);
        $tax = round($subtotal * self::GST_RATE / 100, 2);
        $cycleMonths = self::cycleMonths($sub->billing_cycle);

        $start = CarbonImmutable::parse($opts['period_start'] ?? $sub->current_period_end ?? CarbonImmutable::today());
        $end = $start->addMonthsNoOverflow($cycleMonths);

        $cycleLabel = str_replace('_', '-', $sub->billing_cycle);

        return PlatformInvoice::create([
            'hospital_id' => $sub->hospital_id,
            'subscription_id' => $sub->id,
            'description' => $opts['description']
                ?? ($sub->plan->name.' — '.ucfirst($cycleLabel).' ('.$sub->branches.' branch'.($sub->branches > 1 ? 'es' : '').')'),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'due_date' => CarbonImmutable::today()->addDays($opts['due_days'] ?? 7)->toDateString(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'status' => 'sent',
        ]);
    }

    /** Record a payment against a platform invoice and extend the subscription. */
    public function recordPayment(PlatformInvoice $invoice, array $data): PlatformPayment
    {
        $payment = DB::transaction(function () use ($invoice, $data) {
            $amount = round((float) $data['amount'], 2);

            $payment = $invoice->payments()->create([
                'hospital_id' => $invoice->hospital_id,
                'amount' => $amount,
                'method' => $data['method'],
                'gateway_order_id' => $data['gateway_order_id'] ?? null,
                'gateway_payment_id' => $data['gateway_payment_id'] ?? null,
                'gateway_signature' => $data['gateway_signature'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $paid = (float) $invoice->payments()->sum('amount');
            $invoice->update([
                'amount_paid' => $paid,
                'status' => $paid + 0.01 >= (float) $invoice->total ? 'paid' : 'sent',
                'paid_at' => $paid + 0.01 >= (float) $invoice->total ? now() : null,
            ]);

            if ($invoice->fresh()->status === 'paid' && $invoice->subscription) {
                $this->extend($invoice->subscription, $invoice);
            }

            return $payment;
        });

        $this->emailReceipt($payment);

        return $payment;
    }

    /** Email the hospital a receipt (and, if this cleared the invoice, an activation note). */
    protected function emailReceipt(PlatformPayment $payment): void
    {
        try {
            $payment->loadMissing('invoice.subscription.plan', 'hospital.users');

            $recipients = collect([$payment->hospital->email])
                ->merge($payment->hospital->users->pluck('email'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($recipients) {
                Mail::to($recipients)->send(new PlatformPaymentReceipt($payment));
            }
        } catch (\Throwable $e) {
            report($e); // never let a mail hiccup fail the payment
        }
    }

    protected function extend(Subscription $sub, PlatformInvoice $invoice): void
    {
        if ($sub->isPerpetual()) {
            $base = $sub->amc_valid_until && $sub->amc_valid_until->isFuture()
                ? CarbonImmutable::parse($sub->amc_valid_until) : CarbonImmutable::today();
            $sub->update(['status' => 'active', 'amc_valid_until' => $base->addYear()->toDateString()]);
        } else {
            $start = $invoice->period_start
                ? CarbonImmutable::parse($invoice->period_start)
                : ($sub->current_period_end && $sub->current_period_end->isFuture()
                    ? CarbonImmutable::parse($sub->current_period_end) : CarbonImmutable::today());
            $sub->update([
                'status' => 'active',
                'current_period_start' => $start->toDateString(),
                'current_period_end' => $start->addMonthsNoOverflow(self::cycleMonths($sub->billing_cycle))->toDateString(),
                'trial_ends_at' => null,
            ]);
        }

        $this->syncHospital($sub->hospital);
    }

    /** Push the derived access flag + branch limit onto the hospital (hot path). */
    public function syncHospital(Hospital $hospital): void
    {
        $sub = $hospital->subscription()->with('plan')->first();

        if (! $sub) {
            $hospital->update(['access_status' => 'active', 'branch_limit' => 1]);

            return;
        }

        $hospital->update([
            'access_status' => $sub->computeAccessStatus(),
            'branch_limit' => max($sub->branches, $sub->plan->branch_limit),
            'subscription_status' => match ($sub->status) {
                'suspended' => 'suspended',
                'cancelled' => 'cancelled',
                default => 'active',
            },
        ]);
    }

    /** Daily maintenance: move lapsed trials/periods forward. */
    public function refreshAll(): int
    {
        $touched = 0;
        Subscription::with('hospital', 'plan')
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->each(function (Subscription $sub) use (&$touched) {
                $access = $sub->computeAccessStatus();
                $newStatus = match (true) {
                    $sub->status === 'trialing' && $access !== 'active' => 'past_due',
                    $sub->status === 'active' && $access === 'restricted' => 'past_due',
                    $sub->status === 'active' && $access === 'blocked' => 'past_due',
                    default => $sub->status,
                };
                if ($newStatus !== $sub->status || $sub->hospital->access_status !== $access) {
                    $sub->update(['status' => $newStatus]);
                    $this->syncHospital($sub->hospital);
                    $touched++;
                }
            });

        return $touched;
    }
}
