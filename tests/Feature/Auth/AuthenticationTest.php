<?php

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;

test('login screen can be rendered for guest', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
});

test('authenticated users are redirected from login screen to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('dashboard'));
});

test('email is required to authenticate', function () {
    $response = $this->from('/login')->post('/login', [
        'email' => '',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('password is required to authenticate', function () {
    $response = $this->from('/login')->post('/login', [
        'email' => 'staff@assetflow.io',
        'password' => '',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('password');
});

test('email must be a valid email format', function () {
    $response = $this->from('/login')->post('/login', [
        'email' => 'invalid-email-format',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('users can authenticate using valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    $response = $this->post('/login', [
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));
});

test('users can authenticate with case-insensitive email', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    $response = $this->post('/login', [
        'email' => 'STAFF@ASSETFLOW.IO',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));
});

test('users cannot authenticate with non-existent email', function () {
    $response = $this->from('/login')->post('/login', [
        'email' => 'nonexistent@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => __('Email atau kata sandi yang Anda masukkan salah.'),
    ]);
});

test('users cannot authenticate with invalid password', function () {
    User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'staff@assetflow.io',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => __('Email atau kata sandi yang Anda masukkan salah.'),
    ]);
});

test('inactive users cannot authenticate', function () {
    User::factory()->inactive()->create([
        'email' => 'inactive@assetflow.io',
        'password' => 'secret123',
        'registration_status' => 'approved',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'inactive@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => __('Akun Anda telah dinonaktifkan. Silakan hubungi Administrator IT.'),
    ]);
});

test('pending registration users cannot authenticate', function () {
    User::factory()->pendingRegistration()->create([
        'email' => 'pending@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'pending@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => __('Pendaftaran akun Anda masih menunggu persetujuan dari Super Admin.'),
    ]);
});

test('rejected registration users cannot authenticate', function () {
    User::factory()->rejectedRegistration()->create([
        'email' => 'rejected@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'rejected@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => __('Pendaftaran akun Anda ditolak. Silakan hubungi Administrator IT.'),
    ]);
});

test('users can authenticate with remember me enabled', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    $response = $this->post('/login', [
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'remember' => true,
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));
    $response->assertCookie(Auth::guard('web')->getRecallerName());
});

test('users can authenticate without remember me enabled', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    $response = $this->post('/login', [
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'remember' => false,
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));
    $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
});

test('users are redirected to intended url after successful authentication', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    // Guest tries to access protected page first
    $this->get('/dashboard')->assertRedirect('/login');

    // Guest authenticates via login
    $response = $this->post('/login', [
        'email' => 'staff@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('users receive standard invalid credentials error for attempts 1 to 3 and 5', function () {
    User::factory()->create([
        'email' => 'ratelimit@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    for ($i = 1; $i <= 3; $i++) {
        $response = $this->from('/login')->post('/login', [
            'email' => 'ratelimit@assetflow.io',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => __('Email atau kata sandi yang Anda masukkan salah.'),
        ]);
    }
});

test('users receive warning message on the 4th failed login attempt', function () {
    User::factory()->create([
        'email' => 'warning4@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    // 3 failed attempts
    for ($i = 1; $i <= 3; $i++) {
        $this->from('/login')->post('/login', [
            'email' => 'warning4@assetflow.io',
            'password' => 'wrong-password',
        ]);
    }

    // 4th attempt should return specific warning message
    $response = $this->from('/login')->post('/login', [
        'email' => 'warning4@assetflow.io',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Percobaan ke-4 dari 5');
    expect(session('errors')->first('email'))->toContain('dikunci selama 15 menit');
});

test('users are locked out on the 5th consecutive failed login attempt for 15 minutes', function () {
    User::factory()->create([
        'email' => 'lockedout@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    // 4 failed attempts
    for ($i = 1; $i <= 4; $i++) {
        $this->from('/login')->post('/login', [
            'email' => 'lockedout@assetflow.io',
            'password' => 'wrong-password',
        ]);
    }

    // 5th attempt should immediately trigger lockout
    $response = $this->from('/login')->post('/login', [
        'email' => 'lockedout@assetflow.io',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Terlalu banyak percobaan login');
    expect(session('errors')->first('email'))->toContain('dikunci sementara');

    // 6th attempt (even with correct password) is blocked by ensureIsNotRateLimited
    $responseBlocked = $this->from('/login')->post('/login', [
        'email' => 'lockedout@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $responseBlocked->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Terlalu banyak percobaan login');
});

test('lockout event is dispatched when login rate limit is exceeded', function () {
    Event::fake([Lockout::class]);

    User::factory()->create([
        'email' => 'eventlockout@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    // 4 failed attempts
    for ($i = 1; $i <= 4; $i++) {
        $this->from('/login')->post('/login', [
            'email' => 'eventlockout@assetflow.io',
            'password' => 'wrong-password',
        ]);
    }

    Event::assertNotDispatched(Lockout::class);

    // 5th attempt triggers lockout event
    $this->from('/login')->post('/login', [
        'email' => 'eventlockout@assetflow.io',
        'password' => 'wrong-password',
    ]);

    Event::assertDispatched(Lockout::class);
});

test('successful login clears the failed attempts rate limiter counter', function () {
    User::factory()->create([
        'email' => 'clearlimit@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    // 3 failed attempts
    for ($i = 1; $i <= 3; $i++) {
        $this->from('/login')->post('/login', [
            'email' => 'clearlimit@assetflow.io',
            'password' => 'wrong-password',
        ]);
    }

    // Successful attempt
    $successResponse = $this->post('/login', [
        'email' => 'clearlimit@assetflow.io',
        'password' => 'secret123',
    ]);

    $successResponse->assertRedirect(route('dashboard'));

    // Logout
    $this->post('/logout');

    // Should be able to fail another 5 times without immediate lockout
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->from('/login')->post('/login', [
            'email' => 'clearlimit@assetflow.io',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        if ($i === 4) {
            expect(session('errors')->first('email'))->toContain('Percobaan ke-4 dari 5');
        }
    }
});

test('rate limiting throttle key is case-insensitive for email', function () {
    User::factory()->create([
        'email' => 'caseinsensitive@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    // 5 failed attempts with varying case
    $emails = [
        'caseinsensitive@assetflow.io',
        'CASEINSENSITIVE@ASSETFLOW.IO',
        'CaseInsensitive@AssetFlow.io',
        'caseINSENSITIVE@assetflow.IO',
        'CASEinsensitive@ASSETFLOW.io',
    ];

    foreach ($emails as $email) {
        $this->from('/login')->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    // 6th attempt with lowercase should be locked out
    $response = $this->from('/login')->post('/login', [
        'email' => 'caseinsensitive@assetflow.io',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Terlalu banyak percobaan login');
});

test('rate limiting is isolated per email and ip address', function () {
    User::factory()->create([
        'email' => 'user_one@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    User::factory()->create([
        'email' => 'user_two@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    // Lock out user_one with 5 failed attempts
    for ($i = 1; $i <= 5; $i++) {
        $this->from('/login')->post('/login', [
            'email' => 'user_one@assetflow.io',
            'password' => 'wrong-password',
        ]);
    }

    // user_one 6th attempt is locked out
    $responseOne = $this->from('/login')->post('/login', [
        'email' => 'user_one@assetflow.io',
        'password' => 'secret123',
    ]);
    $responseOne->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Terlalu banyak percobaan login');

    // user_two should NOT be locked out and can login successfully
    $responseTwo = $this->post('/login', [
        'email' => 'user_two@assetflow.io',
        'password' => 'secret123',
    ]);
    $responseTwo->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs(User::where('email', 'user_two@assetflow.io')->first());
});


