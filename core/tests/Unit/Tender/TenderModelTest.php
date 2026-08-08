<?php

namespace Tests\Unit\Tender;

use App\TenderModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Locks Tender::recalculatePrice(), the single source of truth for a tender's list price. */
class TenderModelTest extends TestCase
{
    use RefreshDatabase, CreatesTenderFixtures;

    /** @test */
    public function recalculate_price_sums_active_paid_module_costs()
    {
        $tender = $this->makeTender();
        TenderModule::create(['tender_id' => $tender->id, 'name' => 'Doc A', 'cost' => 100, 'summary' => 'x', 'status' => 1]);
        TenderModule::create(['tender_id' => $tender->id, 'name' => 'Doc B', 'cost' => 50, 'summary' => 'x', 'status' => 1]);

        $tender->recalculatePrice();

        $this->assertEquals(150, $tender->fresh()->current_price);
    }

    /** @test */
    public function recalculate_price_ignores_inactive_modules()
    {
        $tender = $this->makeTender();
        TenderModule::create(['tender_id' => $tender->id, 'name' => 'Active', 'cost' => 100, 'summary' => 'x', 'status' => 1]);
        TenderModule::create(['tender_id' => $tender->id, 'name' => 'Inactive', 'cost' => 999, 'summary' => 'x', 'status' => 0]);

        $tender->recalculatePrice();

        $this->assertEquals(100, $tender->fresh()->current_price);
    }

    /** @test */
    public function recalculate_price_is_null_when_only_free_modules_exist()
    {
        $tender = $this->makeTender(['current_price' => 999]);
        TenderModule::create(['tender_id' => $tender->id, 'name' => 'Free', 'cost' => null, 'summary' => 'x', 'status' => 1]);

        $tender->recalculatePrice();

        $this->assertNull($tender->fresh()->current_price);
    }

    /** @test */
    public function recalculate_price_is_null_with_no_modules_at_all()
    {
        $tender = $this->makeTender(['current_price' => 999]);

        $tender->recalculatePrice();

        $this->assertNull($tender->fresh()->current_price);
    }
}
