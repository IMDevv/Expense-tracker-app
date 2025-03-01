<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Livewire\Dashboard;
use App\Livewire\Expenses\ExpenseList;
use App\Livewire\Budgets\BudgetList;
use App\Livewire\Reports\ReportList;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Expenses
    Route::get('/expenses', ExpenseList::class)->name('expenses');
    
    // Budgets
    Route::get('/budgets', BudgetList::class)->name('budgets');
    
    // Reports
    Route::get('/reports', ReportList::class)->name('reports');

    // Profile routes
    Route::get('/profile', \App\Livewire\Profile\UpdateProfile::class)->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/categories', App\Livewire\Admin\CategoryList::class)->name('admin.categories');
});

require __DIR__.'/auth.php'; 