<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates everything a new hospital tenant needs: its standard set of
 * roles (scoped to its team id) and, optionally, a first admin user and
 * a main branch. Reused by the seeder and by the Super Admin "add hospital" flow.
 */
class HospitalProvisioner
{
    /**
     * @param  array<string,mixed>  $attributes  hospital column values
     * @param  array<string,mixed>|null  $admin  ['name','email','password'] for the first Hospital Admin
     */
    public function create(array $attributes, ?array $admin = null): Hospital
    {
        return DB::transaction(function () use ($attributes, $admin) {
            $hospital = Hospital::create($attributes);

            $this->syncRoles($hospital);

            $branch = Branch::withoutTenantScope()->create([
                'hospital_id' => $hospital->id,
                'name' => $attributes['name'].' — Main',
                'code' => 'MAIN',
                'type' => 'main',
                'city' => $attributes['city'] ?? null,
                'state' => $attributes['state'] ?? null,
            ]);

            if ($admin) {
                $this->createAdmin($hospital, $branch, $admin);
            }

            return $hospital;
        });
    }

    /**
     * Create (or refresh) the per-hospital roles. Idempotent — safe to re-run
     * after config/hms.php changes to roll new permissions out to every tenant.
     */
    public function syncRoles(Hospital $hospital): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($hospital->id);

        $catalog = config('hms.roles');
        $allPermissions = config('hms.permissions');
        $platformOnly = config('hms.platform_only_permissions');

        foreach ($catalog as $roleName => $permissions) {
            if ($roleName === 'Super Admin') {
                continue; // platform-level only
            }

            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                config('permission.column_names.team_foreign_key') => $hospital->id,
            ]);

            $resolved = $permissions === ['*'] ? $allPermissions : $permissions;
            $resolved = array_values(array_diff($resolved, $platformOnly));

            $role->syncPermissions($resolved);
        }
    }

    protected function createAdmin(Hospital $hospital, Branch $branch, array $admin): User
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($hospital->id);

        $user = User::create([
            'hospital_id' => $hospital->id,
            'name' => $admin['name'],
            'email' => $admin['email'],
            'password' => $admin['password'] ?? Str::password(12),
            'designation' => 'Administrator',
            'is_active' => true,
        ]);

        $user->assignRole('Hospital Admin');
        $user->branches()->attach($branch->id, ['is_primary' => true]);

        return $user;
    }
}
