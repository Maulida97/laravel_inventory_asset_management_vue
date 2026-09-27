<?php

use App\Models\User;
use Illuminate\Database\QueryException;

test('it creates a user with extended profile attributes', function () {
    $user = User::create([
        'name' => 'Ashia Maulida',
        'email' => 'maulida@assetflow.io',
        'password' => 'secure123',
        'phone_number' => '08812345678',
        'employee_id' => 'EMP-00001',
        'position' => 'IT Asset Specialist',
        'photo_path' => 'profiles/maulida.jpg',
        'join_date' => '2026-01-15',
        'is_active' => true,
        'registration_status' => 'approved',
    ]);

    expect($user->id)->not()->toBeNull()
        ->and($user->name)->toBe('Ashia Maulida')
        ->and($user->email)->toBe('maulida@assetflow.io')
        ->and($user->phone_number)->toBe('08812345678')
        ->and($user->employee_id)->toBe('EMP-00001')
        ->and($user->position)->toBe('IT Asset Specialist')
        ->and($user->photo_path)->toBe('profiles/maulida.jpg')
        ->and($user->join_date->format('Y-m-d'))->toBe('2026-01-15')
        ->and($user->is_active)->toBeTrue()
        ->and($user->registration_status)->toBe('approved');
});

test('it enforces unique employee_id', function () {
    User::factory()->create(['employee_id' => 'EMP-9999']);

    expect(fn () => User::factory()->create([
        'employee_id' => 'EMP-9999',
    ]))->toThrow(QueryException::class);
});

test('it supports user factory states', function () {
    $unverified = User::factory()->unverified()->create();
    $inactive = User::factory()->inactive()->create();
    $pending = User::factory()->pendingRegistration()->create();

    expect($unverified->email_verified_at)->toBeNull()
        ->and($inactive->is_active)->toBeFalse()
        ->and($pending->registration_status)->toBe('pending');
});