<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpenseAlert;
use App\Mail\BudgetAlert;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'amount',
        'description',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'name');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'category', 'category')
            ->where('period_start', '<=', $this->date)
            ->where('period_end', '>=', $this->date);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($expense) {
            // Send email to user
            if ($expense->user->email) {
                Mail::to($expense->user->email)->send(new ExpenseAlert($expense));
            }

            // Check budget and send alert if nearly exhausted
            $budget = $expense->budget;
            if ($budget && $budget->isNearlyExhausted()) {
                Mail::to($expense->user->email)->send(new BudgetAlert($budget));
            }
        });

        static::saving(function ($expense) {
            $budget = $expense->budget;
            if (!$budget) {
                throw new \Exception('No budget exists for this category in the selected period.');
            }
        });
    }
} 