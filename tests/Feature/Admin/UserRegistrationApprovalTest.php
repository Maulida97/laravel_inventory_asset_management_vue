<?php

use App\Models\Department;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RoleSeeder::class,
        PermissionSeeder::class,
    ]);
});

test('guest cannot access user registration approval page', function () {
    $response = $this->get('/settings/user-registrations');

    $response->assertRedirect(route('login'));
});

test('non-super admin roles cannot access user registration approval page', function () {
    $manager = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $manager->assignRole('Manager');

    $response = $this->actingAs($manager)->get('/settings/user-registrations');

    $response->assertStatus(403);
});

test('super admin can view pending user registrations', function () {
    $superAdmin = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $superAdmin->assignRole('Super Admin');

    $dept = Department::factory()->create(['is_active' => true, 'name' => 'Technology']);
    $pendingUser = User::factory()->pendingRegistration()->inactive()->create([
        'department_id' => $dept->id,
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
    ]);

    $response = $this->actingAs($superAdmin)->get('/settings/user-registrations');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/UserRegistrations/Index')
        ->has('pendingUsers.data', 1)
        ->where('pendingUsers.data.0.id', $pendingUser->id)
        ->where('pendingUsers.data.0.name', 'John Doe')
        ->has('availableRoles')
        ->has('departments')
    );
});

test('super admin can approve pending registration and assign roles', function () {
    $superAdmin = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $superAdmin->assignRole('Super Admin');

    $pendingUser = User::factory()->pendingRegistration()->inactive()->create([
        'name' => 'Applicant One',
        'email' => 'applicant@example.com',
    ]);

    $response = $this->actingAs($superAdmin)->post("/settings/user-registrations/{$pendingUser->id}/approve", [
        'roles' => ['Requester', 'Inventory Staff'],
    ]);

    $response->assertRedirect(route('admin.user-registrations.index'));
    $response->assertSessionHas('status');

    $pendingUser->refresh();
    expect($pendingUser->registration_status)->toBe('approved');
    expect($pendingUser->is_active)->toBeTrue();
    expect($pendingUser->hasRole('Requester'))->toBeTrue();
    expect($pendingUser->hasRole('Inventory Staff'))->toBeTrue();
});

test('approving registration requires at least one valid role', function () {
    $superAdmin = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $superAdmin->assignRole('Super Admin');

    $pendingUser = User::factory()->pendingRegistration()->inactive()->create();

    $response = $this->actingAs($superAdmin)->from('/settings/user-registrations')
        ->post("/settings/user-registrations/{$pendingUser->id}/approve", [
            'roles' => [],
        ]);

    $response->assertSessionHasErrors('roles');

    $pendingUser->refresh();
    expect($pendingUser->registration_status)->toBe('pending');
    expect($pendingUser->is_active)->toBeFalse();
});

test('approving registration fails with invalid role name', function () {
    $superAdmin = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $superAdmin->assignRole('Super Admin');

    $pendingUser = User::factory()->pendingRegistration()->inactive()->create();

    $response = $this->actingAs($superAdmin)->from('/settings/user-registrations')
        ->post("/settings/user-registrations/{$pendingUser->id}/approve", [
            'roles' => ['Invalid Role Name'],
        ]);

    $response->assertSessionHasErrors('roles.0');
});

test('super admin can reject pending user registration', function () {
    $superAdmin = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $superAdmin->assignRole('Super Admin');

    $pendingUser = User::factory()->pendingRegistration()->inactive()->create([
        'name' => 'Rejected Candidate',
    ]);

    $response = $this->actingAs($superAdmin)->post("/settings/user-registrations/{$pendingUser->id}/reject");

    $response->assertRedirect(route('admin.user-registrations.index'));
    $response->assertSessionHas('status');

    $pendingUser->refresh();
    expect($pendingUser->registration_status)->toBe('rejected');
    expect($pendingUser->is_active)->toBeFalse();
    expect($pendingUser->roles)->toBeEmpty();
});

test('cannot approve or reject user that is not in pending status', function () {
    $superAdmin = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $superAdmin->assignRole('Super Admin');

    $alreadyApprovedUser = User::factory()->create([
        'registration_status' => 'approved',
        'is_active' => true,
    ]);

    $response = $this->actingAs($superAdmin)->post("/settings/user-registrations/{$alreadyApprovedUser->id}/approve", [
        'roles' => ['Requester'],
    ]);

    $response->assertSessionHasErrors('error');
});

test('approved user can successfully login with registered password', function () {
    $dept = Department::factory()->create(['is_active' => true]);
    $superAdmin = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $superAdmin->assignRole('Super Admin');

    // 1. Self register
    $this->post('/register', [
        'name' => 'Newly Approved User',
        'email' => 'newapproved@example.com',
        'department_id' => $dept->id,
        'password' => 'secretPassword123',
        'password_confirmation' => 'secretPassword123',
    ]);

    $user = User::where('email', 'newapproved@example.com')->first();

    // 2. Super admin approves
    $this->actingAs($superAdmin)->post("/settings/user-registrations/{$user->id}/approve", [
        'roles' => ['Requester'],
    ]);

    // Logout super admin
    $this->post('/logout');

    // 3. User attempts login
    $loginResponse = $this->post('/login', [
        'email' => 'newapproved@example.com',
        'password' => 'secretPassword123',
    ]);

    $loginResponse->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});
