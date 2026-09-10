<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\IpdAdmission;
use App\Models\Patient;
use App\Models\Service;
use App\Support\BillingService;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    public function index(Request $request, Tenancy $tenancy): View
    {
        $invoices = Invoice::query()
            ->with('patient')
            ->where('branch_id', $tenancy->branchId())
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('invoice_no', 'like', '%'.$request->string('q').'%')
                ->orWhereHas('patient', fn ($p) => $p->search($request->string('q')))))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('billing.invoices.index', compact('invoices'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        // Shortcut entrypoints: build the right draft and jump to it.
        if ($request->filled('appointment')) {
            $appt = Appointment::with(['patient', 'doctor'])->findOrFail($request->integer('appointment'));
            $invoice = $this->billing->opdInvoiceFor($appt);

            return redirect()->route('billing.invoices.show', $invoice);
        }

        if ($request->filled('admission')) {
            $adm = IpdAdmission::with(['patient', 'charges'])->findOrFail($request->integer('admission'));
            $invoice = $this->billing->ipdInvoiceFor($adm, $request->boolean('pharmacy'));

            return redirect()->route('billing.invoices.show', $invoice);
        }

        return view('billing.invoices.create', [
            'patient' => $request->filled('patient') ? Patient::find($request->integer('patient')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $invoice = $this->billing->openInvoice(Patient::findOrFail($data['patient_id']), [
            'type' => 'general',
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('billing.invoices.show', $invoice)->with('status', "Draft {$invoice->invoice_no} created.");
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'items.service', 'payments.receiver', 'appointment.doctor', 'admission']);

        return view('billing.invoices.show', [
            'invoice' => $invoice,
            'services' => Service::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->isEditable(), 422);

        $data = $request->validate([
            'discount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $invoice->update($data);
        $this->billing->recalculate($invoice);

        return back()->with('status', 'Invoice updated.');
    }

    public function addItem(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->isEditable(), 422);

        $data = $request->validate([
            'service_id' => ['nullable', Rule::exists('services', 'id')],
            'description' => ['required_without:service_id', 'nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gst_rate' => ['nullable', 'numeric', 'min:0', 'max:28'],
        ]);

        $service = ! empty($data['service_id']) ? Service::find($data['service_id']) : null;

        $invoice->items()->create([
            'service_id' => $service?->id,
            'item_type' => $service ? ($service->category === 'consultation' ? 'consultation' : 'service') : 'other',
            'description' => ($data['description'] ?? null) ?: $service?->name,
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'gst_rate' => $data['gst_rate'] ?? $service?->gst_rate ?? 0,
            'sort_order' => ($invoice->items()->max('sort_order') ?? 0) + 1,
        ]);

        $this->billing->recalculate($invoice);

        return back()->with('status', 'Item added.');
    }

    public function removeItem(Invoice $invoice, int $item): RedirectResponse
    {
        abort_unless($invoice->isEditable(), 422);

        $invoice->items()->whereKey($item)->delete();
        $this->billing->recalculate($invoice);

        return back()->with('status', 'Item removed.');
    }

    public function finalize(Invoice $invoice): RedirectResponse
    {
        $this->billing->finalize($invoice);

        return back()->with('status', "Invoice {$invoice->invoice_no} finalized.");
    }

    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->amount_paid > 0, 422, 'Refund payments before cancelling.');

        $invoice->update(['status' => 'cancelled', 'notes' => trim(($invoice->notes ? $invoice->notes.' | ' : '').'Cancelled: '.$request->string('reason'))]);

        return redirect()->route('billing.invoices.index')->with('status', "Invoice {$invoice->invoice_no} cancelled.");
    }
}
