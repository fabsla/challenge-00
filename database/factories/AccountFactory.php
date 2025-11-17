<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
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
            'agency_number' => str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT),
            'account_number' => str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
            'balance' => $this->faker->numberBetween(0, 100000),
        ];
    }
}
