<?php

namespace App\Livewire\Budgets;

use App\Livewire\AppComponent;
use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;

class BudgetList extends AppComponent
{
    use WithPagination;

    #[Rule('required|string')]
    public $category = '';

    #[Rule('required|numeric|min:0')]
    public $amount = '';

    public $editingBudget = null;
    public $showDeleteModal = false;
    public $budgetToDelete = null;

    protected function rules()
    {
        return [
            'category' => [
                'required',
                'string',
                'exists:categories,name,is_active,1'
            ],
            'amount' => 'required|numeric|min:0'
        ];
    }

    public function createBudget()
    {
        try {
            $this->validate();

            // Check for existing budget in the same period
            $existingBudget = auth()->user()->budgets()
                ->where('category', $this->category)
                ->where('period_start', '<=', now()->endOfMonth())
                ->where('period_end', '>=', now()->startOfMonth())
                ->exists();

            if ($existingBudget) {
                $this->addError('category', 'A budget for this category already exists for the current month.');
                return;
            }

            auth()->user()->budgets()->create([
                'category' => $this->category,
                'amount' => $this->amount,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
            ]);

            $this->reset(['category', 'amount']);
            $this->dispatch('close-modal', 'create-budget');
           // $this->dispatch('notify', type: 'success', message: 'Budget created successfully!');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function editBudget(Budget $budget)
    {
        $this->editingBudget = $budget;
        $this->category = $budget->category;
        $this->amount = $budget->amount;
        $this->dispatch('open-modal', 'edit-budget');
    }

    public function updateBudget()
    {
        if (!$this->editingBudget) {
            return;
        }

        try {
            $this->validate();

            // Check for existing budget in the same period (excluding current budget)
            $existingBudget = auth()->user()->budgets()
                ->where('category', $this->category)
                ->where('period_start', '<=', $this->editingBudget->period_end)
                ->where('period_end', '>=', $this->editingBudget->period_start)
                ->where('id', '!=', $this->editingBudget->id)
                ->exists();

            if ($existingBudget) {
                $this->addError('category', 'A budget for this category already exists for the selected period.');
                return;
            }

            $this->editingBudget->update([
                'category' => $this->category,
                'amount' => $this->amount,
            ]);

            $this->reset(['category', 'amount', 'editingBudget']);
            $this->dispatch('close-modal', 'edit-budget');
           // $this->dispatch('notify', type: 'success', message: 'Budget updated successfully!');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function confirmDelete(Budget $budget)
    {
        $this->budgetToDelete = $budget;
        $this->dispatch('open-modal', 'confirm-delete');
    }

    public function deleteBudget()
    {
        if (!$this->budgetToDelete) {
            return;
        }

        try {
            $this->budgetToDelete->delete();
            
            $this->dispatch('close-modal', 'confirm-delete');
           // $this->dispatch('notify', type: 'error', message: 'Budget deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to delete budget: ' . $e->getMessage());
        }

        $this->reset(['budgetToDelete']);
    }

    public function cancelDelete()
    {
        $this->dispatch('close-modal', 'confirm-delete');
        $this->reset(['budgetToDelete']);
    }

    public function getCategories()
    {
        return Category::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }

    protected function view(): string
    {
        return 'livewire.budgets.budget-list';
    }

    public function render()
    {
        return view($this->view(), [
            'budgets' => auth()->user()->budgets()
                ->with('expenses')
                ->latest()
                ->paginate(10),
            'categories' => $this->getCategories()
        ])->layout($this->layout());
    }

    public function checkBudgetLimits(Budget $budget)
    {
        if ($budget->isNearlyExhausted()) {
            $this->dispatch('notify', type: 'warning', message: "Warning: Your budget for {$budget->category} is nearly exhausted! Remaining: KES " . number_format($budget->remaining, 2));
        }

        if ($budget->isExhausted()) {
            $this->dispatch('notify', type: 'error', message: "Budget for {$budget->category} is exhausted for this month.");
        }
    }
} 