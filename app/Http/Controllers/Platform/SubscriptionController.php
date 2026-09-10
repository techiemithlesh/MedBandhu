<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Plan;
use App\Models\PlatformInvoice;
use App\Models\Subscription;
use App\Support\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subs) {}

    public function assign(Request $request, Hospital $hospital): RedirectResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')],
            'licence_type' => ['required', Rule::in(['subscription', 'perpetual'])],
            'billing_cycle' => ['required', Rule::in(['monthly', 'half_yearly', 'yearly'])],
            'branches' => ['required', 'integer', 'min:1', 'max:200'],
            'trial' => ['boolean'],
            'amc_valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->subs->subscribe(Hospital::withoutGlobalScopes()->findOrFail($hospital->id), Plan::findOrFail($data['plan_id']), [
            'licence_type' => $data['licence_type'],
            'billing_cycle' => $data['billing_cycle'],
            'branches' => $data['branches'],
            'trial' => $request->boolean('trial'),
            'amc_valid_until' => $data['amc_valid_until'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Subscription updated.');
    }

    public function suspend(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->subs->suspend($subscription, $request->string('reason')->toString() ?: null);

        return back()->with('status', 'Subscription suspended.');
    }

    public function resume(Subscription $subscription): RedirectResponse
    {
        $this->subs->resume($subscription);

        return back()->with('status', 'Subscription resumed.');
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $this->subs->cancel($subscription);

        return back()->with('status', 'Subscription cancelled.');
    }

    public function raiseInvoice(Request $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'due_days' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        $invoice = $this->subs->raiseInvoice($subscription->load('plan', 'hospital'), array_filter($data, fn ($v) => $v !== null));

        return back()->with('status', "Invoice {$invoice->number} raised.");
    }

    public function recordPayment(Request $request, PlatformInvoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$invoice->balance],
            'method' => ['required', Rule::in(['cash', 'upi', 'bank_transfer', 'cheque', 'adjustment'])],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->subs->recordPayment($invoice->load('subscription'), $data);

        return back()->with('status', 'Payment recorded.');
    }

    public function voidInvoice(PlatformInvoice $invoice): RedirectResponse
    {
        abort_if($invoice->amount_paid > 0, 422, 'Invoice has payments.');
        $invoice->update(['status' => 'void']);

        return back()->with('status', 'Invoice voided.');
    }
}
