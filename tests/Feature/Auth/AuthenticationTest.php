<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('login screen can be rendered for guest', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
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

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create([
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
    $response->assertSessionHasErrors('email');
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->create([
        'email' => 'inactive@assetflow.io',
        'password' => 'secret123',
        'is_active' => false,
        'registration_status' => 'approved',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'inactive@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('pending registration users cannot authenticate', function () {
    $user = User::factory()->create([
        'email' => 'pending@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'pending',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'pending@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('rejected registration users cannot authenticate', function () {
    $user = User::factory()->create([
        'email' => 'rejected@assetflow.io',
        'password' => 'secret123',
        'is_active' => true,
        'registration_status' => 'rejected',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => 'rejected@assetflow.io',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('authenticated users are redirected from login screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect(route('dashboard'));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
