<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'amount',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category', 'category')
            ->whereBetween('date', [$this->period_start, $this->period_end]);
    }

    public function getSpentAttribute(): float
    {
        return $this->expenses()
            ->whereBetween('date', [$this->period_start, $this->period_end])
            ->sum('amount') ?? 0;
    }

    public function getRemainingAttribute(): float
    {
        return $this->amount - $this->spent;
    }

    public function getProgressPercentageAttribute(): float
    {
        return $this->amount > 0 ? ($this->spent / $this->amount) * 100 : 0;
    }

    public function isExhausted(): bool
    {
        return $this->remaining <= 0;
    }

    public function isNearlyExhausted(): bool
    {
        return $this->remaining > 0 && ($this->spent / $this->amount) >= 0.9;
    }

    public function getRemainingPercentage(): float
    {
        return $this->amount > 0 ? (($this->amount - $this->spent) / $this->amount) * 100 : 0;
    }

    public function isWithinPeriod($date): bool
    {
        return Carbon::parse($date)->between(
            $this->period_start,
            $this->period_end,
            true // Include start and end dates
        );
    }

    public static function createRules($userId = null): array
    {
        $userId = $userId ?? auth()->id();
        
        return [
            'category' => [
                'required',
                'string',
                'exists:categories,name,is_active,1',
                Rule::unique('budgets')->where(function (Builder $query) use ($userId) {
                    return $query->where('user_id', $userId)
                        ->whereRaw('period_start <= ? AND period_end >= ?', [
                            now()->endOfMonth(),
                            now()->startOfMonth()
                        ]);
                }),
            ],
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($budget) {
            // Check for existing budget in the same month
            $existingBudget = static::where('user_id', $budget->user_id)
                ->where('category', $budget->category)
                ->where(function ($query) use ($budget) {
                    $query->whereRaw('period_start <= ? AND period_end >= ?', [
                        $budget->period_end,
                        $budget->period_start
                    ]);
                })
                ->when($budget->exists, function ($query) use ($budget) {
                    $query->where('id', '!=', $budget->id);
                })
                ->exists();

            if ($existingBudget) {
                throw new \Exception('A budget for this category already exists for the selected period.');
            }
        });
    }
} 