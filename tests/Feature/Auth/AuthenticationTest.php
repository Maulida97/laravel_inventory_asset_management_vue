<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
