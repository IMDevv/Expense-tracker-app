<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date|before_or_equal:today'
        ];
    }
} 