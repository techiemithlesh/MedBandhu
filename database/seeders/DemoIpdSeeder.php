<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\Department;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Ward;
use App\Support\IpdService;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoIpdSeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::where('code', 'DEMO')->first();
        if (! $hospital || Ward::where('hospital_id', $hospital->id)->exists()) {
            $this->command->warn('DEMO hospital missing or wards already seeded — skipping.');

            return;
        }

        app(Tenancy::class)->setHospital($hospital);
        setPermissionsTeamId($hospital->id);
        $main = $hospital->branches()->where('code', 'MAIN')->first();
        app(Tenancy::class)->setBranch($main);

        $depts = Department::pluck('id', 'code');

        $wardDefs = [
            ['General Ward', 'GW', 'general', 'Ground', 1500, 12],
            ['Semi-Private', 'SP', 'semi_private', '1st', 3000, 6],
            ['Private Rooms', 'PR', 'private', '1st', 6000, 4],
            ['ICU', 'ICU', 'icu', '2nd', 12000, 4],
            ['Maternity', 'MAT', 'maternity', 'Ground', 4000, 4],
        ];

        foreach ($wardDefs as [$name, $code, $type, $floor, $rate, $bedCount]) {
            $ward = Ward::create([
                'branch_id' => $main->id,
                'department_id' => $type === 'icu' ? ($depts['GEN-MED'] ?? null) : null,
                'name' => $name, 'code' => $code, 'type' => $type, 'floor' => $floor,
                'gender_restriction' => $type === 'maternity' ? 'female' : 'any',
                'default_daily_charge' => $rate,
            ]);

            for ($i = 1; $i <= $bedCount; $i++) {
                $ward->beds()->create([
                    'hospital_id' => $hospital->id,
                    'branch_id' => $main->id,
                    'bed_number' => $code.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'daily_charge' => $rate,
                    'status' => 'available',
                ]);
            }
        }

        $ipd = app(IpdService::class);
        $doctors = Staff::doctors()->get();
        $patients = Patient::orderBy('id')->take(4)->get();

        // Admission 1 — 3 days ago, general ward, with a transfer to private + notes
        $gwBed = Bed::where('ward_id', Ward::where('code', 'GW')->value('id'))->available()->first();
        $a1 = $ipd->admit($patients[0], $gwBed, [
            'admitting_doctor_id' => $doctors->firstWhere('first_name', 'Priya')?->id,
            'source' => 'opd',
            'admitted_at' => CarbonImmutable::now()->subDays(3)->setTime(10, 30),
            'provisional_diagnosis' => 'Community-acquired pneumonia',
            'attendant_name' => 'Ram Prasad', 'attendant_relation' => 'Son', 'attendant_phone' => '9800001111',
        ]);
        // backdate the opening movement
        $a1->currentMovement->update(['started_at' => $a1->admitted_at]);
        $prBed = Bed::where('ward_id', Ward::where('code', 'PR')->value('id'))->available()->first();
        $ipd->transfer($a1, $prBed, 'Family requested private room');
        $a1->nursingNotes()->createMany([
            ['patient_id' => $a1->patient_id, 'category' => 'observation', 'note' => 'Temp 100.4°F, started on IV antibiotics.', 'recorded_at' => now()->subDays(3)->addHours(2)],
            ['patient_id' => $a1->patient_id, 'category' => 'medication', 'note' => 'Inj Ceftriaxone 1g given.', 'recorded_at' => now()->subDays(2)],
            ['patient_id' => $a1->patient_id, 'category' => 'observation', 'note' => 'Afebrile since morning, tolerating orals.', 'recorded_at' => now()->subHours(6)],
        ]);
        $ipd->generateBedCharges($a1->fresh(['bedMovements.ward']));

        // Admission 2 — yesterday, ICU
        $icuBed = Bed::where('ward_id', Ward::where('code', 'ICU')->value('id'))->available()->first();
        $a2 = $ipd->admit($patients[3], $icuBed, [
            'admitting_doctor_id' => $doctors->firstWhere('first_name', 'Arjun')?->id,
            'source' => 'emergency',
            'admitted_at' => CarbonImmutable::now()->subDay()->setTime(22, 0),
            'provisional_diagnosis' => 'Acute exacerbation of asthma',
            'expected_discharge_on' => CarbonImmutable::now()->addDays(2)->toDateString(),
        ]);
        $a2->currentMovement->update(['started_at' => $a2->admitted_at]);
        $a2->nursingNotes()->create([
            'patient_id' => $a2->patient_id, 'category' => 'observation',
            'note' => 'On nebulisation q4h, SpO2 94% on 2L O2.', 'recorded_at' => now()->subHours(3),
        ]);

        app(Tenancy::class)->forget();
        $this->command->info('Demo wards, beds and IPD admissions seeded for Sunrise.');
    }
}
