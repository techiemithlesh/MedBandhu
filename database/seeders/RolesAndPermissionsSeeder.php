<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Permissions are global (not team-scoped).
        foreach (config('hms.permissions') as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Platform-level Super Admin role — stored under the sentinel platform team.
        $platformTeam = config('hms.platform_team_id');
        app(PermissionRegistrar::class)->setPermissionsTeamId($platformTeam);

        Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
            config('permission.column_names.team_foreign_key') => $platformTeam,
        ])->syncPermissions(config('hms.permissions'));
    }
}
