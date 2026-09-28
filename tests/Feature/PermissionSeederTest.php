<?php

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('it seeds all predefined permissions from matrix with web guard', function () {
    $this->seed(PermissionSeeder::class);

    $expectedTotal = count(PermissionSeeder::PERMISSIONS_MATRIX);
    expect(Permission::count())->toBe($expectedTotal);

    foreach (array_keys(PermissionSeeder::PERMISSIONS_MATRIX) as $permissionName) {
        $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();

        expect($permission)->not()->toBeNull()
            ->and($permission->name)->toBe($permissionName)
            ->and($permission->guard_name)->toBe('web');
    }
});

test('it assigns all permissions to super admin role', function () {
    $this->seed(PermissionSeeder::class);

    $superAdminRole = Role::findByName('Super Admin', 'web');
    $expectedTotal = count(PermissionSeeder::PERMISSIONS_MATRIX);

    expect($superAdminRole->permissions)->toHaveCount($expectedTotal);
});

test('it assigns correct granular permissions to requester and manager roles', function () {
    $this->seed(PermissionSeeder::class);

    $requesterRole = Role::findByName('Requester', 'web');
    $managerRole = Role::findByName('Manager', 'web');

    // Requester checks
    expect($requesterRole->hasPermissionTo('inventory.stock-request.create'))->toBeTrue()
        ->and($requesterRole->hasPermissionTo('approval.my-request.view'))->toBeTrue()
        ->and($requesterRole->hasPermissionTo('dashboard.widget.my-requests'))->toBeTrue()
        ->and($requesterRole->hasPermissionTo('item.create'))->toBeFalse()
        ->and($requesterRole->hasPermissionTo('user.create'))->toBeFalse();

    // Manager checks
    expect($managerRole->hasPermissionTo('approval.review.level-2'))->toBeTrue()
        ->and($managerRole->hasPermissionTo('approval.approve.level-2'))->toBeTrue()
        ->and($managerRole->hasPermissionTo('report.stock.view'))->toBeTrue()
        ->and($managerRole->hasPermissionTo('report.asset.view'))->toBeTrue()
        ->and($managerRole->hasPermissionTo('inventory.po.create'))->toBeFalse()
        ->and($managerRole->hasPermissionTo('asset.create'))->toBeFalse();
});

test('it is idempotent and can run multiple times without duplicating permissions', function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(PermissionSeeder::class);

    $expectedTotal = count(PermissionSeeder::PERMISSIONS_MATRIX);
    expect(Permission::count())->toBe($expectedTotal);
});
