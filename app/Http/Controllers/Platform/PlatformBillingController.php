<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Mail\PlatformPaymentReceipt;
use App\Models\PlatformInvoice;
use App\Models\PlatformPayment;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformBillingController extends Controller
{
    public function index(): View
    {
        $active = Subscription::with('plan', 'hospital')->whereIn('status', ['trialing', 'active', 'past_due'])->get();

        // normalise every subscription's recurring amount to a monthly figure
        $mrr = $active->where('status', '!=', 'trialing')->sum(function (Subscription $s) {
            $monthly = $s->billing_cycle === 'yearly' ? (float) $s->amount / 12 : (float) $s->amount;

            return $s->isPerpetual() ? 0 : $monthly;
        });

        $month = CarbonImmutable::today()->startOfMonth();

        return view('platform.billing.index', [
            'mrr' => round($mrr, 2),
            'counts' => [
                'active' => $active->where('status', 'active')->count(),
                'trialing' => $active->where('status', 'trialing')->count(),
                'past_due' => $active->where('status', 'past_due')->count(),
                'perpetual' => Subscription::where('licence_type', 'perpetual')->count(),
            ],
            'collectedThisMonth' => PlatformPayment::where('paid_at', '>=', $month)->sum('amount'),
            'outstanding' => PlatformInvoice::where('status', 'sent')->selectRaw('COALESCE(SUM(total - amount_paid),0) b')->value('b'),
            'subscriptions' => Subscription::with('plan', 'hospital')->orderByDesc('id')->get(),
        ]);
    }

    public function invoices(Request $request): View
    {
        return view('platform.billing.invoices', [
            'invoices' => PlatformInvoice::with('hospital')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->boolean('overdue'), fn ($q) => $q->where('status', 'sent')->whereDate('due_date', '<', today()))
                ->latest('id')->paginate(30)->withQueryString(),
        ]);
    }

    public function invoice(PlatformInvoice $invoice): View
    {
        $invoice->load('hospital', 'subscription.plan', 'payments');

        return view('platform.billing.invoice', compact('invoice'));
    }

    public function receipt(PlatformPayment $payment): View
    {
        return view('emails.platform-payment-receipt', PlatformPaymentReceipt::viewData($payment));
    }
}
