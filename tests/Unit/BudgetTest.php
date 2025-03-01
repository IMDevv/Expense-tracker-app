<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Budget;
use App\Models\User;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;

class BudgetTest extends TestCase
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
    public function it_can_calculate_spent_amount()
    {
        // Create some expenses
        Expense::factory()
            ->withBudget($this->budget)
            ->create([
                'amount' => 300,
            ]);

        Expense::factory()
            ->withBudget($this->budget)
            ->create([
                'amount' => 200,
            ]);

        $this->assertEquals(500, $this->budget->fresh()->spent);
    }

    #[Test]
    public function it_can_calculate_remaining_amount()
    {
        Expense::factory()
            ->withBudget($this->budget)
            ->create([
                'amount' => 300,
            ]);

        $this->assertEquals(700, $this->budget->fresh()->remaining);
    }

    /** @test */
    public function it_can_determine_if_budget_is_exhausted()
    {
        $this->assertFalse($this->budget->isExhausted());

        Expense::factory()
            ->withBudget($this->budget)
            ->create([
                'amount' => 1000,
            ]);

        $this->assertTrue($this->budget->fresh()->isExhausted());
    }

    /** @test */
    public function it_can_determine_if_budget_is_nearly_exhausted()
    {
        $this->assertFalse($this->budget->isNearlyExhausted());

        Expense::factory()
            ->withBudget($this->budget)
            ->create([
                'amount' => 910, // 91% of budget
            ]);

        $this->assertTrue($this->budget->fresh()->isNearlyExhausted());
    }

    /** @test */
    public function it_validates_date_is_within_period()
    {
        $date = $this->budget->period_start->copy()->addDays(5);
        $this->assertTrue($this->budget->isWithinPeriod($date));
        $this->assertFalse($this->budget->isWithinPeriod($this->budget->period_start->copy()->subMonths(1)));
        $this->assertFalse($this->budget->isWithinPeriod($this->budget->period_end->copy()->addMonths(1)));
    }

    /** @test */
    public function it_prevents_duplicate_budgets_in_same_period()
    {
        $this->expectException(\Exception::class);

        Budget::create([
            'user_id' => $this->user->id,
            'category' => 'Food',
            'amount' => 500,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);
    }
} 