<?php

namespace App\Livewire\Expenses;

use Livewire\Component;
use App\Models\Expense;

class ExpenseForm extends Component
{
    public $expense;
    public $amount = '';
    public $category = '';
    public $description = '';
    public $date;
    public $showModal = false;
    public $isEditing = false;

    // Predefined categories with option for custom
    public $predefinedCategories = [
        'Food & Dining',
        'Transportation',
        'Housing',
        'Utilities',
        'Healthcare',
        'Entertainment',
        'Shopping',
        'Education',
        'Other'
    ];

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'category' => 'required|string|max:50',
        'description' => 'nullable|string|max:255',
        'date' => 'required|date|before_or_equal:today'
    ];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function openModal(Expense $expense = null)
    {
        $this->resetValidation();
        $this->resetExcept('predefinedCategories');
        
        if ($expense->exists) {
            $this->expense = $expense;
            $this->amount = $expense->amount;
            $this->category = $expense->category;
            $this->description = $expense->description;
            $this->date = $expense->date->format('Y-m-d');
            $this->isEditing = true;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $expenseData = [
            'amount' => $this->amount,
            'category' => $this->category,
            'description' => $this->description,
            'date' => $this->date,
        ];

        if ($this->isEditing) {
            $this->expense->update($expenseData);
            $this->dispatch('expense-updated');
        } else {
            auth()->user()->expenses()->create($expenseData);
            $this->dispatch('expense-created');
        }

        $this->showModal = false;
        $this->dispatch('refresh-expenses-list');
    }

    public function render()
    {
        return view('livewire.expenses.expense-form');
    }
} 