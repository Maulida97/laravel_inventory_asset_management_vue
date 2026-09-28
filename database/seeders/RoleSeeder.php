<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * The 6 core system roles according to system architecture.
     *
     * @var array<int, string>
     */
    public const ROLES = [
        'Super Admin',
        'Admin',
        'Inventory Staff',
        'Asset Staff',
        'Requester',
        'Manager',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () {
            foreach (self::ROLES as $roleName) {
                Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);
            }
        });

        // Clear cache again after seeding
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
