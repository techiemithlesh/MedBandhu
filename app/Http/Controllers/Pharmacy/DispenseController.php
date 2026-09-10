<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PharmacySale;
use App\Support\PharmacyStock;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DispenseController extends Controller
{
    public function __construct(protected PharmacyStock $stock) {}

    public function index(Tenancy $tenancy): View
    {
        $branchId = $tenancy->branchId();

        // Recent consultations with a prescription that hasn't been dispensed yet.
        $dispensedConsultIds = PharmacySale::whereNotNull('consultation_id')->pluck('consultation_id')->all();

        $pending = Consultation::query()
            ->whereHas('items')
            ->whereNotIn('id', $dispensedConsultIds)
            ->with(['patient', 'doctor', 'items'])
            ->latest()
            ->limit(25)
            ->get();

        return view('pharmacy.dispense.index', [
            'pending' => $pending,
            'sales' => PharmacySale::with(['patient', 'prescriber'])
                ->where('branch_id', $branchId)
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $consultation = null;
        $lines = [];

        if ($request->filled('consultation')) {
            $consultation = Consultation::with(['patient', 'doctor', 'items'])->findOrFail($request->integer('consultation'));
            $lines = $consultation->items->map(fn ($i) => [
                'hint' => trim($i->drug_name.' '.$i->strength.' '.$i->dosage),
                'medicine_id' => '', 'quantity' => 1, 'sale_price' => 0, 'discount_percent' => 0,
            ])->values()->all();
        }

        return view('pharmacy.dispense.create', [
            'consultation' => $consultation,
            'patient' => $consultation?->patient
                ?? ($request->filled('patient') ? Patient::find($request->integer('patient')) : null),
            'lines' => $lines,
        ]);
    }

    public function store(Request $request, Tenancy $tenancy): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => ['nullable', Rule::exists('patients', 'id')],
            'consultation_id' => ['nullable', Rule::exists('consultations', 'id')],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['required', Rule::in(['cash', 'card', 'upi', 'credit'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', Rule::exists('medicines', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $branchId = $tenancy->branchId();

        $sale = DB::transaction(function () use ($data, $branchId) {
            $prescriber = null;
            if (! empty($data['consultation_id'])) {
                $prescriber = Consultation::find($data['consultation_id'])?->doctor_id;
            }

            $sale = PharmacySale::create([
                'branch_id' => $branchId,
                'patient_id' => $data['patient_id'] ?? null,
                'consultation_id' => $data['consultation_id'] ?? null,
                'prescribed_by' => $prescriber,
                'customer_name' => $data['customer_name'] ?? null,
                'sale_date' => today(),
                'payment_mode' => $data['payment_mode'],
                'status' => 'completed',
            ]);

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            foreach ($data['items'] as $row) {
                $medicine = Medicine::findOrFail($row['medicine_id']);
                $qty = (int) $row['quantity'];
                $disc = (float) ($row['discount_percent'] ?? 0);

                $plan = $this->stock->allocate($medicine, $branchId, $qty);

                // One sale line per batch allocation (keeps batch traceability).
                foreach ($plan as $alloc) {
                    $batch = $alloc['batch'];
                    $take = $alloc['quantity'];
                    $gross = $take * (float) $batch->sale_price;
                    $lineDiscount = round($gross * $disc / 100, 2);
                    $lineTotal = round($gross - $lineDiscount, 2);
                    $lineTax = round($lineTotal * (float) $medicine->gst_rate / (100 + (float) $medicine->gst_rate), 2);

                    $sale->items()->create([
                        'medicine_id' => $medicine->id,
                        'medicine_batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'quantity' => $take,
                        'mrp' => $batch->mrp,
                        'sale_price' => $batch->sale_price,
                        'discount_percent' => $disc,
                        'gst_rate' => $medicine->gst_rate,
                        'line_total' => $lineTotal,
                    ]);

                    $subtotal += $gross;
                    $discountTotal += $lineDiscount;
                    $taxTotal += $lineTax;
                }

                $this->stock->dispense($plan, $sale, 'Bill '.$sale->sale_no);
            }

            $net = $subtotal - $discountTotal;
            $rounded = round($net);

            $sale->update([
                'subtotal' => round($subtotal, 2),
                'discount' => round($discountTotal, 2),
                'tax' => round($taxTotal, 2),
                'round_off' => round($rounded - $net, 2),
                'total' => $rounded,
                'amount_paid' => $rounded,
            ]);

            return $sale;
        });

        return redirect()->route('pharmacy.dispense.show', $sale)
            ->with('status', "Dispensed — bill {$sale->sale_no}, ₹".number_format($sale->total, 2));
    }

    public function show(PharmacySale $sale): View
    {
        $sale->load(['patient', 'prescriber', 'consultation', 'items.medicine']);

        return view('pharmacy.dispense.show', compact('sale'));
    }
}
