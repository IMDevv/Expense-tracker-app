<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use App\Http\Resources\BudgetResource;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = $request->user()
            ->budgets()
            ->with('expenses')
            ->latest()
            ->get();

        return BudgetResource::collection($budgets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|exists:categories,name,is_active,1',
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $budget = $request->user()->budgets()->create($validated);

        return new BudgetResource($budget);
    }

    public function show(Request $request, Budget $budget)
    {
        $this->authorize('view', $budget);
        return new BudgetResource($budget);
    }

    public function update(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $budget->update($validated);

        return new BudgetResource($budget);
    }

    public function destroy(Request $request, Budget $budget)
    {
        $this->authorize('delete', $budget);
        $budget->delete();
        return response()->json(['message' => 'Budget deleted successfully']);
    }

    public function current(Request $request)
    {
        $budget = $request->user()->currentBudget;
        return $budget ? new BudgetResource($budget) : response()->json(null);
    }
} 