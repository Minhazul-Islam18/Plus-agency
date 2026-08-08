<?php

namespace Tests\Unit\Tender;

use App\Http\Controllers\Payment\Tender\TenderPaymentHelper;
use App\TenderPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks tenderPayableAmount() as the sole source of the charged amount: it
 * must always be derived from purchased_modules (server-side, already
 * filtered to the buyer's unpaid set), never trust a client-sent total.
 */
class TenderPayableAmountTest extends TestCase
{
    use RefreshDatabase, CreatesTenderFixtures;

    private function helper()
    {
        return new class {
            use TenderPaymentHelper;

            public function payable(TenderPurchase $purchase): float
            {
                return $this->tenderPayableAmount($purchase);
            }
        };
    }

    /** @test */
    public function payable_amount_sums_purchased_module_costs()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, [
            'purchased_modules' => json_encode([
                ['name' => 'Doc A', 'cost' => 100],
                ['name' => 'Doc B', 'cost' => 50.5],
            ]),
        ]);

        $this->assertSame(150.5, $this->helper()->payable($purchase));
    }

    /** @test */
    public function payable_amount_includes_free_modules_as_zero_cost()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, [
            'purchased_modules' => json_encode([
                ['name' => 'Paid', 'cost' => 75],
                ['name' => 'Free', 'cost' => 0],
            ]),
        ]);

        $this->assertSame(75.0, $this->helper()->payable($purchase));
    }

    /** @test */
    public function payable_amount_is_zero_when_purchased_modules_is_empty_or_missing()
    {
        $tender = $this->makeTender();

        $empty = $this->makeTenderPurchase($tender, ['purchased_modules' => json_encode([])]);
        $this->assertSame(0.0, $this->helper()->payable($empty));

        $missing = $this->makeTenderPurchase($tender, ['purchased_modules' => null]);
        $this->assertSame(0.0, $this->helper()->payable($missing));
    }
}
