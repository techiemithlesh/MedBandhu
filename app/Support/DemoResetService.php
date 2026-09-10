<?php

namespace App\Support;

use App\Models\Hospital;
use App\Models\User;
use Database\Seeders\DemoBillingSeeder;
use Database\Seeders\DemoHospitalSeeder;
use Database\Seeders\DemoIpdSeeder;
use Database\Seeders\DemoOpdSeeder;
use Database\Seeders\DemoPharmacySeeder;
use Database\Seeders\DemoPlansSeeder;
use Database\Seeders\DemoStaffSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Tears the demo hospital down to nothing and rebuilds it from the Demo*
 * seeders. Every hospital_id FK is ON DELETE CASCADE, so removing the
 * hospitals row wipes all of its tenant data in one shot; the only things
 * that need manual cleanup first are the demo users (users.hospital_id is
 * ON DELETE SET NULL — a null hospital_id would read as a super admin) and
 * the spatie role rows, which are keyed by team id with no FK.
 */
class DemoResetService
{
    /** @var class-string[] */
    protected array $seeders = [
        DemoHospitalSeeder::class,
        DemoPlansSeeder::class,
        DemoStaffSeeder::class,
        DemoOpdSeeder::class,
        DemoIpdSeeder::class,
        DemoPharmacySeeder::class,
        DemoBillingSeeder::class,
    ];

    public function reset(): void
    {
        $code = config('hms.demo.hospital_code', 'DEMO');
        $teamKey = config('permission.column_names.team_foreign_key', 'team_id');

        $hospital = Hospital::withoutGlobalScopes()->where('code', $code)->first();

        if ($hospital) {
            $id = $hospital->id;

            DB::transaction(function () use ($hospital, $id, $teamKey) {
                DB::table('model_has_roles')->where($teamKey, $id)->delete();
                DB::table('model_has_permissions')->where($teamKey, $id)->delete();
                DB::table('roles')->where($teamKey, $id)->delete();

                User::withoutGlobalScopes()->where('hospital_id', $id)->delete();

                // hard delete — Hospital is SoftDeletes, and only a real DELETE
                // fires the ON DELETE CASCADE that clears the tenant's data.
                $hospital->forceDelete();
            });
        }

        foreach ($this->seeders as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }
    }
}
