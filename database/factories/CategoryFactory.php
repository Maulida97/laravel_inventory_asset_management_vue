<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'CAT-'.fake()->unique()->numerify('####'),
            'name' => fake()->randomElement([
                'Elektronik & Komputer',
                'Alat Tulis Kantor',
                'Peralatan Kebersihan',
                'Kendaraan Operasional',
                'Sparepart Mesin',
                'Furniture & Perabotan',
                'Perangkat Jaringan',
            ]),
            'type' => fake()->randomElement(Category::TYPES),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inventory(): static
    {
        return $this->state(fn () => [
            'type' => Category::TYPE_INVENTORY,
        ]);
    }

    public function asset(): static
    {
        return $this->state(fn () => [
            'type' => Category::TYPE_ASSET,
        ]);
    }

    public function both(): static
    {
        return $this->state(fn () => [
            'type' => Category::TYPE_BOTH,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
