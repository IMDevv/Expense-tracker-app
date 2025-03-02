<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        \Log::info('BudgetSeeder is running');

        $user = User::updateOrCreate(
            ['email' => 'ringier@example.com'],
            [
                'name' => 'ringier',
                'password' => bcrypt('ringier@123$'),
                'role' => 'admin',
                'email_verified_at' => Carbon::now(), 
            ]
        );

        \Log::info('User created/updated', ['user' => $user]);

        // Define budget categories and amounts
        $categories = [
            'Food' => 15000,
            'Transport' => 8000,
            'Bills' => 20000,
            'Shopping' => 10000,
            'Entertainment' => 5000,
        ];

        // Seed budgets for the created user
        foreach ($categories as $category => $amount) {
            Budget::create([
                'user_id' => $user->id,
                'category' => $category,
                'amount' => $amount,
                'period_start' => Carbon::now()->startOfMonth(),
                'period_end' => Carbon::now()->endOfMonth(),
            ]);
        }
    }
}
