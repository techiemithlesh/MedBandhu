<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Platform Super Admin (not bound to any hospital).
        // Production reads SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD from .env;
        // with no password set, a strong one is generated and printed once.
        $email = env('SUPER_ADMIN_EMAIL', 'super@hms.test');
        $password = env('SUPER_ADMIN_PASSWORD')
            ?: (app()->environment('production') ? Str::password(16) : 'password');

        $superAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'hospital_id' => null,
                'name' => 'Platform Super Admin',
                'password' => $password,
                'designation' => 'Platform Operator',
                'is_active' => true,
            ]
        );
        setPermissionsTeamId(config('hms.platform_team_id'));
        $superAdmin->assignRole('Super Admin');

        if ($superAdmin->wasRecentlyCreated && ! env('SUPER_ADMIN_PASSWORD') && app()->environment('production')) {
            $this->command->warn("Super admin created — {$email} / {$password}");
            $this->command->warn('Log in and change this password now.');
        }

        // Demo dataset — powers the public "Try live demo" and loads the plans.
        // Each seeder is idempotent, so `db:seed` is safe to re-run.
        if (config('hms.demo.enabled')) {
            $this->call([
                DemoHospitalSeeder::class,
                DemoPlansSeeder::class,
                DemoStaffSeeder::class,
                DemoOpdSeeder::class,
                DemoIpdSeeder::class,
                DemoPharmacySeeder::class,
                DemoBillingSeeder::class,
            ]);
        } else {
            $this->call(DemoPlansSeeder::class); // no demo — still load the plans
        }
    }
}
