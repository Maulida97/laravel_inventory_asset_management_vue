<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'code' => 'LOC-'.fake()->unique()->numerify('####'),
            'name' => fake()->randomElement([
                'Gedung Utama',
                'Gedung Sayap Barat',
                'Gedung Sayap Timur',
                'Gudang Sentral',
                'Ruang Server',
                'Ruang Rapat Utama',
                'Area Parkir Logistik',
            ]),
            'type' => 'building',
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }

    /**
     * State for a parent location (building / warehouse).
     */
    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
            'type' => fake()->randomElement(['building', 'warehouse']),
        ]);
    }

    /**
     * State for a child location linked to an existing or newly created parent.
     */
    public function child(?Location $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent?->id ?? Location::factory()->parent(),
            'type' => fake()->randomElement(['room', 'area']),
        ]);
    }

    /**
     * State for building type location.
     */
    public function building(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'building',
        ]);
    }

    /**
     * State for warehouse type location.
     */
    public function warehouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'warehouse',
        ]);
    }

    /**
     * State for room type location.
     */
    public function room(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'room',
        ]);
    }

    /**
     * State for area type location.
     */
    public function area(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'area',
        ]);
    }

    /**
     * Indicate that the location is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
