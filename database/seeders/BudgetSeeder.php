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
        $user = User::first();
        
        $categories = [
            'Food' => 15000,
            'Transport' => 8000,
            'Bills' => 20000,
            'Shopping' => 10000,
            'Entertainment' => 5000,
        ];

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