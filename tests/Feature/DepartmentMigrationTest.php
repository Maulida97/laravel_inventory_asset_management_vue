<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\QueryException;

test('it creates a department with required attributes', function () {
    $department = Department::create([
        'code' => 'DEP-IT',
        'name' => 'Information Technology',
        'description' => 'Handles IT infrastructure and software systems',
        'is_active' => true,
    ]);

    expect($department->id)->not()->toBeNull()
        ->and($department->code)->toBe('DEP-IT')
        ->and($department->name)->toBe('Information Technology')
        ->and($department->description)->toBe('Handles IT infrastructure and software systems')
        ->and($department->is_active)->toBeTrue();
});

test('it enforces unique department code', function () {
    Department::factory()->create(['code' => 'DEP-HR']);

    expect(fn () => Department::factory()->create([
        'code' => 'DEP-HR',
    ]))->toThrow(QueryException::class);
});

test('it associates users with department and enforces foreign key relationship', function () {
    $department = Department::factory()->create([
        'code' => 'DEP-FIN',
        'name' => 'Finance',
    ]);

    $user = User::factory()->create([
        'department_id' => $department->id,
        'name' => 'Finance Staff',
    ]);

    expect($user->department)->not()->toBeNull()
        ->and($user->department->id)->toBe($department->id)
        ->and($user->department->code)->toBe('DEP-FIN')
        ->and($department->users)->toHaveCount(1)
        ->and($department->users->first()->id)->toBe($user->id);
});

test('it prevents deleting department when referenced by user (on delete restrict)', function () {
    $department = Department::factory()->create([
        'code' => 'DEP-OPS',
        'name' => 'Operations',
    ]);

    User::factory()->create([
        'department_id' => $department->id,
    ]);

    expect(fn () => $department->delete())->toThrow(QueryException::class);
});

test('it supports department factory states', function () {
    $inactiveDept = Department::factory()->inactive()->create();

    expect($inactiveDept->is_active)->toBeFalse();
});
