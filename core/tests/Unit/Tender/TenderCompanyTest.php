<?php

namespace Tests\Unit\Tender;

use App\TenderBlacklist;
use App\TenderCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers TenderCompany's purchase-count, blacklist delegation, and the
 * create/update/restore semantics of syncFromPurchase — the buyer-identity
 * record every tender purchase attempt (paid or not) keeps in sync.
 */
class TenderCompanyTest extends TestCase
{
    use RefreshDatabase, CreatesTenderFixtures;

    /** @test */
    public function purchased_tenders_count_counts_only_completed_purchases()
    {
        $tender = $this->makeTender();
        $this->makeTenderPurchase($tender, ['company_registration_no' => 'AB12', 'payment_status' => 'Completed']);
        $this->makeTenderPurchase($tender, ['company_registration_no' => 'AB12', 'payment_status' => 'Completed']);
        $this->makeTenderPurchase($tender, ['company_registration_no' => 'AB12', 'payment_status' => 'Pending']);

        $company = TenderCompany::create(['company_registration_no' => 'AB12']);

        $this->assertSame(2, $company->purchasedTendersCount());
    }

    /** @test */
    public function is_blacklisted_delegates_to_tender_blacklist()
    {
        TenderBlacklist::create(['company_registration_no' => 'AB12', 'reason' => 'Fraud']);
        $company = TenderCompany::create(['company_registration_no' => 'AB12']);
        $clean = TenderCompany::create(['company_registration_no' => 'CD34']);

        $this->assertTrue($company->isBlacklisted());
        $this->assertFalse($clean->isBlacklisted());
    }

    /** @test */
    public function sync_from_purchase_creates_a_new_company_record()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, [
            'company_registration_no' => 'ab-12',
            'company_name' => 'Acme Co',
            'country' => 'Bangladesh',
            'email' => 'buyer@example.com',
            'phone_number' => '+8801712345678',
        ]);

        TenderCompany::syncFromPurchase($purchase);

        $company = TenderCompany::where('company_registration_no', 'AB12')->first();
        $this->assertNotNull($company);
        $this->assertSame('Acme Co', $company->company_name);
        $this->assertNotNull($company->first_purchase_at);
        $this->assertNotNull($company->last_purchase_at);
    }

    /** @test */
    public function sync_from_purchase_updates_an_existing_company_without_resetting_first_purchase_at()
    {
        $tender = $this->makeTender();
        $original = $this->makeTenderPurchase($tender, ['company_registration_no' => 'AB12', 'company_name' => 'Old Name']);
        TenderCompany::syncFromPurchase($original);
        $firstSeen = TenderCompany::where('company_registration_no', 'AB12')->first()->first_purchase_at;

        $later = $this->makeTenderPurchase($tender, ['company_registration_no' => 'AB12', 'company_name' => 'New Name']);
        TenderCompany::syncFromPurchase($later);

        $company = TenderCompany::where('company_registration_no', 'AB12')->first();
        $this->assertSame('New Name', $company->company_name);
        $this->assertEquals($firstSeen, $company->first_purchase_at);
    }

    /** @test */
    public function sync_from_purchase_restores_a_soft_deleted_company()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, ['company_registration_no' => 'AB12']);
        TenderCompany::syncFromPurchase($purchase);
        TenderCompany::where('company_registration_no', 'AB12')->first()->delete();

        $this->assertTrue(TenderCompany::withTrashed()->where('company_registration_no', 'AB12')->first()->trashed());

        TenderCompany::syncFromPurchase($this->makeTenderPurchase($tender, ['company_registration_no' => 'AB12']));

        $company = TenderCompany::where('company_registration_no', 'AB12')->first();
        $this->assertNotNull($company);
        $this->assertFalse($company->trashed());
    }

    /** @test */
    public function sync_from_purchase_skips_when_registration_number_is_blank()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, ['company_registration_no' => null]);

        TenderCompany::syncFromPurchase($purchase);

        $this->assertSame(0, TenderCompany::count());
    }
}
