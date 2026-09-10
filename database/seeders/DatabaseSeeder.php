<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Platform Super Admin (not bound to any hospital).
        $superAdmin = User::firstOrCreate(
            ['email' => 'super@hms.test'],
            [
                'hospital_id' => null,
                'name' => 'Platform Super Admin',
                'password' => 'password',
                'designation' => 'Platform Operator',
                'is_active' => true,
            ]
        );
        setPermissionsTeamId(config('hms.platform_team_id'));
        $superAdmin->assignRole('Super Admin');

        // Demo dataset — each seeder is idempotent ("already seeded" guarded),
        // so `db:seed` is safe to re-run and `migrate:fresh --seed` rebuilds
        // the whole working demo in the right order.
        $this->call([
            DemoHospitalSeeder::class,
            DemoPlansSeeder::class,
            DemoStaffSeeder::class,
            DemoOpdSeeder::class,
            DemoIpdSeeder::class,
            DemoPharmacySeeder::class,
            DemoBillingSeeder::class,
        ]);
    }
}
