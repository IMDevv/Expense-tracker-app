@component('mail::message')
# New Expense Alert

A new expense has been recorded:

**Amount:** KES {{ number_format($expense->amount, 2) }}  
**Category:** {{ $expense->category }}  
**Description:** {{ $expense->description }}  
**Date:** {{ $expense->date->format('M d, Y') }}

@component('mail::button', ['url' => route('expenses')])
View Expenses
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent 