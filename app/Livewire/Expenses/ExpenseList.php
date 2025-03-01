<?php

namespace App\Livewire\Expenses;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use App\Livewire\AppComponent;
use App\Models\Category;

class ExpenseList extends AppComponent
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $dateRange = '';
    public $sortField = 'date';
    public $sortDirection = 'desc';

    // Form fields
    public $amount = '';
    public $description = '';
    public $date = '';
    public $editingExpense = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'dateRange' => ['except' => ''],
        'sortField' => ['except' => 'date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function save()
    {
        $validated = $this->validate();

        try {
            // Check if budget is exhausted
            $budget = auth()->user()->budgets()
                ->where('category', $validated['category'])
                ->where('period_start', '<=', now())
                ->where('period_end', '>=', now())
                ->first();

            if ($budget->isExhausted()) {
                $this->dispatch('notify', type: 'error', message: "Budget for {$budget->category} is exhausted for this month.");
                return;
            }

            // Check if this expense would exceed the budget
            $remainingBudget = $budget->amount - $budget->spent;
            if ($validated['amount'] > $remainingBudget) {
                $this->dispatch('notify', type: 'error', message: "This expense would exceed your budget for {$budget->category}. Remaining: KES " . number_format($remainingBudget, 2));
                return;
            }

            $expense = auth()->user()->expenses()->create($validated);

            // Check if budget is nearly exhausted after this expense
            if ($budget->isNearlyExhausted()) {
                $this->dispatch('notify', type: 'warning', message: "Warning: Your budget for {$budget->category} is nearly exhausted! Remaining: KES " . number_format($budget->remaining, 2));
            }

            $this->reset(['amount', 'category', 'description', 'date']);
            $this->dispatch('close-modal', 'add-expense');
           // $this->dispatch('notify', type: 'success', message: 'Expense added successfully!');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function edit(Expense $expense)
    {
        $this->amount = $expense->amount;
        $this->category = $expense->category;
        $this->description = $expense->description;
        $this->date = $expense->date->format('Y-m-d');
        $this->editingExpense = $expense;

        $this->dispatch('open-modal', 'edit-expense');
    }

    public function update()
    {
        if (!$this->editingExpense) {
            return;
        }

        $validated = $this->validate();
        $this->editingExpense->update($validated);

        $this->reset(['amount', 'category', 'description', 'date', 'editingExpense']);
        $this->dispatch('close-modal', 'edit-expense');
       // $this->dispatch('notify', type: 'success', message: 'Expense updated successfully!');
    }

    public function delete(Expense $expense)
    {
        try {
            $expense->delete();
            
            $this->dispatch('notify', type: 'error', message: 'Expense deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getAvailableCategories()
    {
        $now = now();
        return auth()->user()->budgets()
            ->where('period_start', '<=', $now)
            ->where('period_end', '>=', $now)
            ->get()
            ->filter(function ($budget) {
                return !$budget->isExhausted();
            })
            ->pluck('category', 'category')
            ->toArray();
    }

    protected function rules()
    {
        $categories = implode(',', array_keys($this->getAvailableCategories()));
        
        return [
            'amount' => 'required|numeric|min:0',
            'category' => [
                'required',
                'string',
                "in:$categories",
            ],
            'description' => 'nullable|string',
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $budget = auth()->user()->budgets()
                        ->where('category', $this->category)
                        ->where('period_start', '<=', $value)
                        ->where('period_end', '>=', $value)
                        ->first();

                    if (!$budget) {
                        $fail("No budget exists for {$this->category} on this date.");
                    }
                },
            ],
        ];
    }

    protected function messages()
    {
        return [
            'category.in' => 'You can only add expenses for categories with active budgets.',
        ];
    }

    public function render()
    {
        return view($this->view(), [
            'expenses' => auth()->user()->expenses()
                ->when($this->search, function ($query) {
                    $query->where('description', 'like', '%' . $this->search . '%');
                })
                ->when($this->category, function ($query) {
                    $query->where('category', $this->category);
                })
                ->when($this->dateRange, function ($query) {
                    [$start, $end] = explode(' - ', $this->dateRange);
                    $query->whereBetween('date', [$start, $end]);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
            'categories' => $this->getAvailableCategories()
        ])->layout($this->layout());
    }

    protected function view(): string
    {
        return 'livewire.expenses.expense-list';
    }
} 