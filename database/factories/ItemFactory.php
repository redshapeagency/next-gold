<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $material = fake()->randomElement(['gold', 'argento', 'platino', 'altro']);
        $karat = $material === 'gold' ? fake()->randomElement([18, 24, 14]) : null;
        $purity = in_array($material, ['argento', 'platino']) ? fake()->randomFloat(2, 90, 99.99) : null;

        return [
            'code' => fake()->unique()->regexify('ITEM[0-9]{6}'),
            'name' => fake()->words(2, true),
            'category_id' => Category::factory(),
            'material' => $material,
            'karat' => $karat,
            'purity' => $purity,
            'weight_grams' => fake()->randomFloat(3, 0.1, 100),
            'price_purchase' => fake()->randomFloat(2, 10, 10000),
            'price_sale' => fake()->randomFloat(2, 20, 20000),
            'description' => fake()->optional()->paragraph(),
            'photo_path' => null,
            'status' => 'in_stock',
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
