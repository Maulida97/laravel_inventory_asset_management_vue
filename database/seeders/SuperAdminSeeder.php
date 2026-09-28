<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure PermissionSeeder (and thus RoleSeeder) has run
        $this->call(PermissionSeeder::class);

        DB::transaction(function () {
            $superAdminEmail = env('SUPERADMIN_EMAIL', 'superadmin@assetflow.io');
            $superAdminPassword = env('SUPERADMIN_PASSWORD', 'password');

            $superAdmin = User::firstOrCreate(
                ['email' => $superAdminEmail],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make($superAdminPassword),
                    'phone_number' => '081234567890',
                    'employee_id' => 'EMP-00000',
                    'position' => 'System Administrator',
                    'photo_path' => null,
                    'join_date' => '2026-01-01',
                    'is_active' => true,
                    'registration_status' => 'approved',
                    'email_verified_at' => now(),
                ]
            );

            // Assign Super Admin role if not already assigned
            if (! $superAdmin->hasRole('Super Admin')) {
                $superAdmin->assignRole('Super Admin');
            }
        });

        // Clear cache again after seeding
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
