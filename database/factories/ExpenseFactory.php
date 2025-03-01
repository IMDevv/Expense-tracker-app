<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use App\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 month', 'now');
        
        return [
            'user_id' => User::factory(),
            'category' => $this->faker->word,
            'description' => $this->faker->sentence,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'date' => $date,
        ];
    }

    public function withBudget(Budget $budget)
    {
        return $this->state(function (array $attributes) use ($budget) {
            return [
                'user_id' => $budget->user_id,
                'category' => $budget->category,
                'date' => $budget->period_start->copy()->addDays(5), // Make sure it's within the budget period
            ];
        });
    }
} 