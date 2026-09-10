<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\IpdAdmission;
use App\Models\Patient;
use App\Models\PharmacySale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /** Recalculate an invoice's money fields from its items + payments. */
    public function recalculate(Invoice $invoice): Invoice
    {
        $invoice->load(['items', 'payments']);   // force-refresh, not loadMissing

        $subtotal = 0.0;
        $tax = 0.0;
        foreach ($invoice->items as $item) {
            $subtotal += (float) $item->line_total;
            // GST is treated as inclusive in line_total; extract for records.
            $tax += (float) $item->line_total * (float) $item->gst_rate / (100 + (float) $item->gst_rate);
        }

        $afterDiscount = max(0, $subtotal - (float) $invoice->discount);
        $rounded = round($afterDiscount);

        $paid = $invoice->payments->sum('signed_amount');

        $status = $invoice->status;
        if (! in_array($status, ['cancelled', 'draft'], true)) {
            if ($paid <= 0) {
                $status = 'finalized';
            } elseif ($paid + 0.01 >= $rounded) {
                $status = 'paid';
            } else {
                $status = 'partially_paid';
            }
        }

        $invoice->forceFill([
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'round_off' => round($rounded - $afterDiscount, 2),
            'total' => $rounded,
            'amount_paid' => round($paid, 2),
            'balance' => round($rounded - $paid, 2),
            'status' => $status,
        ])->save();

        return $invoice;
    }

    public function openInvoice(Patient $patient, array $attributes): Invoice
    {
        return Invoice::create([
            'branch_id' => app(Tenancy::class)->branchId(),
            'patient_id' => $patient->id,
            'status' => 'draft',
            ...$attributes,
        ]);
    }

    /** Create/return the draft OPD invoice for an appointment, seeded with the consult fee. */
    public function opdInvoiceFor(Appointment $appointment): Invoice
    {
        if ($appointment->invoice) {
            return $appointment->invoice;
        }

        return DB::transaction(function () use ($appointment) {
            $invoice = $this->openInvoice($appointment->patient, [
                'type' => 'opd',
                'appointment_id' => $appointment->id,
            ]);

            $fee = (float) $appointment->consultation_fee;
            if ($fee > 0) {
                $invoice->items()->create([
                    'item_type' => 'consultation',
                    'description' => 'Consultation — '.$appointment->doctor->full_name
                        .($appointment->type === 'followup' ? ' (follow-up)' : ''),
                    'quantity' => 1,
                    'unit_price' => $fee,
                    'gst_rate' => 0,
                ]);
            }

            return $this->recalculate($invoice);
        });
    }

    /** Build a draft IPD bill from the admission's charges (+ optional credit pharmacy). */
    public function ipdInvoiceFor(IpdAdmission $admission, bool $includePharmacy = false): Invoice
    {
        return DB::transaction(function () use ($admission, $includePharmacy) {
            $invoice = $admission->invoices()->where('status', 'draft')->first()
                ?? $this->openInvoice($admission->patient, [
                    'type' => 'ipd',
                    'ipd_admission_id' => $admission->id,
                ]);

            $invoice->items()->delete();

            $sort = 0;
            foreach ($admission->charges as $charge) {
                $invoice->items()->create([
                    'item_type' => $charge->type === 'bed' ? 'bed' : 'procedure',
                    'source_type' => $charge->getMorphClass(),
                    'source_id' => $charge->id,
                    'description' => $charge->description,
                    'quantity' => $charge->quantity,
                    'unit_price' => $charge->unit_price,
                    'gst_rate' => 0,
                    'sort_order' => $sort++,
                ]);
            }

            if ($includePharmacy) {
                $sales = PharmacySale::completed()
                    ->where('patient_id', $admission->patient_id)
                    ->where('payment_mode', 'credit')
                    ->whereDate('sale_date', '>=', $admission->admitted_at->toDateString())
                    ->get();

                foreach ($sales as $sale) {
                    $invoice->items()->create([
                        'item_type' => 'pharmacy',
                        'source_type' => $sale->getMorphClass(),
                        'source_id' => $sale->id,
                        'description' => 'Pharmacy bill '.$sale->sale_no,
                        'quantity' => 1,
                        'unit_price' => $sale->total,
                        'gst_rate' => 0,
                        'sort_order' => $sort++,
                    ]);
                }
            }

            return $this->recalculate($invoice);
        });
    }

    public function finalize(Invoice $invoice): Invoice
    {
        abort_unless($invoice->status === 'draft', 422, 'Only draft invoices can be finalized.');

        if ($invoice->items()->count() === 0) {
            throw ValidationException::withMessages(['invoice' => 'Add at least one line before finalizing.']);
        }

        $invoice->forceFill([
            'status' => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => auth()->id(),
        ])->save();

        // Mark a linked OPD appointment as fee-collected once fully paid (below).
        return $this->recalculate($invoice);
    }

    public function recordPayment(Invoice $invoice, array $data): void
    {
        $invoice->refresh();
        abort_if(in_array($invoice->status, ['draft', 'cancelled'], true), 422, 'Finalize the invoice first.');

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be positive.']);
        }
        if ($amount - 0.01 > (float) $invoice->balance) {
            throw ValidationException::withMessages(['amount' => 'Amount exceeds the outstanding balance (₹'.number_format($invoice->balance, 2).').']);
        }

        DB::transaction(function () use ($invoice, $data, $amount) {
            $invoice->payments()->create([
                'branch_id' => $invoice->branch_id,
                'patient_id' => $invoice->patient_id,
                'type' => 'payment',
                'amount' => $amount,
                'mode' => $data['mode'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recalculate($invoice);

            if ($invoice->fresh()->status === 'paid' && $invoice->appointment_id) {
                $invoice->appointment?->update(['fee_paid' => true]);
            }
        });
    }

    public function refund(Invoice $invoice, array $data): void
    {
        $invoice->refresh();
        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0 || $amount - 0.01 > (float) $invoice->amount_paid) {
            throw ValidationException::withMessages(['amount' => 'Refund cannot exceed the amount paid (₹'.number_format($invoice->amount_paid, 2).').']);
        }

        DB::transaction(function () use ($invoice, $data, $amount) {
            $invoice->payments()->create([
                'branch_id' => $invoice->branch_id,
                'patient_id' => $invoice->patient_id,
                'type' => 'refund',
                'amount' => $amount,
                'mode' => $data['mode'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? 'Refund',
            ]);

            $invoice->refresh();
            $this->recalculate($invoice);

            if ((float) $invoice->fresh()->amount_paid <= 0) {
                $invoice->update(['status' => 'refunded']);
            }
        });
    }
}
