<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ExpenseResource;

class ExpensesController extends Controller
{
    public function index()
    {
        $expenses = auth()->user()->expenses()
            ->latest()
            ->paginate(10);

        return ExpenseResource::collection($expenses);
    }

    public function store(Request $request)
    {
        // Get available categories from active budgets
        $availableCategories = auth()->user()->budgets()
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->get()
            ->filter(function ($budget) {
                return !$budget->isExhausted();
            })
            ->pluck('category')
            ->toArray();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'category' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($availableCategories) {
                    if (!in_array($value, $availableCategories)) {
                        $fail('You can only add expenses for categories with non-exhausted budgets.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $budget = auth()->user()->budgets()
                ->where('category', $request->category)
                ->where('period_start', '<=', now())
                ->where('period_end', '>=', now())
                ->first();

            if ($budget->isExhausted()) {
                return response()->json([
                    'message' => "Budget for {$budget->category} is exhausted for this month."
                ], 422);
            }

            $remainingBudget = $budget->amount - $budget->spent;
            if ($request->amount > $remainingBudget) {
                return response()->json([
                    'message' => "This expense would exceed your budget for {$budget->category}. Remaining budget: KES " . number_format($remainingBudget, 2)
                ], 422);
            }

            $expense = auth()->user()->expenses()->create($validator->validated());

            return new ExpenseResource($expense);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Expense $expense)
    {
        // Check if expense belongs to authenticated user
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        return new ExpenseResource($expense);
    }

    public function update(Request $request, Expense $expense)
    {
        // Check if expense belongs to authenticated user
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Get available categories from active budgets
        $availableCategories = auth()->user()->budgets()
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->pluck('category')
            ->toArray();

        // Custom validation for categories with active budgets
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'category' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($availableCategories) {
                    if (!in_array($value, $availableCategories)) {
                        $fail('You can only update expenses with categories that have active budgets.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $expense->update($validator->validated());

            return new ExpenseResource($expense);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Expense $expense)
    {
        // Check if expense belongs to authenticated user
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            $expense->delete();

            return response()->json([
                'message' => 'Expense deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete expense',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAvailableCategories()
    {
        $categories = auth()->user()->budgets()
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->pluck('category')
            ->toArray();

        return response()->json([
            'categories' => $categories
        ]);
    }
} 