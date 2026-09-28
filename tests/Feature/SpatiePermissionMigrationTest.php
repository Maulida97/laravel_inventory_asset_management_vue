<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('it verifies all 5 spatie permission tables exist in the database', function () {
    expect(Schema::hasTable('roles'))->toBeTrue()
        ->and(Schema::hasTable('permissions'))->toBeTrue()
        ->and(Schema::hasTable('model_has_roles'))->toBeTrue()
        ->and(Schema::hasTable('model_has_permissions'))->toBeTrue()
        ->and(Schema::hasTable('role_has_permissions'))->toBeTrue();
});

test('it can create roles and permissions with default web guard', function () {
    $role = Role::create(['name' => 'Inventory Staff', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'inventory.stock-in.create', 'guard_name' => 'web']);

    expect($role->id)->not()->toBeNull()
        ->and($role->name)->toBe('Inventory Staff')
        ->and($role->guard_name)->toBe('web')
        ->and($permission->id)->not()->toBeNull()
        ->and($permission->name)->toBe('inventory.stock-in.create')
        ->and($permission->guard_name)->toBe('web');
});

test('it enforces unique constraint on role name and guard', function () {
    Role::create(['name' => 'Manager', 'guard_name' => 'web']);

    expect(fn() => Role::create(['name' => 'Manager', 'guard_name' => 'web']))
        ->toThrow(RoleAlreadyExists::class);
});

test('it enforces unique constraint on permission name and guard', function () {
    Permission::create(['name' => 'location.view', 'guard_name' => 'web']);

    expect(fn() => Permission::create(['name' => 'location.view', 'guard_name' => 'web']))
        ->toThrow(PermissionAlreadyExists::class);
});

test('it assigns role to user and verifies relations and helper methods', function () {
    $role = Role::create(['name' => 'Asset Staff', 'guard_name' => 'web']);
    $user = User::factory()->create();

    $user->assignRole($role);

    expect($user->hasRole('Asset Staff'))->toBeTrue()
        ->and($user->hasRole($role))->toBeTrue()
        ->and($user->roles)->toHaveCount(1)
        ->and($user->roles->first()->name)->toBe('Asset Staff');
});

test('it allows user to inherit permissions through assigned role', function () {
    $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $permission1 = Permission::create(['name' => 'asset.view', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'asset.create', 'guard_name' => 'web']);
    $unassignedPermission = Permission::create(['name' => 'inventory.stock-in.create', 'guard_name' => 'web']);

    $role->givePermissionTo([$permission1, $permission2]);

    $user = User::factory()->create();
    $user->assignRole($role);

    expect($user->hasPermissionTo('asset.view'))->toBeTrue()
        ->and($user->hasPermissionTo('asset.create'))->toBeTrue()
        ->and($user->hasPermissionTo('inventory.stock-in.create'))->toBeFalse();
});

test('it supports direct permission assignment to user', function () {
    $permission = Permission::create(['name' => 'audit.view', 'guard_name' => 'web']);
    $user = User::factory()->create();

    $user->givePermissionTo($permission);

    expect($user->hasPermissionTo('audit.view'))->toBeTrue()
        ->and($user->hasDirectPermission('audit.view'))->toBeTrue();
});

test('it cascades delete on role removal without deleting associated user', function () {
    $role = Role::create(['name' => 'Requester', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'inventory.stock-out.create', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $user = User::factory()->create();
    $user->assignRole($role);

    expect(DB::table('model_has_roles')->where('role_id', $role->id)->count())->toBe(1)
        ->and(DB::table('role_has_permissions')->where('role_id', $role->id)->count())->toBe(1);

    // Delete the role
    $role->delete();

    // Check pivot tables are cleaned up automatically
    expect(DB::table('model_has_roles')->where('role_id', $role->id)->count())->toBe(0)
        ->and(DB::table('role_has_permissions')->where('role_id', $role->id)->count())->toBe(0)
        ->and(User::find($user->id))->not()->toBeNull(); // User still exists
});
