<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('it verifies locations table and columns exist in schema', function () {
    expect(Schema::hasTable('locations'))->toBeTrue()
        ->and(Schema::hasColumns('locations', [
            'id',
            'parent_id',
            'code',
            'name',
            'type',
            'address',
            'is_active',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

test('it creates a location with required attributes and correct default values', function () {
    $locationId = DB::table('locations')->insertGetId([
        'code' => 'LOC-WH-01',
        'name' => 'Main Central Warehouse',
        'address' => 'Jl. Industri No. 45, Jakarta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $location = DB::table('locations')->where('id', $locationId)->first();

    expect($location)->not()->toBeNull()
        ->and($location->code)->toBe('LOC-WH-01')
        ->and($location->name)->toBe('Main Central Warehouse')
        ->and($location->type)->toBe('room') // default enum value
        ->and($location->address)->toBe('Jl. Industri No. 45, Jakarta')
        ->and((bool) $location->is_active)->toBeTrue() // default is_active
        ->and($location->parent_id)->toBeNull();
});

test('it enforces unique location code constraint', function () {
    DB::table('locations')->insert([
        'code' => 'LOC-DUP-01',
        'name' => 'Original Location',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('locations')->insert([
        'code' => 'LOC-DUP-01',
        'name' => 'Duplicate Location',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

test('it supports parent and child location self-referencing relationship', function () {
    $parentId = DB::table('locations')->insertGetId([
        'code' => 'LOC-BLD-A',
        'name' => 'Building A (Gedung Utama)',
        'type' => 'building',
        'address' => 'Kawasan Sentra Bisnis Blok A',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $childId = DB::table('locations')->insertGetId([
        'parent_id' => $parentId,
        'code' => 'LOC-BLD-A-R101',
        'name' => 'Room 101 - IT Server Room',
        'type' => 'room',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $childLocation = DB::table('locations')->where('id', $childId)->first();

    expect($childLocation->parent_id)->toBe($parentId)
        ->and($childLocation->type)->toBe('room');
});

test('it prevents deleting parent location when child locations exist (on delete restrict)', function () {
    $parentId = DB::table('locations')->insertGetId([
        'code' => 'LOC-WH-MAIN',
        'name' => 'Warehouse Main',
        'type' => 'warehouse',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('locations')->insert([
        'parent_id' => $parentId,
        'code' => 'LOC-WH-RACK-01',
        'name' => 'Rack Area 01',
        'type' => 'area',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Deleting parent must fail due to ON DELETE RESTRICT foreign key
    expect(fn () => DB::table('locations')->where('id', $parentId)->delete())->toThrow(QueryException::class);
});

test('it supports all valid location enum types', function () {
    $types = ['building', 'warehouse', 'room', 'area'];

    foreach ($types as $index => $type) {
        $id = DB::table('locations')->insertGetId([
            'code' => 'LOC-TYPE-' . ($index + 1),
            'name' => 'Location Type ' . ucfirst($type),
            'type' => $type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $location = DB::table('locations')->where('id', $id)->first();
        expect($location->type)->toBe($type);
    }
});
