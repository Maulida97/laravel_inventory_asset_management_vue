<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('reset password link screen can be rendered for guest', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/ForgotPassword'));
});

test('reset password link screen redirects authenticated user to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/forgot-password');

    $response->assertRedirect(route('dashboard'));
});

test('reset password link can be requested with valid email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'staff@assetflow.io',
    ]);

    $response->assertSessionHas('status', __(Password::RESET_LINK_SENT));
    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset password link request is case-insensitive for email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'STAFF@ASSETFLOW.IO',
    ]);

    $response->assertSessionHas('status', __(Password::RESET_LINK_SENT));
    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset password link request fails with empty email', function () {
    $response = $this->from('/forgot-password')->post('/forgot-password', [
        'email' => '',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset password link request fails with invalid email format', function () {
    $response = $this->from('/forgot-password')->post('/forgot-password', [
        'email' => 'invalid-email-format',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset password screen can be rendered with token for guest', function () {
    $response = $this->get('/reset-password/sample-token?email=staff@assetflow.io');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Auth/ResetPassword')
            ->where('token', 'sample-token')
            ->where('email', 'staff@assetflow.io')
    );
});

test('password can be reset with valid token and matching confirmation', function () {
    Event::fake();

    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'oldpassword123',
    ]);

    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'staff@assetflow.io',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHas('status', __(Password::PASSWORD_RESET));
    $response->assertRedirect(route('login'));

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
    Event::assertDispatched(PasswordReset::class);
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
        'password' => 'oldpassword123',
    ]);

    $response = $this->from('/reset-password/invalid-token')->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'staff@assetflow.io',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('email');

    $user->refresh();
    expect(Hash::check('oldpassword123', $user->password))->toBeTrue();
});

test('password cannot be reset with mismatching password confirmation', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
    ]);

    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'staff@assetflow.io',
        'password' => 'newpassword123',
        'password_confirmation' => 'differentpassword',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password cannot be reset with password shorter than 8 characters', function () {
    $user = User::factory()->create([
        'email' => 'staff@assetflow.io',
    ]);

    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'staff@assetflow.io',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
});
