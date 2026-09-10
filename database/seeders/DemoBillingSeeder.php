<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Hospital;
use App\Models\IpdAdmission;
use App\Models\Patient;
use App\Models\Service;
use App\Support\BillingService;
use App\Support\Tenancy;
use Illuminate\Database\Seeder;

class DemoBillingSeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::where('code', 'DEMO')->first();
        if (! $hospital || Service::where('hospital_id', $hospital->id)->exists()) {
            $this->command->warn('DEMO hospital missing or billing already seeded — skipping.');

            return;
        }

        app(Tenancy::class)->setHospital($hospital);
        setPermissionsTeamId($hospital->id);
        $main = $hospital->branches()->where('code', 'MAIN')->first();
        app(Tenancy::class)->setBranch($main);

        $services = [
            ['General Consultation', 'CONS-GEN', 'consultation', 500, 0],
            ['Specialist Consultation', 'CONS-SPL', 'consultation', 800, 0],
            ['ECG', 'INV-ECG', 'investigation', 300, 12],
            ['X-Ray Chest', 'INV-XRAY', 'investigation', 450, 12],
            ['Complete Blood Count', 'INV-CBC', 'investigation', 350, 12],
            ['Dressing (minor)', 'PROC-DRS', 'procedure', 200, 12],
            ['Nebulisation', 'PROC-NEB', 'nursing', 150, 12],
            ['IV Cannulation', 'PROC-IV', 'nursing', 100, 12],
            ['Nursing Charges / day', 'ROOM-NUR', 'nursing', 400, 0],
            ['Doctor Visit (IPD) / day', 'ROOM-DRV', 'room', 500, 0],
        ];
        foreach ($services as [$name, $code, $cat, $price, $gst]) {
            Service::create(['name' => $name, 'code' => $code, 'category' => $cat, 'price' => $price, 'gst_rate' => $gst]);
        }

        $billing = app(BillingService::class);

        // 1) OPD bill — completed appointment, finalized + paid in full
        $appt = Appointment::where('status', 'completed')->whereDoesntHave('invoice')->with(['patient', 'doctor'])->first();
        if ($appt) {
            $invoice = $billing->opdInvoiceFor($appt);
            $ecg = Service::where('code', 'INV-ECG')->first();
            $invoice->items()->create([
                'service_id' => $ecg->id, 'item_type' => 'service', 'description' => $ecg->name,
                'quantity' => 1, 'unit_price' => $ecg->price, 'gst_rate' => $ecg->gst_rate, 'sort_order' => 5,
            ]);
            $billing->recalculate($invoice);
            $billing->finalize($invoice);
            $billing->recordPayment($invoice, ['amount' => $invoice->fresh()->balance, 'mode' => 'upi']);
        }

        // 2) IPD bill — from an admission's charges, finalized, partially paid
        $adm = IpdAdmission::whereDoesntHave('invoices')
            ->whereHas('charges')
            ->with(['patient', 'charges'])
            ->orderByRaw("field(status,'discharged') desc")
            ->first();
        if (! $adm) {
            $adm = IpdAdmission::whereDoesntHave('invoices')->with(['patient', 'charges', 'bedMovements.ward'])->first();
            if ($adm) {
                app(\App\Support\IpdService::class)->generateBedCharges($adm);
                $adm->load('charges');
            }
        }
        if ($adm) {
            $invoice = $billing->ipdInvoiceFor($adm, true);
            foreach (['ROOM-NUR' => $adm->days_admitted, 'ROOM-DRV' => $adm->days_admitted, 'PROC-DRS' => 2] as $code => $qty) {
                $svc = Service::where('code', $code)->first();
                $invoice->items()->create([
                    'service_id' => $svc->id, 'item_type' => 'service', 'description' => $svc->name,
                    'quantity' => $qty, 'unit_price' => $svc->price, 'gst_rate' => $svc->gst_rate,
                    'sort_order' => $invoice->items()->max('sort_order') + 1,
                ]);
            }
            $invoice->update(['discount' => 500]);
            $billing->recalculate($invoice);
            $billing->finalize($invoice);
            $billing->recordPayment($invoice, ['amount' => round($invoice->fresh()->total * 0.6), 'mode' => 'card']);
        }

        // 3) General invoice — outstanding (finalized, unpaid)
        $patient = Patient::whereDoesntHave('invoices')->first();
        if ($patient) {
            $invoice = $billing->openInvoice($patient, ['type' => 'general', 'notes' => 'Health check package']);
            foreach (['INV-CBC', 'INV-XRAY', 'CONS-GEN'] as $code) {
                $svc = Service::where('code', $code)->first();
                $invoice->items()->create([
                    'service_id' => $svc->id, 'item_type' => $svc->category === 'consultation' ? 'consultation' : 'service',
                    'description' => $svc->name, 'quantity' => 1, 'unit_price' => $svc->price, 'gst_rate' => $svc->gst_rate,
                ]);
            }
            $billing->recalculate($invoice);
            $billing->finalize($invoice);
        }

        app(Tenancy::class)->forget();
        $this->command->info('Demo billing: 10 services, OPD paid bill, IPD partial bill, 1 outstanding invoice.');
    }
}
