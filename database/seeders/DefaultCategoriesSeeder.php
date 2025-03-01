<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DefaultCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Food & Dining',
                'description' => 'Restaurants, groceries, and food delivery',
            ],
            [
                'name' => 'Transportation',
                'description' => 'Public transport, fuel, and vehicle maintenance',
            ],
            [
                'name' => 'Bills & Utilities',
                'description' => 'Electricity, water, internet, and phone bills',
            ],
            [
                'name' => 'Shopping',
                'description' => 'Clothing, electronics, and general shopping',
            ],
            [
                'name' => 'Entertainment',
                'description' => 'Movies, games, and recreational activities',
            ],
            [
                'name' => 'Health',
                'description' => 'Medical expenses, medications, and health insurance',
            ],
            [
                'name' => 'Other',
                'description' => 'Miscellaneous expenses',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
} 