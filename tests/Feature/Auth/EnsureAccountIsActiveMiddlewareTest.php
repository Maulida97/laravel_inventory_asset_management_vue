<?php

use App\Models\User;

test('authenticated active and approved user can access protected routes', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('guest cannot access protected routes and is redirected to login', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated user with is_active false is logged out and redirected to login', function () {
    $user = User::factory()->create([
        'is_active' => false,
        'registration_status' => 'approved',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => __('Akun Anda telah dinonaktifkan. Silakan hubungi Administrator IT.'),
    ]);
});

test('authenticated user with pending registration status is logged out and redirected to login', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'pending',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => __('Pendaftaran akun Anda masih menunggu persetujuan dari Super Admin.'),
    ]);
});

test('authenticated user with rejected registration status is logged out and redirected to login', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'registration_status' => 'rejected',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => __('Pendaftaran akun Anda ditolak. Silakan hubungi Administrator IT.'),
    ]);
});
