<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DoctorProfile;
use App\Models\DoctorSchedule;
use App\Models\Hospital;
use App\Models\Staff;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Database\Seeder;

class DemoStaffSeeder extends Seeder
{
    public function run(): void
    {
        $hospital = Hospital::where('code', 'DEMO')->first();
        if (! $hospital) {
            $this->command->warn('DEMO hospital not found — run DemoHospitalSeeder first.');

            return;
        }

        app(Tenancy::class)->setHospital($hospital);
        setPermissionsTeamId($hospital->id);

        $main = $hospital->branches()->where('code', 'MAIN')->first();

        $departments = collect([
            ['General Medicine', 'GEN-MED'],
            ['Pediatrics', 'PEDIA'],
            ['Orthopedics', 'ORTHO'],
            ['Cardiology', 'CARDIO'],
            ['Nursing', 'NURSING'],
            ['Pharmacy', 'PHARMACY'],
        ])->mapWithKeys(function ($d) use ($hospital) {
            $dept = Department::firstOrCreate(
                ['hospital_id' => $hospital->id, 'code' => $d[1]],
                ['name' => $d[0], 'is_active' => true],
            );

            return [$d[1] => $dept];
        });

        // link existing seeded login users to staff records
        $userByEmail = User::where('hospital_id', $hospital->id)->get()->keyBy('email');

        $people = [
            // code, type, salutation, first, last, dept, designation, email(for user link), doctor[]
            ['EMP-001', 'doctor', 'Dr.', 'Priya', 'Nair', 'GEN-MED', 'Consultant Physician', 'doctor@sunrise.test',
                ['specialization' => 'Internal Medicine', 'qualifications' => 'MBBS, MD (Medicine)', 'registration_no' => 'BMC-24518', 'registration_council' => 'Bihar Medical Council', 'experience_years' => 12, 'consultation_fee' => 600, 'followup_fee' => 300, 'appointment_duration_min' => 15]],
            ['EMP-002', 'doctor', 'Dr.', 'Arjun', 'Sharma', 'ORTHO', 'Orthopedic Surgeon', null,
                ['specialization' => 'Orthopedics', 'qualifications' => 'MBBS, MS (Ortho)', 'registration_no' => 'BMC-19233', 'experience_years' => 15, 'consultation_fee' => 800, 'followup_fee' => 400, 'is_surgeon' => true, 'appointment_duration_min' => 20]],
            ['EMP-003', 'doctor', 'Dr.', 'Meera', 'Iyer', 'PEDIA', 'Pediatrician', null,
                ['specialization' => 'Pediatrics', 'qualifications' => 'MBBS, DCH, MD (Peds)', 'registration_no' => 'BMC-30871', 'experience_years' => 9, 'consultation_fee' => 500, 'followup_fee' => 250, 'appointment_duration_min' => 15]],
            ['EMP-004', 'nurse', 'Ms.', 'Sunita', 'Devi', 'NURSING', 'Staff Nurse', 'nurse@sunrise.test', null],
            ['EMP-005', 'nurse', 'Ms.', 'Farah', 'Khan', 'NURSING', 'Senior Staff Nurse', null, null],
            ['EMP-006', 'receptionist', 'Mr.', 'Rahul', 'Verma', null, 'Front Desk Executive', 'reception@sunrise.test', null],
            ['EMP-007', 'pharmacist', 'Mr.', 'Amit', 'Kumar', 'PHARMACY', 'Pharmacist', 'pharmacy@sunrise.test', null],
            ['EMP-008', 'accountant', 'Ms.', 'Neha', 'Gupta', null, 'Accounts Executive', 'accounts@sunrise.test', null],
            ['EMP-009', 'lab_technician', 'Mr.', 'Vikram', 'Singh', null, 'Lab Technician', null, null],
            ['EMP-010', 'support', 'Mr.', 'Ramesh', 'Yadav', null, 'Ward Attendant', null, null],
        ];

        foreach ($people as [$code, $type, $sal, $first, $last, $deptCode, $designation, $userEmail, $doctor]) {
            $staff = Staff::firstOrCreate(
                ['hospital_id' => $hospital->id, 'employee_code' => $code],
                [
                    'branch_id' => $main->id,
                    'department_id' => $deptCode ? $departments[$deptCode]->id : null,
                    'user_id' => $userEmail ? $userByEmail->get($userEmail)?->id : null,
                    'type' => $type,
                    'salutation' => $sal,
                    'first_name' => $first,
                    'last_name' => $last,
                    'gender' => in_array($sal, ['Ms.', 'Mrs.']) ? 'female' : 'male',
                    'designation' => $designation,
                    'employment_type' => $type === 'doctor' ? 'visiting' : 'permanent',
                    'joined_on' => now()->subMonths(rand(3, 40)),
                    'status' => 'active',
                    'phone' => '9'.rand(100000000, 999999999),
                ],
            );

            if ($doctor) {
                DoctorProfile::firstOrCreate(
                    ['staff_id' => $staff->id],
                    array_merge(['hospital_id' => $hospital->id], $doctor),
                );

                // Mon/Wed/Fri morning clinic
                foreach ([1, 3, 5] as $dow) {
                    DoctorSchedule::firstOrCreate(
                        ['staff_id' => $staff->id, 'branch_id' => $main->id, 'day_of_week' => $dow],
                        [
                            'hospital_id' => $hospital->id,
                            'start_time' => '10:00',
                            'end_time' => '13:00',
                            'slot_minutes' => $doctor['appointment_duration_min'] ?? 15,
                            'max_tokens' => 20,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }

        // department heads
        $departments['GEN-MED']->update(['head_staff_id' => Staff::where('employee_code', 'EMP-001')->value('id')]);
        $departments['ORTHO']->update(['head_staff_id' => Staff::where('employee_code', 'EMP-002')->value('id')]);
        $departments['NURSING']->update(['head_staff_id' => Staff::where('employee_code', 'EMP-005')->value('id')]);

        app(Tenancy::class)->forget();

        $this->command->info('Demo departments, staff and doctor schedules seeded for Sunrise.');
    }
}
