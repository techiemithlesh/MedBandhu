<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Staff;
use App\Support\AppointmentSlots;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoOpdSeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::where('code', 'DEMO')->first();
        if (! $hospital || Patient::where('hospital_id', $hospital->id)->exists()) {
            $this->command->warn('DEMO hospital missing or already has patients — skipping.');

            return;
        }

        app(Tenancy::class)->setHospital($hospital);
        setPermissionsTeamId($hospital->id);
        $main = $hospital->branches()->where('code', 'MAIN')->first();
        app(Tenancy::class)->setBranch($main);

        $people = [
            ['Mr.', 'Ramesh', 'Prasad', 'male', '1968-04-12', 'B+', '9812345670', 'Patna', 'Hypertension', 'Penicillin'],
            ['Mrs.', 'Sita', 'Kumari', 'female', '1979-11-03', 'O+', '9812345671', 'Patna', null, null],
            ['Ms.', 'Anjali', 'Singh', 'female', '1996-06-21', 'A+', '9812345672', 'Danapur', null, null],
            ['Mr.', 'Vivek', 'Ranjan', 'male', '2015-02-08', 'AB+', '9812345673', 'Patna', 'Asthma', null],
            ['Mr.', 'Suresh', 'Yadav', 'male', '1955-09-17', 'B-', '9812345674', 'Fatuha', 'Diabetes, CKD', 'Sulfa drugs'],
            ['Mrs.', 'Kiran', 'Devi', 'female', '1988-01-30', 'O-', '9812345675', 'Patna', null, null],
            ['Mr.', 'Alok', 'Verma', 'male', '2001-12-05', 'A-', '9812345676', 'Patna', null, null],
            ['Ms.', 'Pooja', 'Sharma', 'female', '1992-07-14', 'B+', '9812345677', 'Bihta', null, 'Dust'],
        ];

        $patients = collect($people)->map(fn ($p) => Patient::create([
            'salutation' => $p[0], 'first_name' => $p[1], 'last_name' => $p[2],
            'gender' => $p[3], 'dob' => $p[4], 'blood_group' => $p[5], 'phone' => $p[6],
            'city' => $p[7], 'state' => 'Bihar',
            'chronic_conditions' => $p[8], 'allergies' => $p[9],
        ]));

        $doctors = Staff::doctors()->get()->keyBy('first_name');
        $today = CarbonImmutable::today();
        $slots = app(AppointmentSlots::class);

        // Priya Nair has a Mon/Wed/Fri schedule; only book slotted appts when she has one today.
        $priyaSlots = $slots->for($doctors['Priya'], $today);

        $bookings = [
            ['Priya', 0, 'checked_in', 'new'],
            ['Priya', 1, 'checked_in', 'followup'],
            ['Priya', 2, 'completed', 'new'],
            ['Priya', 3, 'scheduled', 'new'],
            ['Arjun', null, 'scheduled', 'new'],       // walk-in style, no schedule today maybe
            ['Meera', null, 'checked_in', 'new'],
        ];

        foreach ($bookings as $i => [$docName, $slotIdx, $status, $type]) {
            $doctor = $doctors[$docName];
            $slot = $slotIdx !== null ? $priyaSlots->get($slotIdx) : null;

            $appt = Appointment::create([
                'branch_id' => $main->id,
                'patient_id' => $patients[$i]->id,
                'doctor_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'scheduled_date' => $today->toDateString(),
                'scheduled_time' => $slot['time'] ?? null,
                'slot_minutes' => $doctor->doctorProfile->appointment_duration_min ?? 15,
                'token_no' => $i + 1,
                'type' => $type,
                'source' => $slot ? 'booked' : 'walk_in',
                'status' => $status,
                'checked_in_at' => in_array($status, ['checked_in', 'completed', 'in_consultation']) ? now() : null,
                'completed_at' => $status === 'completed' ? now() : null,
                'consultation_started_at' => $status === 'completed' ? now() : null,
                'reason' => ['Fever and body ache', 'BP review', 'Knee pain', 'Cough', 'Cough & cold', 'Routine checkup'][$i],
                'consultation_fee' => $type === 'followup'
                    ? $doctor->doctorProfile->followup_fee
                    : $doctor->doctorProfile->consultation_fee,
                'fee_paid' => $status !== 'scheduled',
            ]);

            if ($status === 'completed') {
                $appt->vital()->create([
                    'patient_id' => $appt->patient_id,
                    'height_cm' => 168, 'weight_kg' => 72, 'temperature_c' => 37.8,
                    'pulse_bpm' => 88, 'systolic' => 130, 'diastolic' => 84, 'spo2' => 98,
                    'recorded_at' => now(),
                ]);
                $c = $appt->consultation()->create([
                    'patient_id' => $appt->patient_id,
                    'doctor_id' => $appt->doctor_id,
                    'chief_complaint' => 'Fever x3 days, body ache',
                    'examination_findings' => 'Throat congested, chest clear',
                    'diagnosis' => 'Acute viral fever',
                    'advice' => 'Rest, plenty of fluids. Review if fever persists beyond 3 days.',
                    'followup_date' => $today->addDays(4)->toDateString(),
                ]);
                $c->items()->createMany([
                    ['drug_name' => 'Paracetamol', 'strength' => '650 mg', 'dosage' => '1-1-1', 'duration_days' => 3, 'instructions' => 'after food', 'sort_order' => 0],
                    ['drug_name' => 'Cetirizine', 'strength' => '10 mg', 'dosage' => '0-0-1', 'duration_days' => 5, 'instructions' => 'at night', 'sort_order' => 1],
                ]);
            }
        }

        app(Tenancy::class)->forget();
        $this->command->info('Demo patients + OPD queue seeded for Sunrise.');
    }
}
