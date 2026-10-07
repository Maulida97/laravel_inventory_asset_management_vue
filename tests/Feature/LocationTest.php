<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_location_using_factory(): void
    {
        $location = Location::factory()->create([
            'code' => 'LOC-001',
            'name' => 'Main Office Building',
            'type' => 'building',
            'address' => 'Jl. Jend. Sudirman No. 10',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'code' => 'LOC-001',
            'name' => 'Main Office Building',
            'type' => 'building',
            'is_active' => true,
        ]);
    }

    public function test_is_active_attribute_is_cast_to_boolean(): void
    {
        $location = Location::factory()->create([
            'is_active' => 1,
        ]);

        $this->assertIsBool($location->is_active);
        $this->assertTrue($location->is_active);

        $inactive = Location::factory()->inactive()->create();
        $this->assertIsBool($inactive->is_active);
        $this->assertFalse($inactive->is_active);
    }

    public function test_parent_location_has_many_children_relationship(): void
    {
        $parent = Location::factory()->parent()->create(['name' => 'Main Building']);

        $child1 = Location::factory()->child($parent)->create(['name' => 'Room 101']);
        $child2 = Location::factory()->child($parent)->create(['name' => 'Room 102']);

        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->contains($child1));
        $this->assertTrue($parent->children->contains($child2));
    }

    public function test_child_location_belongs_to_parent_relationship(): void
    {
        $parent = Location::factory()->parent()->create(['name' => 'Main Warehouse']);
        $child = Location::factory()->child($parent)->create(['name' => 'Storage Bay A']);

        $this->assertInstanceOf(Location::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertEquals('Main Warehouse', $child->parent->name);
    }

    public function test_scope_active_filters_only_active_locations(): void
    {
        $activeLocation = Location::factory()->create(['is_active' => true]);
        $inactiveLocation = Location::factory()->inactive()->create();

        $activeResults = Location::active()->get();

        $this->assertTrue($activeResults->contains($activeLocation));
        $this->assertFalse($activeResults->contains($inactiveLocation));
    }

    public function test_scope_parents_filters_only_top_level_locations(): void
    {
        $parent = Location::factory()->parent()->create();
        $child = Location::factory()->child($parent)->create();

        $parents = Location::parents()->get();

        $this->assertTrue($parents->contains($parent));
        $this->assertFalse($parents->contains($child));
    }

    public function test_scope_children_filters_only_child_locations(): void
    {
        $parent = Location::factory()->parent()->create();
        $child = Location::factory()->child($parent)->create();

        $childrenOnly = Location::childrenOnly()->get();
        $subLocations = Location::subLocations()->get();

        $this->assertTrue($childrenOnly->contains($child));
        $this->assertFalse($childrenOnly->contains($parent));

        $this->assertTrue($subLocations->contains($child));
        $this->assertFalse($subLocations->contains($parent));
    }

    public function test_is_parent_and_is_child_helper_methods(): void
    {
        $parent = Location::factory()->parent()->create();
        $child = Location::factory()->child($parent)->create();

        $this->assertTrue($parent->isParent());
        $this->assertFalse($parent->isChild());

        $this->assertTrue($child->isChild());
        $this->assertFalse($child->isParent());
    }

    public function test_factory_states_work_correctly(): void
    {
        $building = Location::factory()->building()->create();
        $warehouse = Location::factory()->warehouse()->create();
        $room = Location::factory()->room()->create();
        $area = Location::factory()->area()->create();

        $this->assertEquals('building', $building->type);
        $this->assertEquals('warehouse', $warehouse->type);
        $this->assertEquals('room', $room->type);
        $this->assertEquals('area', $area->type);
    }
}
