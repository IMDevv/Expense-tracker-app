<?php

namespace App\Livewire\Budgets;

use Livewire\Component;
use App\Models\Budget;
use Carbon\Carbon;

class BudgetForm extends Component
{
    public $budget;
    public $amount = '';
    public $start_date;
    public $end_date;
    public $showModal = false;
    public $isEditing = false;

    protected $rules = [
        'amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date'
    ];

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    public function openModal(Budget $budget = null)
    {
        $this->resetValidation();
        $this->resetExcept(['start_date', 'end_date']);
        
        if ($budget->exists) {
            $this->budget = $budget;
            $this->amount = $budget->amount;
            $this->start_date = $budget->start_date->format('Y-m-d');
            $this->end_date = $budget->end_date->format('Y-m-d');
            $this->isEditing = true;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $budgetData = [
            'amount' => $this->amount,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];

        if ($this->isEditing) {
            $this->budget->update($budgetData);
            $this->dispatch('budget-updated');
        } else {
            auth()->user()->budgets()->create($budgetData);
            $this->dispatch('budget-created');
        }

        $this->showModal = false;
        $this->dispatch('refresh-budgets-list');
    }

    public function render()
    {
        return view('livewire.budgets.budget-form');
    }
} 