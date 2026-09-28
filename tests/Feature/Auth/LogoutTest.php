<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('authenticated user can logout via post request', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('logout invalidates the session and regenerates csrf token', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $sessionIdBefore = session()->getId();
    $tokenBefore = session()->token();

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));

    expect(session()->getId())->not()->toBe($sessionIdBefore)
        ->and(session()->token())->not()->toBe($tokenBefore);
});

test('unauthenticated user cannot access logout endpoint', function () {
    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
