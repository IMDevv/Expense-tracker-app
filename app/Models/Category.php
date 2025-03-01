<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active'];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category', 'name');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'category', 'name');
    }
} 