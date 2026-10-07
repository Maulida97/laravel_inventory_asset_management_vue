<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $requester;
    private User $inventoryStaff;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $department = Department::factory()->create();

        $this->superAdmin = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
            'registration_status' => 'approved',
        ]);
        $this->superAdmin->assignRole('Super Admin');

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

        $this->inventoryStaff = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
            'registration_status' => 'approved',
        ]);
        $this->inventoryStaff->assignRole('Inventory Staff');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/locations');
        $response->assertRedirect('/login');
    }

    public function test_all_authenticated_roles_with_view_permission_can_access_location_index(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->superAdmin)->get('/locations')->assertOk();
        $this->actingAs($this->admin)->get('/locations')->assertOk();
        $this->actingAs($this->requester)->get('/locations')->assertOk();
        $this->actingAs($this->inventoryStaff)->get('/locations')->assertOk();
    }

    public function test_admin_can_create_parent_location(): void
    {
        $payload = [
            'code' => 'LOC-HQ01',
            'name' => 'Headquarter Building',
            'type' => 'building',
            'parent_id' => null,
            'address' => 'Jl. Jendral Sudirman Kav. 1',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/locations', $payload);

        $response->assertRedirect('/locations');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('locations', [
            'code' => 'LOC-HQ01',
            'name' => 'Headquarter Building',
            'type' => 'building',
            'parent_id' => null,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_child_location_under_parent(): void
    {
        $parent = Location::factory()->parent()->create(['code' => 'LOC-BLD01']);

        $payload = [
            'code' => 'LOC-ROOM101',
            'name' => 'Server Room A',
            'type' => 'room',
            'parent_id' => $parent->id,
            'address' => 'Floor 2, Left Wing',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/locations', $payload);

        $response->assertRedirect('/locations');
        $this->assertDatabaseHas('locations', [
            'code' => 'LOC-ROOM101',
            'parent_id' => $parent->id,
            'type' => 'room',
        ]);
    }

    public function test_location_creation_fails_when_assigning_child_as_parent(): void
    {
        $parent = Location::factory()->parent()->create();
        $child = Location::factory()->child($parent)->create();

        $payload = [
            'code' => 'LOC-SUBROOM',
            'name' => 'Sub Room 1',
            'type' => 'room',
            'parent_id' => $child->id, // Invalid: hierarchy is limited to 2 levels
        ];

        $response = $this->actingAs($this->admin)->post('/locations', $payload);

        $response->assertSessionHasErrors(['parent_id']);
    }

    public function test_location_creation_requires_unique_code(): void
    {
        Location::factory()->create(['code' => 'LOC-UNIQUE']);

        $payload = [
            'code' => 'LOC-UNIQUE',
            'name' => 'Duplicate Location',
            'type' => 'warehouse',
        ];

        $response = $this->actingAs($this->admin)->post('/locations', $payload);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_non_admin_cannot_create_location(): void
    {
        $payload = [
            'code' => 'LOC-NEW',
            'name' => 'Unauthorized Location',
            'type' => 'room',
        ];

        $response = $this->actingAs($this->requester)->post('/locations', $payload);

        $response->assertForbidden();
    }

    public function test_admin_can_update_location(): void
    {
        $location = Location::factory()->create([
            'code' => 'LOC-OLD',
            'name' => 'Old Name',
            'type' => 'room',
        ]);

        $payload = [
            'code' => 'LOC-OLD',
            'name' => 'Updated Name',
            'type' => 'area',
            'address' => 'Updated Address',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->put("/locations/{$location->id}", $payload);

        $response->assertRedirect('/locations');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Name',
            'type' => 'area',
            'address' => 'Updated Address',
        ]);
    }

    public function test_location_cannot_be_its_own_parent_on_update(): void
    {
        $location = Location::factory()->parent()->create();

        $payload = [
            'code' => $location->code,
            'name' => $location->name,
            'type' => $location->type,
            'parent_id' => $location->id, // Invalid: cannot be parent of self
        ];

        $response = $this->actingAs($this->admin)->put("/locations/{$location->id}", $payload);

        $response->assertSessionHasErrors(['parent_id']);
    }

    public function test_parent_with_children_cannot_be_turned_into_a_child(): void
    {
        $parent1 = Location::factory()->parent()->create();
        $child = Location::factory()->child($parent1)->create();

        $parent2 = Location::factory()->parent()->create();

        // Attempting to turn parent1 (which has children) into a child of parent2
        $payload = [
            'code' => $parent1->code,
            'name' => $parent1->name,
            'type' => 'room',
            'parent_id' => $parent2->id,
        ];

        $response = $this->actingAs($this->admin)->put("/locations/{$parent1->id}", $payload);

        $response->assertSessionHasErrors(['parent_id']);
    }

    public function test_admin_can_toggle_location_status(): void
    {
        $location = Location::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->patch("/locations/{$location->id}/toggle-status");

        $response->assertRedirect('/locations');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'is_active' => false,
        ]);

        $response2 = $this->actingAs($this->admin)->patch("/locations/{$location->id}/toggle-status");
        $response2->assertRedirect('/locations');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_toggle_location_status(): void
    {
        $location = Location::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->requester)->patch("/locations/{$location->id}/toggle-status");

        $response->assertForbidden();
    }
}
