<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseReport extends Component
{
    public $period = 'month';
    public $chartData;
    public $categoryData;
    public $dateRange;
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadChartData();
    }

    public function loadChartData()
    {
        $query = auth()->user()->expenses()
            ->whereBetween('date', [$this->startDate, $this->endDate]);

        // Category breakdown for pie chart
        $this->categoryData = $query->clone()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->map(fn($item) => [
                'label' => $item->category,
                'value' => $item->total
            ]);

        // Time series data for line chart
        $interval = match($this->period) {
            'week' => 'day',
            'month' => 'day',
            'year' => 'month',
            default => 'day'
        };

        $this->chartData = $query->clone()
            ->select(
                DB::raw("DATE_TRUNC('$interval', date) as date"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date->format('Y-m-d'),
                'total' => $item->total
            ]);
    }

    public function updatedPeriod($value)
    {
        $this->startDate = match($value) {
            'week' => now()->startOfWeek()->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            'year' => now()->startOfYear()->format('Y-m-d'),
            default => now()->startOfMonth()->format('Y-m-d')
        };

        $this->endDate = match($value) {
            'week' => now()->endOfWeek()->format('Y-m-d'),
            'month' => now()->endOfMonth()->format('Y-m-d'),
            'year' => now()->endOfYear()->format('Y-m-d'),
            default => now()->endOfMonth()->format('Y-m-d')
        };

        $this->loadChartData();
    }

    public function render()
    {
        return view('livewire.reports.expense-report');
    }
} 