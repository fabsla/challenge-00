<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionHistory>
 */
class TransactionHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'type' => 'DEPOSIT',
            'amount' => $this->faker->numberBetween(100, 10000),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * State para transações com tipo DEPOSIT
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'DEPOSIT',
        ]);
    }

    /**
     * State para transações com tipo TRANSFER
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'TRANSFER',
        ]);
    }

    /**
     * State para transações com tipo WITHDRAWAL
     */
    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'WITHDRAWAL',
        ]);
    }
}
