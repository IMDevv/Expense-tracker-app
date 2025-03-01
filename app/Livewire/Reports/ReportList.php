<?php

namespace App\Livewire\Reports;

use App\Livewire\AppComponent;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportList extends AppComponent
{
    public $dateRange = '';
    public $startDate;
    public $endDate;
    public $totalExpenses = 0;
    public $averageDaily = 0;
    public $categoryBreakdown = [];
    public $monthlyTrends = [];
    public $topExpenses = [];
    public $dailyAverage = 0;

    public function mount()
    {
        $this->startDate = now()->startOfMonth();
        $this->endDate = now()->endOfMonth();
        $this->loadReportData();
    }

    public function updatedDateRange($value)
    {
        if ($value) {
            [$start, $end] = explode(' - ', $value);
            $this->startDate = Carbon::parse($start);
            $this->endDate = Carbon::parse($end);
            $this->loadReportData();
        }
    }

    public function loadReportData()
    {
        $user = auth()->user();

        // Calculate total expenses for the period
        $this->totalExpenses = $user->expenses()
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');

        // Calculate daily average
        $this->dailyAverage = $this->calculateDailyAverage(
            $user->expenses(),
            $this->startDate,
            $this->endDate
        );

        // Get category breakdown
        $this->categoryBreakdown = $user->expenses()
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Get monthly trends
        $this->monthlyTrends = $user->expenses()
            ->where('date', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_TRUNC(\'month\', date) as month'),
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

        // Get top expenses
        $this->topExpenses = $user->expenses()
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderByDesc('amount')
            ->take(5)
            ->get();
    }

    private function calculateDailyAverage($expenses, $startDate, $endDate)
    {
        $totalAmount = $expenses->sum('amount');
        $numberOfDays = $startDate->diffInDays($endDate) + 1; // Add 1 to include both start and end dates
        
        return $numberOfDays > 0 ? $totalAmount / $numberOfDays : 0;
    }

    public function exportToCsv()
    {
        $user = auth()->user();
        $expenses = $user->expenses()
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="expenses.csv"',
        ];

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Category', 'Description', 'Amount']);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->date->format('Y-m-d'),
                    $expense->category,
                    $expense->description,
                    $expense->amount
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getBudgetsByMonth()
    {
        $user = auth()->user();
        
        // Get all budgets within the selected date range
        $budgets = $user->budgets()
            ->where(function ($query) {
                $query->where('period_start', '>=', $this->startDate)
                      ->orWhere('period_end', '<=', $this->endDate);
            })
            ->with(['expenses' => function ($query) {
                $query->whereBetween('date', [
                    $this->startDate,
                    $this->endDate
                ])->orderBy('date');
            }])
            ->get();

        // Group budgets by month
        $groupedBudgets = $budgets->groupBy(function ($budget) {
            return $budget->period_start->format('F Y');
        })->sortByDesc(function ($budgets, $month) {
            // Sort months in descending order
            return Carbon::parse("01 " . $month)->timestamp;
        });

        return $groupedBudgets;
    }

    // Add helper method to get chart data
    private function getCategoryChartData()
    {
        return [
            'labels' => $this->categoryBreakdown->pluck('category'),
            'data' => $this->categoryBreakdown->pluck('total')
        ];
    }

    // Add helper method to get trend data
    private function getTrendChartData()
    {
        return [
            'labels' => $this->monthlyTrends->pluck('month'),
            'data' => $this->monthlyTrends->pluck('total')
        ];
    }

    public function downloadCsv()
    {
        $fileName = 'expense_report_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['EXPENSE REPORT', '', '', '', '']);
            fputcsv($file, ['Generated on: ' . now()->format('Y-m-d H:i:s'), '', '', '', '']);
            fputcsv($file, ['Period: ' . $this->startDate->format('Y-m-d') . ' to ' . $this->endDate->format('Y-m-d'), '', '', '', '']);
            fputcsv($file, ['', '', '', '', '']); // Empty line for spacing

            // Summary Section
            fputcsv($file, ['SUMMARY', '', '', '', '']);
            fputcsv($file, ['Total Expenses:', 'KES ' . number_format($this->totalExpenses, 2), '', '', '']);
            fputcsv($file, ['Daily Average:', 'KES ' . number_format($this->dailyAverage, 2), '', '', '']);
            fputcsv($file, ['', '', '', '', '']); // Empty line

            // Get expenses grouped by month and category
            $groupedExpenses = auth()->user()->expenses()
                ->whereBetween('date', [$this->startDate, $this->endDate])
                ->get()
                ->groupBy(function($expense) {
                    return $expense->date->format('F Y');
                });

            foreach ($groupedExpenses as $month => $monthExpenses) {
                fputcsv($file, [$month, '', '', '', '']);
                fputcsv($file, ['Category', 'Budget Amount', 'Spent Amount', 'Remaining', '']);
                
                // Group expenses by category
                $categoryExpenses = $monthExpenses->groupBy('category');
                
                foreach ($categoryExpenses as $category => $expenses) {
                    // Get the budget for this category and month
                    $budget = auth()->user()->budgets()
                        ->where('category', $category)
                        ->where('period_start', '<=', $expenses->first()->date)
                        ->where('period_end', '>=', $expenses->first()->date)
                        ->first();

                    $totalSpent = $expenses->sum('amount');
                    $budgetAmount = $budget ? $budget->amount : 0;
                    $remaining = $budget ? $budget->amount - $totalSpent : 0;

                    fputcsv($file, [
                        $category,
                        'KES ' . number_format($budgetAmount, 2),
                        'KES ' . number_format($totalSpent, 2),
                        'KES ' . number_format($remaining, 2),
                        ''
                    ]);

                    // Add header for detailed expenses
                    fputcsv($file, ['Date', 'Description', 'Amount', '', '']);
                    
                    // List all expenses for this category
                    foreach ($expenses as $expense) {
                        fputcsv($file, [
                            $expense->date->format('Y-m-d'),
                            $expense->description,
                            'KES ' . number_format($expense->amount, 2),
                            '',
                            ''
                        ]);
                    }
                    
                    fputcsv($file, ['', '', '', '', '']); // Empty line between categories
                }
                
                fputcsv($file, ['', '', '', '', '']); // Empty line between months
            }

            fclose($file);
        };

        // After generating CSV, reinitialize the charts
        $this->dispatch('initCharts', [
            'categoryData' => $this->getCategoryChartData(),
            'trendData' => $this->getTrendChartData()
        ]);

        return response()->stream($callback, 200, $headers);
    }

    protected function view(): string
    {
        return 'livewire.reports.report-list';
    }
} 