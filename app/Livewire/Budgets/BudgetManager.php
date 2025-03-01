<?php

namespace App\Livewire\Budgets;

use Livewire\Component;
use App\Models\Budget;
use Livewire\WithPagination;

class BudgetManager extends Component
{
    use WithPagination;

    public $budgets;
    public $currentBudget;

    public function mount()
    {
        $this->loadBudgets();
    }

    public function loadBudgets()
    {
        $user = auth()->user();
        
        $this->currentBudget = $user->budgets()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $this->budgets = $user->budgets()
            ->latest('start_date')
            ->get();
    }

    public function render()
    {
        return view('livewire.budgets.budget-manager');
    }
} 