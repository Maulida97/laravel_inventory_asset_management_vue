<?php

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('it seeds all six predefined system roles successfully', function () {
    $this->seed(RoleSeeder::class);

    $expectedRoles = [
        'Super Admin',
        'Admin',
        'Inventory Staff',
        'Asset Staff',
        'Requester',
        'Manager',
    ];

    expect(Role::count())->toBe(6);

    foreach ($expectedRoles as $roleName) {
        $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

        expect($role)->not()->toBeNull()
            ->and($role->name)->toBe($roleName)
            ->and($role->guard_name)->toBe('web');
    }
});

test('it is idempotent and produces no duplicates or errors when run multiple times', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::count())->toBe(6);
});

test('it runs role seeder via main database seeder', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Role::count())->toBe(6)
        ->and(Role::where('name', 'Super Admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'Admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'Inventory Staff')->exists())->toBeTrue()
        ->and(Role::where('name', 'Asset Staff')->exists())->toBeTrue()
        ->and(Role::where('name', 'Requester')->exists())->toBeTrue()
        ->and(Role::where('name', 'Manager')->exists())->toBeTrue();
});
