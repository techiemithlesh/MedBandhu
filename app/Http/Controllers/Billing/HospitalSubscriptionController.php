<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Mail\PlatformPaymentReceipt;
use App\Models\PlatformInvoice;
use App\Models\PlatformPayment;
use App\Support\RazorpayGateway;
use App\Support\SubscriptionService;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalSubscriptionController extends Controller
{
    public function __construct(
        protected Tenancy $tenancy,
        protected RazorpayGateway $razorpay,
        protected SubscriptionService $subs,
    ) {}

    public function show(): View
    {
        $hospital = $this->tenancy->hospital()->load('subscription.plan');

        return view('billing.subscription.show', [
            'hospital' => $hospital,
            'subscription' => $hospital->subscription,
            'invoices' => $hospital->platformInvoices()->with('payments')->latest('id')->get(),
            'razorpayEnabled' => $this->razorpay->enabled(),
            'payTo' => array_filter(config('hms.pay_to', [])),
        ]);
    }

    /** On-screen / printable receipt for one of this hospital's payments. */
    public function receipt(PlatformPayment $payment): View
    {
        abort_unless($payment->hospital_id === $this->tenancy->hospitalId(), 403);

        return view('emails.platform-payment-receipt', PlatformPaymentReceipt::viewData($payment));
    }

    /** Start a Razorpay checkout for a pending invoice. */
    public function pay(PlatformInvoice $invoice): View|RedirectResponse
    {
        abort_unless($invoice->hospital_id === $this->tenancy->hospitalId(), 403);
        abort_if($invoice->status === 'paid' || $invoice->balance <= 0, 422, 'Invoice already settled.');

        if (! $this->razorpay->enabled()) {
            return redirect()->route('billing.subscription.show')
                ->with('error', 'Online payment isn\'t configured. Please pay by bank transfer / UPI and share the reference with support.');
        }

        $order = $this->razorpay->createOrder($invoice);

        return view('billing.subscription.pay', [
            'invoice' => $invoice,
            'order' => $order,
            'keyId' => $this->razorpay->keyId(),
            'hospital' => $this->tenancy->hospital(),
        ]);
    }

    /** Razorpay checkout callback (redirect from the browser after payment). */
    public function callback(Request $request, PlatformInvoice $invoice): RedirectResponse
    {
        abort_unless($invoice->hospital_id === $this->tenancy->hospitalId(), 403);

        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        if (! $this->razorpay->verifyPayment($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature'])) {
            return redirect()->route('billing.subscription.show')->with('error', 'Payment could not be verified. If money was deducted, contact support.');
        }

        // Idempotency: don't double-record the same gateway payment.
        if (! $invoice->payments()->where('gateway_payment_id', $data['razorpay_payment_id'])->exists()) {
            $this->subs->recordPayment($invoice->load('subscription'), [
                'amount' => $invoice->balance,
                'method' => 'razorpay',
                'gateway_order_id' => $data['razorpay_order_id'],
                'gateway_payment_id' => $data['razorpay_payment_id'],
                'gateway_signature' => $data['razorpay_signature'],
            ]);
        }

        return redirect()->route('billing.subscription.show')->with('status', 'Payment received — thank you!');
    }
}
