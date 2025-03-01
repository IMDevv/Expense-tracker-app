<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Expense;
use App\Models\User;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;

class ExpenseTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $budget;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'Food',
            'amount' => 1000,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);
    }

    #[Test]
    public function it_belongs_to_a_user()
    {
        $expense = Expense::factory()
            ->withBudget($this->budget)
            ->create();

        $this->assertInstanceOf(User::class, $expense->user);
        $this->assertEquals($this->user->id, $expense->user->id);
    }

    #[Test]
    public function it_can_be_associated_with_a_budget()
    {
        $expense = Expense::factory()
            ->withBudget($this->budget)
            ->create();

        $this->assertTrue($expense->budget->is($this->budget));
    }

    #[Test]
    public function it_validates_expense_date_within_budget_period()
    {
        $this->expectException(\Exception::class);

        Expense::factory()
            ->withBudget($this->budget)
            ->create(['date' => now()->subMonths(1)]);
    }
} 