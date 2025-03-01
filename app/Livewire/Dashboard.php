<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends AppComponent
{
    public $totalExpenses = 0;
    public $monthlyExpenses = 0;
    public $recentExpenses = [];
    public $budgets = [];
    public $currentBudget = 0;
    public $remainingBudget = 0;
    public $categoryTotals = [];
    public $monthlyTrend = [];

    public function mount()
    {
        try {
            $this->loadDashboardData();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to load dashboard data: ' . $e->getMessage());
        }
    }

    public function loadDashboardData()
    {
        $user = auth()->user();
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Get total expenses (all time)
        $this->totalExpenses = $user->expenses()->sum('amount');

        // Get this month's expenses (fix: use whereBetween with proper date range)
        $this->monthlyExpenses = $user->expenses()
            ->whereBetween('date', [
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            ])
            ->sum('amount');

        // Get recent expenses
        $this->recentExpenses = $user->expenses()
            ->latest('date')
            ->take(5)
            ->get();

        // Get active budgets and calculate totals
        $this->budgets = $user->budgets()
            ->where('period_start', '<=', $now)
            ->where('period_end', '>=', $now)
            ->get()
            ->map(function ($budget) use ($startOfMonth, $endOfMonth) {
                // Calculate spent amount for current month only
                $spent = $budget->expenses()
                    ->whereBetween('date', [
                        $startOfMonth->format('Y-m-d'),
                        $endOfMonth->format('Y-m-d')
                    ])
                    ->sum('amount');

                return [
                    'category' => $budget->category,
                    'amount' => $budget->amount,
                    'spent' => $spent,
                    'remaining' => $budget->amount - $spent,
                    'progress' => $budget->amount > 0 ? min(($spent / $budget->amount) * 100, 100) : 0
                ];
            });

        // Calculate total budget and remaining
        $this->currentBudget = $this->budgets->sum('amount');
        $this->remainingBudget = $this->currentBudget - $this->monthlyExpenses;

        // Category totals for pie chart (this month only)
        $this->categoryTotals = $user->expenses()
            ->whereBetween('date', [
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            ])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')  // Order by highest spending
            ->get();

        // Monthly trend data (last 6 months)
        $this->monthlyTrend = $user->expenses()
            ->where('date', '>=', now()->subMonths(6))
            ->select(
                DB::raw("DATE_TRUNC('month', date) as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month)->format('M Y'),
                    'total' => $item->total
                ];
            });
    }

    public function checkBudgetWarnings()
    {
        $nearlyExhaustedBudgets = auth()->user()->budgets()
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->get()
            ->filter(function ($budget) {
                return $budget->isNearlyExhausted();
            });

        foreach ($nearlyExhaustedBudgets as $budget) {
            $this->dispatch('notify', type: 'warning', message: "Warning: Your budget for {$budget->category} is nearly exhausted! Remaining: KES " . number_format($budget->remaining, 2));
        }

        $exhaustedBudgets = auth()->user()->budgets()
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->get()
            ->filter(function ($budget) {
                return $budget->isExhausted();
            });

        foreach ($exhaustedBudgets as $budget) {
            $this->dispatch('notify', type: 'error', message: "Budget for {$budget->category} is exhausted for this month.");
        }
    }

    public function refreshData()
    {
        try {
            $this->loadDashboardData();
            $this->dispatch('notify', type: 'success', message: 'Dashboard data refreshed successfully!');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to refresh dashboard data: ' . $e->getMessage());
        }
    }

    protected function view(): string
    {
        return 'livewire.dashboard';
    }
} 