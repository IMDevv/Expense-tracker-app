<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'email_verified_at',
        'avatar',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function getCurrentBudgetAttribute()
    {
        return $this->budgets()
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->first();
    }

    public function getRemainingBudgetAttribute()
    {
        $currentBudget = $this->current_budget;
        if (!$currentBudget) {
            return 0;
        }

        return $currentBudget->amount - $this->expenses()
            ->whereBetween('date', [
                $currentBudget->period_start,
                $currentBudget->period_end
            ])
            ->sum('amount');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
} 