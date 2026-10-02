<?php

use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Ensure roles exist for testing
    $roles = ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'];
    foreach ($roles as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

test('unauthenticated guests are redirected to login page when accessing dashboard', function () {
    $this->get('/')
        ->assertRedirect('/login');

    $this->get('/dashboard')
        ->assertRedirect('/login');
});

test('authenticated and active user can access dashboard successfully', function () {
    $department = Department::factory()->create(['name' => 'Teknologi Informasi']);
    $user = User::factory()->create([
        'department_id' => $department->id,
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(
        fn($page) => $page
            ->component('Dashboard')
            ->has('user')
            ->where('user.name', $user->name)
            ->where('user.department', 'Teknologi Informasi')
    );
});

test('inactive user is redirected to login with error', function () {
    $user = User::factory()->create([
        'is_active' => false,
        'registration_status' => 'approved',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('all predefined system roles can access dashboard', function (string $roleName) {
    $user = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $user->assignRole($roleName);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(
        fn($page) => $page
            ->component('Dashboard')
            ->where('user.id', $user->id)
    );
})->with([
    'Super Admin',
    'Admin',
    'Inventory Staff',
    'Asset Staff',
    'Requester',
    'Manager',
]);
