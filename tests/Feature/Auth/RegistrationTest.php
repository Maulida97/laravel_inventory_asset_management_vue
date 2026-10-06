<?php

use App\Models\Department;
use App\Models\User;
use App\Notifications\NewUserRegistrationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

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

test('it sends NewUserRegistrationNotification to active Super Admins upon successful registration', function () {
    Notification::fake();

    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

    $superAdmin = User::factory()->create([
        'email' => 'superadmin@assetflow.io',
        'is_active' => true,
    ]);
    $superAdmin->assignRole('Super Admin');

    $inactiveSuperAdmin = User::factory()->create([
        'email' => 'inactive.superadmin@assetflow.io',
        'is_active' => false,
    ]);
    $inactiveSuperAdmin->assignRole('Super Admin');

    $regularUser = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'is_active' => true,
    ]);
    $regularUser->assignRole('Staff');

    $dept = Department::factory()->create(['is_active' => true, 'name' => 'Finance']);

    $response = $this->post('/register', [
        'name' => 'Alice Wonder',
        'email' => 'alice@company.com',
        'employee_id' => 'EMP-777',
        'department_id' => $dept->id,
        'position' => 'Finance Analyst',
        'phone_number' => '081122334455',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('login'));

    Notification::assertSentTo(
        [$superAdmin],
        NewUserRegistrationNotification::class,
        function (NewUserRegistrationNotification $notification, array $channels) use ($dept) {
            $mailData = $notification->toArray($notification->newUser);
            return in_array('mail', $channels)
                && $notification->newUser->email === 'alice@company.com'
                && $notification->newUser->name === 'Alice Wonder'
                && $mailData['employee_id'] === 'EMP-777'
                && $mailData['department_name'] === 'Finance';
        }
    );

    Notification::assertNotSentTo([$inactiveSuperAdmin], NewUserRegistrationNotification::class);
    Notification::assertNotSentTo([$regularUser], NewUserRegistrationNotification::class);
});

test('it does not send notification when registration validation fails', function () {
    Notification::fake();

    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

    $superAdmin = User::factory()->create(['is_active' => true]);
    $superAdmin->assignRole('Super Admin');

    $this->from('/register')->post('/register', [
        'name' => '',
        'email' => 'invalid-email',
        'department_id' => '',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    Notification::assertNothingSent();
});

test('registration succeeds gracefully without errors when no active Super Admin exists', function () {
    Notification::fake();

    $dept = Department::factory()->create(['is_active' => true]);

    $response = $this->post('/register', [
        'name' => 'Solo User',
        'email' => 'solo@company.com',
        'department_id' => $dept->id,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('users', ['email' => 'solo@company.com']);
    Notification::assertNothingSent();
});

test('NewUserRegistrationNotification renders mail message with review action URL and recipient', function () {
    $dept = Department::factory()->create(['name' => 'Technology']);
    $newUser = User::factory()->create([
        'name' => 'John Developer',
        'email' => 'john.dev@company.com',
        'employee_id' => 'EMP-0099',
        'position' => 'Fullstack Dev',
        'department_id' => $dept->id,
    ]);

    $admin = User::factory()->create(['name' => 'Super Admin Boss']);

    $notification = new NewUserRegistrationNotification($newUser);
    $mailMessage = $notification->toMail($admin);

    expect($mailMessage->viewData['admin']->name)->toBe('Super Admin Boss');
    expect($mailMessage->viewData['newUser']->name)->toBe('John Developer');
    expect($mailMessage->viewData['reviewUrl'])->toBe(route('admin.user-registrations.index'));
    expect($mailMessage->subject)->toContain('Pendaftaran Akun Baru Menunggu Persetujuan');
});
