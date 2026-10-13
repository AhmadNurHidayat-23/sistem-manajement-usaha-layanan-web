<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ulasan>
 */
class UlasanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'transaction_id' => Transaction::factory(),
            'service_id' => Service::factory(),
            'employee_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'komentar' => fake()->optional(0.7)->sentence(10),
        ];
    }
}
