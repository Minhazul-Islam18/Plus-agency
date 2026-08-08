<?php

namespace Tests\Unit\Tender;

use App\TenderBlacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Locks TenderBlacklist::matches() — the gate that blocks a new purchase at checkout. */
class TenderBlacklistTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function matches_true_for_a_blacklisted_registration_number()
    {
        TenderBlacklist::create(['company_registration_no' => 'AB12', 'reason' => 'Fraud']);

        $this->assertTrue(TenderBlacklist::matches('AB12'));
    }

    /** @test */
    public function matches_normalizes_before_comparing()
    {
        TenderBlacklist::create(['company_registration_no' => 'AB12', 'reason' => 'Fraud']);

        $this->assertTrue(TenderBlacklist::matches('ab-12 '));
        $this->assertTrue(TenderBlacklist::matches(' A B 1 2 '));
    }

    /** @test */
    public function matches_false_for_a_registration_number_not_on_the_list()
    {
        TenderBlacklist::create(['company_registration_no' => 'AB12', 'reason' => 'Fraud']);

        $this->assertFalse(TenderBlacklist::matches('ZZ99'));
    }

    /** @test */
    public function matches_false_for_an_empty_registration_number()
    {
        $this->assertFalse(TenderBlacklist::matches(''));
        $this->assertFalse(TenderBlacklist::matches(null));
    }
}
