<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('it seeds default super admin user with active and approved status', function () {
    $this->seed(SuperAdminSeeder::class);

    $user = User::where('email', 'superadmin@assetflow.io')->first();

    expect($user)->not()->toBeNull()
        ->and($user->name)->toBe('Super Admin')
        ->and($user->employee_id)->toBe('EMP-00000')
        ->and($user->position)->toBe('System Administrator')
        ->and($user->is_active)->toBeTrue()
        ->and($user->registration_status)->toBe('approved')
        ->and($user->email_verified_at)->not()->toBeNull()
        ->and(Hash::check('password', $user->password))->toBeTrue()
        ->and($user->hasRole('Super Admin'))->toBeTrue()
        ->and($user->hasPermissionTo('location.create'))->toBeTrue()
        ->and($user->hasPermissionTo('user.assign-role'))->toBeTrue()
        ->and($user->hasPermissionTo('config.approval.manage'))->toBeTrue();
});

test('it is idempotent and does not create duplicate super admin users', function () {
    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    expect(User::where('email', 'superadmin@assetflow.io')->count())->toBe(1);
});

test('it runs full database seeder successfully', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::where('email', 'superadmin@assetflow.io')->first();

    expect($user)->not()->toBeNull()
        ->and($user->hasRole('Super Admin'))->toBeTrue();
});
