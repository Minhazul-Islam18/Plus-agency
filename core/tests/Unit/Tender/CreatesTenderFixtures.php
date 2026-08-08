<?php

namespace Tests\Unit\Tender;

use App\Language;
use App\Tender;
use App\TenderCategory;
use App\TenderPurchase;

/** Minimal valid fixtures for the Tender module's required (NOT NULL) columns. */
trait CreatesTenderFixtures
{
    private function makeLanguage(): Language
    {
        return Language::create(['name' => 'English', 'code' => 'en', 'is_default' => 1]);
    }

    private function makeTenderCategory(?Language $lang = null): TenderCategory
    {
        $lang = $lang ?? $this->makeLanguage();

        // TenderCategory declares no $fillable (production code sets its
        // properties directly too), so build it the same way here.
        $category = new TenderCategory();
        $category->language_id = $lang->id;
        $category->name = 'Construction';
        $category->serial_number = 1;
        $category->save();

        return $category;
    }

    private function makeTender(array $overrides = []): Tender
    {
        $lang = $this->makeLanguage();
        $category = $this->makeTenderCategory($lang);

        return Tender::create(array_merge([
            'language_id'         => $lang->id,
            'tender_category_id'  => $category->id,
            'country'             => 'Bangladesh',
            'title'               => 'Test Tender',
            'slug'                => 'test-tender-' . uniqid(),
            'overview'            => 'Overview text',
            'expert_name'         => 'Jane Doe',
            'expert_position'     => 'Manager',
            'expert_details'      => 'Details',
            'expert_whatsapp'     => '+8801700000000',
            'expert_email'        => 'expert@example.com',
        ], $overrides));
    }

    private function makeTenderPurchase(Tender $tender, array $overrides = []): TenderPurchase
    {
        // 'purchased_modules' isn't in TenderPurchase::$fillable (production
        // code sets it directly too, via createPendingPurchase()), so it has
        // to be assigned outside create() or it's silently dropped.
        $purchasedModules = $overrides['purchased_modules'] ?? null;
        unset($overrides['purchased_modules']);

        $purchase = TenderPurchase::create(array_merge([
            'tender_id'   => $tender->id,
            'order_number' => strtoupper(uniqid()),
            'first_name'  => 'John',
            'last_name'   => 'Buyer',
            'email'       => 'buyer@example.com',
        ], $overrides));

        if ($purchasedModules !== null) {
            $purchase->purchased_modules = $purchasedModules;
            $purchase->save();
        }

        return $purchase;
    }
}
