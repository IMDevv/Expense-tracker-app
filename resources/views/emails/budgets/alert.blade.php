@component('mail::message')
# Budget Alert

Your budget for **{{ $budget->category }}** is nearly exhausted:

**Budget Amount:** KES {{ number_format($budget->amount, 2) }}  
**Amount Spent:** KES {{ number_format($budget->spent, 2) }}  
**Remaining:** KES {{ number_format($budget->remaining, 2) }}  
**Period:** {{ $budget->period_start->format('M d') }} - {{ $budget->period_end->format('M d, Y') }}

@component('mail::button', ['url' => route('budgets')])
View Budgets
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent 