<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Budget;

class BudgetAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $budget;

    public function __construct(Budget $budget)
    {
        $this->budget = $budget;
    }

    public function build()
    {
        return $this->markdown('emails.budgets.alert')
                    ->subject('Budget Alert - ' . $this->budget->category);
    }
} 