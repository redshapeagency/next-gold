<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->date(),
            'birth_place' => fake()->city(),
            'tax_code' => fake()->regexify('[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]'),
            'id_doc_type' => fake()->randomElement(['Carta d\'Identità', 'Passaporto', 'Patente']),
            'id_doc_number' => fake()->regexify('[A-Z]{2}[0-9]{7}'),
            'id_doc_issuer' => fake()->city(),
            'id_doc_issue_date' => fake()->date(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'zip' => fake()->postcode(),
            'province' => fake()->stateAbbr(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'notes' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
