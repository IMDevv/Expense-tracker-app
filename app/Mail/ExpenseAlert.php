<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Expense;

class ExpenseAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $expense;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }

    public function build()
    {
        return $this->markdown('emails.expenses.alert')
                    ->subject('New Expense Alert');
    }
} 