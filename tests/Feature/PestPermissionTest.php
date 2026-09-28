<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('it verifies home page returns successful response for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');
    $response->assertStatus(200);
});

test('it can assign roles and permissions using pest syntax', function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $role = Role::create(['name' => 'Asset Manager']);
    $permission = Permission::create(['name' => 'manage-assets']);
    $role->givePermissionTo($permission);

    $user = User::factory()->create();
    $user->assignRole($role);

    expect($user->hasRole('Asset Manager'))->toBeTrue()
        ->and($user->hasPermissionTo('manage-assets'))->toBeTrue();
});
