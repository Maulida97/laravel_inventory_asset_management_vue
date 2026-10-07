<?php

namespace Tests\Feature;

use App\Models\Location;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_ten_predefined_locations_with_correct_hierarchy(): void
    {
        $this->seed(LocationSeeder::class);

        $this->assertEquals(10, Location::count());
        $this->assertEquals(3, Location::parents()->count());
        $this->assertEquals(7, Location::childrenOnly()->count());
        $this->assertEquals(10, Location::active()->count());

        // Verify specific parent locations
        $headOffice = Location::where('code', 'LOC-HO')->first();
        $this->assertNotNull($headOffice);
        $this->assertEquals('building', $headOffice->type);
        $this->assertTrue($headOffice->isParent());
        $this->assertEquals(3, $headOffice->children()->count());

        $mainWarehouse = Location::where('code', 'LOC-WHS-JKT')->first();
        $this->assertNotNull($mainWarehouse);
        $this->assertEquals('warehouse', $mainWarehouse->type);
        $this->assertTrue($mainWarehouse->isParent());
        $this->assertEquals(3, $mainWarehouse->children()->count());

        $surabayaBranch = Location::where('code', 'LOC-BRC-SBY')->first();
        $this->assertNotNull($surabayaBranch);
        $this->assertEquals('building', $surabayaBranch->type);
        $this->assertTrue($surabayaBranch->isParent());
        $this->assertEquals(1, $surabayaBranch->children()->count());
    }

    public function test_it_is_idempotent_when_run_multiple_times(): void
    {
        $this->seed(LocationSeeder::class);
        $this->seed(LocationSeeder::class);

        $this->assertEquals(10, Location::count());
    }

    public function test_it_runs_via_main_database_seeder(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals(10, Location::count());
    }
}
