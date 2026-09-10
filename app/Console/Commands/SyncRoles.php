<?php

namespace App\Console\Commands;

use App\Models\Hospital;
use App\Support\HospitalProvisioner;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncRoles extends Command
{
    protected $signature = 'hms:sync-roles';

    protected $description = 'Sync config/hms.php permissions & roles into the DB (global perms, Super Admin role, and every hospital\'s role set).';

    public function handle(HospitalProvisioner $provisioner): int
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (config('hms.permissions') as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $this->info('Permissions synced ('.count(config('hms.permissions')).').');

        $platformTeam = config('hms.platform_team_id');
        app(PermissionRegistrar::class)->setPermissionsTeamId($platformTeam);
        Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
            config('permission.column_names.team_foreign_key') => $platformTeam,
        ])->syncPermissions(config('hms.permissions'));
        $this->info('Super Admin role synced.');

        Hospital::withoutGlobalScopes()->each(function (Hospital $hospital) use ($provisioner) {
            $provisioner->syncRoles($hospital);
            $this->line("  • {$hospital->name} ({$hospital->code})");
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
