<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['purchase', 'sale']);

        return [
            'type' => $type,
            'number' => '2024-' . strtoupper($type) . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'date' => fake()->date(),
            'client_id' => Client::factory(),
            'total_gross' => fake()->randomFloat(2, 100, 10000),
            'total_net' => fake()->randomFloat(2, 90, 9000),
            'notes' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['draft', 'confirmed', 'cancelled']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
