<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Regular user routes
    Route::get('expenses/categories', [ExpenseController::class, 'getAvailableCategories']);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('budgets', BudgetController::class);
    
    // Get active categories (for dropdowns)
    Route::get('/categories', function () {
        return Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class);
    });
}); 