<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\BillingService;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    public function index(Request $request, Tenancy $tenancy): View
    {
        $from = $request->filled('from') ? CarbonImmutable::parse($request->date('from')) : CarbonImmutable::today();
        $to = $request->filled('to') ? CarbonImmutable::parse($request->date('to')) : $from;

        $payments = Payment::query()
            ->with(['invoice', 'patient', 'receiver'])
            ->where('branch_id', $tenancy->branchId())
            ->whereBetween('payment_date', [$from->startOfDay(), $to->endOfDay()])
            ->when($request->filled('mode'), fn ($q) => $q->where('mode', $request->string('mode')))
            ->latest('id')
            ->get();

        $byMode = $payments->groupBy('mode')->map(fn ($g) => $g->sum('signed_amount'));

        return view('billing.payments.index', [
            'payments' => $payments,
            'from' => $from,
            'to' => $to,
            'byMode' => $byMode,
            'collected' => $payments->where('type', 'payment')->sum('amount'),
            'refunded' => $payments->where('type', 'refund')->sum('amount'),
            'net' => $payments->sum('signed_amount'),
        ]);
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'mode' => ['required', Rule::in(Payment::MODES)],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->billing->recordPayment($invoice, $data);

        return back()->with('status', 'Payment recorded.');
    }

    public function refund(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'mode' => ['required', Rule::in(Payment::MODES)],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->billing->refund($invoice, $data);

        return back()->with('status', 'Refund recorded.');
    }

    public function receipt(Payment $payment): View
    {
        $payment->load(['invoice.items', 'patient', 'receiver']);

        return view('billing.payments.receipt', compact('payment'));
    }
}
