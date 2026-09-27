<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_roles_and_permissions_and_assign_to_user(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $viewAssets = Permission::create(['name' => 'view-assets']);
        $createAssets = Permission::create(['name' => 'create-assets']);

        $adminRole = Role::create(['name' => 'Super Admin']);
        $staffRole = Role::create(['name' => 'Staff']);

        $adminRole->givePermissionTo([$viewAssets, $createAssets]);
        $staffRole->givePermissionTo($viewAssets);

        $user = User::factory()->create();
        $user->assignRole($adminRole);

        $this->assertTrue($user->hasRole('Super Admin'));
        $this->assertTrue($user->hasPermissionTo('view-assets'));
        $this->assertTrue($user->hasPermissionTo('create-assets'));
        $this->assertFalse($user->hasRole('Staff'));
    }
}