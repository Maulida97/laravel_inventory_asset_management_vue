<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('registration screen can be rendered for guest with active departments', function () {
    $activeDept = Department::factory()->create(['is_active' => true, 'name' => 'IT Department']);
    $inactiveDept = Department::factory()->create(['is_active' => false, 'name' => 'Archived Department']);

    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Register')
        ->has('departments', 1)
        ->where('departments.0.id', $activeDept->id)
    );
});

test('authenticated users are redirected from registration screen to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    $response->assertRedirect(route('dashboard'));
});

test('new user can register with valid data and gets pending inactive status', function () {
    $dept = Department::factory()->create(['is_active' => true]);

    $response = $this->post('/register', [
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
        'employee_id' => 'EMP-00123',
        'department_id' => $dept->id,
        'position' => 'Software Engineer',
        'phone_number' => '081234567890',
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
        'employee_id' => 'EMP-00123',
        'department_id' => $dept->id,
        'position' => 'Software Engineer',
        'phone_number' => '081234567890',
        'is_active' => false,
        'registration_status' => 'pending',
    ]);

    $this->assertGuest();
});

test('registration requires name, email, department_id, and password', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => '',
        'email' => '',
        'department_id' => '',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['name', 'email', 'department_id', 'password']);
});

test('registration fails if email is already taken', function () {
    $dept = Department::factory()->create(['is_active' => true]);
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Another User',
        'email' => 'EXISTING@EXAMPLE.COM', // Test case-insensitivity
        'department_id' => $dept->id,
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('registration fails if employee_id is already taken', function () {
    $dept = Department::factory()->create(['is_active' => true]);
    User::factory()->create(['employee_id' => 'EMP-11111']);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Another User',
        'email' => 'newuser@example.com',
        'employee_id' => 'EMP-11111',
        'department_id' => $dept->id,
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('employee_id');
});

test('registration fails if department does not exist or is inactive', function () {
    $inactiveDept = Department::factory()->create(['is_active' => false]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'department_id' => $inactiveDept->id,
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('department_id');
});

test('registration fails if password confirmation does not match', function () {
    $dept = Department::factory()->create(['is_active' => true]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'department_id' => $dept->id,
        'password' => 'secret12345',
        'password_confirmation' => 'different-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('password');
});

test('registration fails if password is shorter than 8 characters', function () {
    $dept = Department::factory()->create(['is_active' => true]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'department_id' => $dept->id,
        'password' => '1234567',
        'password_confirmation' => '1234567',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('password');
});

test('newly registered user cannot log in while registration status is pending', function () {
    $dept = Department::factory()->create(['is_active' => true]);

    $this->post('/register', [
        'name' => 'Pending User',
        'email' => 'pending@example.com',
        'department_id' => $dept->id,
        'password' => 'secret12345',
        'password_confirmation' => 'secret12345',
    ]);

    // Try logging in
    $response = $this->from('/login')->post('/login', [
        'email' => 'pending@example.com',
        'password' => 'secret12345',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});
