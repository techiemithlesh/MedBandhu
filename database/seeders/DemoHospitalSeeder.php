<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Hospital;
use App\Models\User;
use App\Support\HospitalProvisioner;
use Illuminate\Database\Seeder;

class DemoHospitalSeeder extends Seeder
{
    public function run(): void
    {
        if (Hospital::where('code', 'DEMO')->exists()) {
            return;
        }

        /** @var Hospital $hospital */
        $hospital = app(HospitalProvisioner::class)->create(
            [
                'name' => 'Sunrise Multispeciality Hospital',
                'code' => 'DEMO',
                'slug' => 'sunrise-demo',
                'email' => 'contact@sunrise.test',
                'phone' => '0612-2000000',
                'city' => 'Patna',
                'state' => 'Bihar',
                'subscription_plan' => 'pro',
                'subscription_status' => 'active',
            ],
            [
                'name' => 'Dr. Anil Mehta',
                'email' => 'admin@sunrise.test',
                'password' => 'password',
            ]
        );

        setPermissionsTeamId($hospital->id);

        $main = $hospital->branches()->where('code', 'MAIN')->first();

        $city = Branch::withoutTenantScope()->create([
            'hospital_id' => $hospital->id,
            'name' => 'Sunrise — City Clinic',
            'code' => 'CITY',
            'type' => 'clinic',
            'city' => 'Patna',
            'state' => 'Bihar',
        ]);

        $people = [
            ['Dr. Priya Nair', 'doctor@sunrise.test', 'Doctor', 'Consultant Physician'],
            ['Sunita Devi', 'nurse@sunrise.test', 'Nurse', 'Staff Nurse'],
            ['Rahul Verma', 'reception@sunrise.test', 'Receptionist', 'Front Desk'],
            ['Amit Kumar', 'pharmacy@sunrise.test', 'Pharmacist', 'Pharmacist'],
            ['Neha Gupta', 'accounts@sunrise.test', 'Accountant', 'Accounts Executive'],
        ];

        $people[] = ['Demo User', config('hms.demo.email'), 'Demo', 'Guest access'];

        foreach ($people as [$name, $email, $role, $designation]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'hospital_id' => $hospital->id,
                    'name' => $name,
                    'password' => 'password',
                    'designation' => $designation,
                    'is_active' => true,
                ]
            );
            $user->assignRole($role);
            $user->branches()->syncWithoutDetaching([$main->id => ['is_primary' => true]]);
        }
    }
}
