<?php

namespace Database\Seeders;

use App\Models\Consultation;
use App\Models\DrugCategory;
use App\Models\Hospital;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\PharmacyStock;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoPharmacySeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::where('code', 'DEMO')->first();
        if (! $hospital || Medicine::where('hospital_id', $hospital->id)->exists()) {
            $this->command->warn('DEMO hospital missing or medicines already seeded — skipping.');

            return;
        }

        app(Tenancy::class)->setHospital($hospital);
        setPermissionsTeamId($hospital->id);
        $main = $hospital->branches()->where('code', 'MAIN')->first();
        app(Tenancy::class)->setBranch($main);

        $cats = collect(['Analgesic', 'Antibiotic', 'Antihistamine', 'Antacid', 'Antipyretic', 'Vitamin', 'Antihypertensive'])
            ->mapWithKeys(fn ($n) => [$n => DrugCategory::create(['name' => $n])->id]);

        $mfrs = collect(['Sun Pharma', 'Cipla', 'Dr Reddy\'s', 'Mankind', 'GSK'])
            ->mapWithKeys(fn ($n) => [$n => Manufacturer::create(['name' => $n])->id]);

        $supplier = Supplier::create([
            'name' => 'MediPlus Distributors', 'contact_person' => 'S. Prasad',
            'phone' => '9835012345', 'gstin' => '10ABCDE1234F1Z5', 'drug_license_no' => 'BR-20B-1234',
        ]);
        Supplier::create(['name' => 'HealthLine Agencies', 'phone' => '9835067890']);

        // name, strength, generic, form, category, mfr, gst, schedule, reorder
        $catalog = [
            ['Paracetamol', '650 mg', 'Paracetamol', 'tablet', 'Antipyretic', 'Mankind', 12, 'OTC', 200],
            ['Paracetamol', '500 mg', 'Paracetamol', 'tablet', 'Antipyretic', 'GSK', 12, 'OTC', 150],
            ['Cetirizine', '10 mg', 'Cetirizine', 'tablet', 'Antihistamine', 'Cipla', 12, 'none', 100],
            ['Amoxicillin', '500 mg', 'Amoxicillin', 'capsule', 'Antibiotic', 'Sun Pharma', 12, 'H', 80],
            ['Azithromycin', '500 mg', 'Azithromycin', 'tablet', 'Antibiotic', 'Cipla', 12, 'H', 60],
            ['Ibuprofen', '400 mg', 'Ibuprofen', 'tablet', 'Analgesic', 'Dr Reddy\'s', 12, 'none', 120],
            ['Pantoprazole', '40 mg', 'Pantoprazole', 'tablet', 'Antacid', 'Sun Pharma', 12, 'none', 100],
            ['Amlodipine', '5 mg', 'Amlodipine', 'tablet', 'Antihypertensive', 'Cipla', 5, 'H', 90],
            ['Metformin', '500 mg', 'Metformin', 'tablet', 'Antihypertensive', 'Mankind', 12, 'none', 100],
            ['Cough Syrup', '100 ml', 'Dextromethorphan', 'syrup', 'Analgesic', 'GSK', 12, 'none', 30],
            ['ORS', 'Sachet', 'Oral Rehydration Salts', 'sachet', 'Vitamin', 'Mankind', 12, 'OTC', 100],
            ['Vitamin C', '500 mg', 'Ascorbic Acid', 'tablet', 'Vitamin', 'Sun Pharma', 12, 'OTC', 80],
            ['Ceftriaxone', '1 g', 'Ceftriaxone', 'injection', 'Antibiotic', 'Cipla', 12, 'H', 20],
            ['Ranitidine', '150 mg', 'Ranitidine', 'tablet', 'Antacid', 'Dr Reddy\'s', 12, 'none', 60],
        ];

        $medicines = collect($catalog)->map(fn ($m) => Medicine::create([
            'name' => $m[0], 'strength' => $m[1], 'generic_name' => $m[2], 'form' => $m[3],
            'unit' => $m[3] === 'syrup' ? 'bottle' : ($m[3] === 'sachet' ? 'sachet' : ($m[3] === 'injection' ? 'vial' : 'tablet')),
            'pack_size' => in_array($m[3], ['tablet', 'capsule']) ? 10 : 1,
            'drug_category_id' => $cats[$m[4]], 'manufacturer_id' => $mfrs[$m[5]],
            'gst_rate' => $m[6], 'schedule' => $m[7], 'reorder_level' => $m[8],
        ]));

        // One GRN receiving stock for every medicine
        $stock = app(PharmacyStock::class);
        $purchase = Purchase::create([
            'branch_id' => $main->id, 'supplier_id' => $supplier->id,
            'invoice_number' => 'MP/2026/0912', 'invoice_date' => CarbonImmutable::now()->subDays(5),
            'received_date' => CarbonImmutable::now()->subDays(5),
        ]);

        $subtotal = 0;
        $tax = 0;
        foreach ($medicines as $idx => $med) {
            $cost = [2.10, 1.40, 1.20, 4.80, 12.50, 1.90, 3.60, 2.20, 1.30, 48, 6.50, 1.80, 26, 1.10][$idx];
            $mrp = round($cost * 2.4, 2);
            $sale = round($mrp * 0.95, 2);
            $qty = [500, 400, 300, 200, 150, 300, 250, 200, 200, 40, 200, 150, 30, 100][$idx];
            // one medicine gets a near-expiry batch for the alerts demo
            $expiry = $idx === 2
                ? CarbonImmutable::now()->addDays(40)
                : CarbonImmutable::now()->addMonths(rand(10, 30));

            $taxable = $qty * $cost;
            $lineGst = $taxable * $med->gst_rate / 100;

            $item = $purchase->items()->create([
                'medicine_id' => $med->id,
                'batch_number' => 'B'.str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT).'-26',
                'expiry_date' => $expiry->toDateString(),
                'quantity' => $qty, 'purchase_price' => $cost, 'mrp' => $mrp, 'sale_price' => $sale,
                'gst_rate' => $med->gst_rate, 'line_total' => round($taxable + $lineGst, 2),
            ]);
            $stock->receive($item->load('purchase'), $main->id);
            $subtotal += $taxable;
            $tax += $lineGst;
        }
        $purchase->update(['subtotal' => round($subtotal, 2), 'tax' => round($tax, 2), 'total' => round($subtotal + $tax, 2)]);

        // Dispense the oldest completed consultation's prescription
        $consultation = Consultation::whereHas('items')->oldest()->first();
        if ($consultation) {
            $sale = \App\Models\PharmacySale::create([
                'branch_id' => $main->id,
                'patient_id' => $consultation->patient_id,
                'consultation_id' => $consultation->id,
                'prescribed_by' => $consultation->doctor_id,
                'sale_date' => today(),
                'payment_mode' => 'cash',
                'status' => 'completed',
            ]);

            $sub = 0;
            $tax2 = 0;
            foreach ($consultation->items->take(2) as $rx) {
                $med = $medicines->first(fn ($m) => stripos($rx->drug_name, $m->name) !== false)
                    ?? $medicines->firstWhere('name', 'Paracetamol');
                $plan = $stock->allocate($med, $main->id, 10);
                foreach ($plan as $alloc) {
                    $batch = $alloc['batch'];
                    $gross = $alloc['quantity'] * (float) $batch->sale_price;
                    $sale->items()->create([
                        'medicine_id' => $med->id, 'medicine_batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number, 'expiry_date' => $batch->expiry_date,
                        'quantity' => $alloc['quantity'], 'mrp' => $batch->mrp, 'sale_price' => $batch->sale_price,
                        'gst_rate' => $med->gst_rate, 'line_total' => round($gross, 2),
                    ]);
                    $sub += $gross;
                    $tax2 += $gross * $med->gst_rate / (100 + $med->gst_rate);
                }
                $stock->dispense($plan, $sale, 'Bill '.$sale->sale_no);
            }
            $rounded = round($sub);
            $sale->update([
                'subtotal' => round($sub, 2), 'tax' => round($tax2, 2),
                'round_off' => round($rounded - $sub, 2), 'total' => $rounded, 'amount_paid' => $rounded,
            ]);
        }

        app(Tenancy::class)->forget();
        $this->command->info('Demo pharmacy: '.$medicines->count().' medicines, 1 GRN, 1 dispensed bill.');
    }
}
