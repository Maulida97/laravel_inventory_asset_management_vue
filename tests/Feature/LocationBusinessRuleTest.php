<?php

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    $department = Department::factory()->create();

    $this->admin = User::factory()->create([
        'department_id' => $department->id,
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $this->admin->assignRole('Admin');

    $this->requester = User::factory()->create([
        'department_id' => $department->id,
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $this->requester->assignRole('Requester');

    // Reset stock & asset simulation resolvers
    Location::resolveStockUsing(null);
    Location::resolveAssetsUsing(null);
});

afterEach(function () {
    Location::resolveStockUsing(null);
    Location::resolveAssetsUsing(null);
});

/*
|--------------------------------------------------------------------------
| HAPPY PATH TESTS
|--------------------------------------------------------------------------
*/

test('BR-L01: parent location can accept direct stock only when it has no children, while children always can', function () {
    $parentWithoutChildren = Location::factory()->create([
        'parent_id' => null,
        'is_active' => true,
    ]);

    expect($parentWithoutChildren->canAcceptDirectStock())->toBeTrue();

    // Add a child
    $child = Location::factory()->create([
        'parent_id' => $parentWithoutChildren->id,
        'is_active' => true,
    ]);

    // Refresh parent to check with children
    $parentWithoutChildren->refresh();

    expect($parentWithoutChildren->canAcceptDirectStock())->toBeFalse()
        ->and($child->canAcceptDirectStock())->toBeTrue();

    // Verify scopeAcceptingStock includes child and parent without children, but excludes parent with children
    $anotherParentWithoutChild = Location::factory()->create(['parent_id' => null, 'is_active' => true]);
    $eligibleLocationIds = Location::acceptingStock()->pluck('id')->toArray();

    expect($eligibleLocationIds)->toContain($child->id)
        ->and($eligibleLocationIds)->toContain($anotherParentWithoutChild->id)
        ->and($eligibleLocationIds)->not->toContain($parentWithoutChildren->id);
});

test('BR-L02: creating a child under a parent with items succeeds and triggers warning flash message', function () {
    $parent = Location::factory()->create([
        'parent_id' => null,
        'name' => 'Gudang Pusat',
        'is_active' => true,
    ]);

    // Simulate parent holds 50 items
    Location::resolveStockUsing(fn (Location $loc) => $loc->id === $parent->id ? 50 : 0);

    $payload = [
        'code' => 'LOC-RACK-01',
        'name' => 'Rak Besi A',
        'type' => 'area',
        'parent_id' => $parent->id,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin)->post('/locations', $payload);

    $response->assertRedirect('/locations');
    $response->assertSessionHas('status');
    $response->assertSessionHas('warning', fn ($msg) => str_contains($msg, 'BR-L02') && str_contains($msg, 'Gudang Pusat'));

    $this->assertDatabaseHas('locations', [
        'code' => 'LOC-RACK-01',
        'parent_id' => $parent->id,
    ]);
});

test('BR-L02: creating a child under an empty parent succeeds without warning flash', function () {
    $parent = Location::factory()->create([
        'parent_id' => null,
        'name' => 'Gudang Kosong',
        'is_active' => true,
    ]);

    $payload = [
        'code' => 'LOC-RACK-02',
        'name' => 'Rak Besi B',
        'type' => 'area',
        'parent_id' => $parent->id,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin)->post('/locations', $payload);

    $response->assertRedirect('/locations');
    $response->assertSessionHas('status');
    $response->assertSessionMissing('warning');
});

test('BR-L03: an empty location can be successfully deactivated, cascading deactivation to empty children', function () {
    $parent = Location::factory()->create([
        'parent_id' => null,
        'is_active' => true,
    ]);

    $child = Location::factory()->create([
        'parent_id' => $parent->id,
        'is_active' => true,
    ]);

    // Location is empty
    $response = $this->actingAs($this->admin)->patch("/locations/{$parent->id}/toggle-status");

    $response->assertRedirect('/locations');
    $response->assertSessionHas('status');
    $response->assertSessionMissing('error');

    $parent->refresh();
    $child->refresh();

    expect($parent->is_active)->toBeFalse()
        ->and($child->is_active)->toBeFalse(); // Cascade deactivation verified
});

test('BR-L05: an empty child location can be successfully moved to another active parent', function () {
    $parentA = Location::factory()->create(['parent_id' => null, 'is_active' => true]);
    $parentB = Location::factory()->create(['parent_id' => null, 'is_active' => true]);

    $child = Location::factory()->create([
        'parent_id' => $parentA->id,
        'is_active' => true,
    ]);

    $payload = [
        'code' => $child->code,
        'name' => $child->name,
        'type' => $child->type,
        'parent_id' => $parentB->id,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin)->put("/locations/{$child->id}", $payload);

    $response->assertRedirect('/locations');
    $response->assertSessionHas('status');

    $child->refresh();
    expect($child->parent_id)->toBe($parentB->id);
});

/*
|--------------------------------------------------------------------------
| EVIL PATH & SECURITY TESTS
|--------------------------------------------------------------------------
*/

test('BR-L03 (Evil Path): deactivating a location that holds stock is rejected with error flash', function () {
    $location = Location::factory()->create(['is_active' => true]);

    // Simulate location has active items
    Location::resolveStockUsing(fn (Location $loc) => $loc->id === $location->id ? 25 : 0);

    $response = $this->actingAs($this->admin)->patch("/locations/{$location->id}/toggle-status");

    $response->assertRedirect();
    $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'BR-L03'));

    $location->refresh();
    expect($location->is_active)->toBeTrue();
});

test('BR-L03 (Evil Path): deactivating a parent location whose child holds active assets is rejected', function () {
    $parent = Location::factory()->create(['parent_id' => null, 'is_active' => true]);
    $child = Location::factory()->create(['parent_id' => $parent->id, 'is_active' => true]);

    // Simulate child has 1 active asset
    Location::resolveAssetsUsing(fn (Location $loc) => $loc->id === $child->id ? 1 : 0);

    $response = $this->actingAs($this->admin)->patch("/locations/{$parent->id}/toggle-status");

    $response->assertRedirect();
    $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'BR-L03'));

    $parent->refresh();
    expect($parent->is_active)->toBeTrue();
});

test('BR-L03 (Evil Path): updating is_active to false via form when location has items is rejected by validation', function () {
    $location = Location::factory()->create(['is_active' => true]);

    // Simulate items present
    Location::resolveStockUsing(fn (Location $loc) => $loc->id === $location->id ? 10 : 0);

    $payload = [
        'code' => $location->code,
        'name' => $location->name,
        'type' => $location->type,
        'is_active' => false,
    ];

    $response = $this->actingAs($this->admin)->put("/locations/{$location->id}", $payload);

    $response->assertSessionHasErrors(['is_active']);

    $location->refresh();
    expect($location->is_active)->toBeTrue();
});

test('BR-L03 (Evil Path): activating a sub-location whose parent is inactive is rejected', function () {
    $parent = Location::factory()->create(['parent_id' => null, 'is_active' => false]);
    $child = Location::factory()->create(['parent_id' => $parent->id, 'is_active' => false]);

    $response = $this->actingAs($this->admin)->patch("/locations/{$child->id}/toggle-status");

    $response->assertRedirect();
    $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'nonaktif'));

    $child->refresh();
    expect($child->is_active)->toBeFalse();
});

test('BR-L04 (Evil Path): permanent deletion of a location is explicitly denied by Policy', function () {
    $location = Location::factory()->create();

    // Check Gate authorization directly
    $response = Gate::forUser($this->admin)->inspect('delete', $location);

    expect($response->allowed())->toBeFalse()
        ->and($response->message())->toContain('BR-L04');

    // HTTP DELETE route does not exist
    $this->actingAs($this->admin)->delete("/locations/{$location->id}")
        ->assertStatus(405);
});

test('BR-L05 (Evil Path): moving a child location that holds stock to another parent is rejected', function () {
    $parentA = Location::factory()->create(['parent_id' => null, 'is_active' => true]);
    $parentB = Location::factory()->create(['parent_id' => null, 'is_active' => true]);

    $child = Location::factory()->create([
        'parent_id' => $parentA->id,
        'is_active' => true,
    ]);

    // Simulate child has 100 laptops
    Location::resolveStockUsing(fn (Location $loc) => $loc->id === $child->id ? 100 : 0);

    $payload = [
        'code' => $child->code,
        'name' => $child->name,
        'type' => $child->type,
        'parent_id' => $parentB->id,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin)->put("/locations/{$child->id}", $payload);

    $response->assertSessionHasErrors(['parent_id']);

    $child->refresh();
    expect($child->parent_id)->toBe($parentA->id); // Remains unchanged
});

test('Security & Authorization: user without location permissions (Requester) is denied with 403 Forbidden', function () {
    $location = Location::factory()->create(['is_active' => true]);

    // Unauthorized creation
    $this->actingAs($this->requester)
        ->post('/locations', [
            'code' => 'LOC-HACK',
            'name' => 'Hack Location',
            'type' => 'warehouse',
        ])
        ->assertForbidden();

    // Unauthorized update
    $this->actingAs($this->requester)
        ->put("/locations/{$location->id}", [
            'code' => $location->code,
            'name' => 'Hacked Name',
            'type' => $location->type,
        ])
        ->assertForbidden();

    // Unauthorized toggle status
    $this->actingAs($this->requester)
        ->patch("/locations/{$location->id}/toggle-status")
        ->assertForbidden();
});

test('Security & Authorization: unauthenticated guest is redirected to login', function () {
    $location = Location::factory()->create();

    $this->get('/locations')->assertRedirect('/login');
    $this->post('/locations', [])->assertRedirect('/login');
    $this->put("/locations/{$location->id}", [])->assertRedirect('/login');
    $this->patch("/locations/{$location->id}/toggle-status")->assertRedirect('/login');
});

