<?php

namespace Tests\Unit\Tender;

use App\TenderPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers TenderPurchase's phone/registration-number normalisation and the
 * duplicate-payment lookup — the identity logic the checkout, blacklist, and
 * SMS-OTP flows all depend on.
 */
class TenderPurchaseTest extends TestCase
{
    use RefreshDatabase, CreatesTenderFixtures;

    // -- normalizePhone --------------------------------------------------

    /** @test */
    public function normalize_phone_strips_non_digits_and_leading_zeros()
    {
        $this->assertSame('22676642050', TenderPurchase::normalizePhone('+226 76 64 20 50'));
        $this->assertSame('22676642050', TenderPurchase::normalizePhone('0022676642050'));
        $this->assertSame('76642050', TenderPurchase::normalizePhone('076642050'));
    }

    /** @test */
    public function normalize_phone_handles_null_and_empty()
    {
        $this->assertSame('', TenderPurchase::normalizePhone(null));
        $this->assertSame('', TenderPurchase::normalizePhone(''));
    }

    // -- e164Phone ---------------------------------------------------------

    /** @test */
    public function e164_phone_prepends_the_country_dial_code_when_absent()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, [
            'phone_number' => '1712345678',
            'country' => 'Bangladesh',
        ]);

        $this->assertSame('+8801712345678', $purchase->e164Phone());
    }

    /** @test */
    public function e164_phone_does_not_double_prepend_when_code_already_present()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, [
            'phone_number' => '8801712345678',
            'country' => 'Bangladesh',
        ]);

        $this->assertSame('+8801712345678', $purchase->e164Phone());
    }

    /** @test */
    public function e164_phone_falls_back_to_bare_digits_for_an_unrecognised_country()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, [
            'phone_number' => '12345678',
            'country' => 'Nowhereland',
        ]);

        $this->assertSame('+12345678', $purchase->e164Phone());
    }

    /** @test */
    public function e164_phone_is_empty_when_there_are_no_digits()
    {
        $tender = $this->makeTender();
        $purchase = $this->makeTenderPurchase($tender, ['phone_number' => null]);

        $this->assertSame('', $purchase->e164Phone());
    }

    // -- phoneMatches --------------------------------------------------------

    /** @test */
    public function phone_matches_identical_canonical_numbers()
    {
        $this->assertTrue(TenderPurchase::phoneMatches('1712345678', '1712345678'));
    }

    /** @test */
    public function phone_matches_a_national_suffix_of_the_full_number()
    {
        // 10+ significant digits vs. the same number missing its country code.
        $this->assertTrue(TenderPurchase::phoneMatches('2261712345678', '1712345678'));
    }

    /** @test */
    public function phone_matches_rejects_a_short_suffix_below_the_eight_digit_floor()
    {
        // '234567' (6 digits) is a genuine suffix of '1234567', but below the
        // 8-digit floor that guards against short-number collisions.
        $this->assertFalse(TenderPurchase::phoneMatches('1234567', '234567'));
    }

    /** @test */
    public function phone_matches_rejects_short_numbers_that_only_partially_overlap()
    {
        $this->assertFalse(TenderPurchase::phoneMatches('12345', '67890'));
    }

    /** @test */
    public function phone_matches_false_when_either_side_is_empty()
    {
        $this->assertFalse(TenderPurchase::phoneMatches('', '1712345678'));
        $this->assertFalse(TenderPurchase::phoneMatches('1712345678', null));
    }

    // -- isSuspended -----------------------------------------------------

    /** @test */
    public function is_suspended_reflects_access_status()
    {
        $tender = $this->makeTender();
        $active = $this->makeTenderPurchase($tender, ['access_status' => 'active']);
        $suspended = $this->makeTenderPurchase($tender, ['access_status' => 'suspended']);

        $this->assertFalse($active->isSuspended());
        $this->assertTrue($suspended->isSuspended());
    }

    // -- paidModuleNamesForReg --------------------------------------------

    /** @test */
    public function paid_module_names_for_reg_lists_names_from_completed_purchases_only()
    {
        $tender = $this->makeTender();

        $this->makeTenderPurchase($tender, [
            'company_registration_no' => 'AB12',
            'payment_status' => 'Completed',
            'purchased_modules' => json_encode([['name' => 'Doc A', 'cost' => 10], ['name' => 'Doc B', 'cost' => 0]]),
        ]);
        $this->makeTenderPurchase($tender, [
            'company_registration_no' => 'AB12',
            'payment_status' => 'Pending',
            'purchased_modules' => json_encode([['name' => 'Doc C', 'cost' => 20]]),
        ]);

        $names = TenderPurchase::paidModuleNamesForReg($tender->id, 'ab-12');

        $this->assertSame(['Doc A', 'Doc B'], $names);
    }

    /** @test */
    public function paid_module_names_for_reg_is_empty_for_a_blank_registration_number()
    {
        $tender = $this->makeTender();

        $this->assertSame([], TenderPurchase::paidModuleNamesForReg($tender->id, ''));
        $this->assertSame([], TenderPurchase::paidModuleNamesForReg($tender->id, null));
    }

    /** @test */
    public function paid_module_names_for_reg_ignores_other_companies()
    {
        $tender = $this->makeTender();
        $this->makeTenderPurchase($tender, [
            'company_registration_no' => 'OTHER1',
            'payment_status' => 'Completed',
            'purchased_modules' => json_encode([['name' => 'Doc X', 'cost' => 10]]),
        ]);

        $this->assertSame([], TenderPurchase::paidModuleNamesForReg($tender->id, 'AB12'));
    }
}
