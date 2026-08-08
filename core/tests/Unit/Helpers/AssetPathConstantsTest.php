<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

/**
 * These constants are defined via composer's "files" autoload
 * (app/Http/Helpers/Helper.php), so they're available without booting
 * the framework — a plain PHPUnit TestCase is enough.
 */
class AssetPathConstantsTest extends TestCase
{
    public function test_public_relative_constants_are_defined_correctly(): void
    {
        $this->assertSame('assets/front/img/', FRONT_IMG_PATH);
        $this->assertSame('assets/admin/img/', FRONT_ADMIN_IMG_PATH);
        $this->assertSame('assets/front/invoices/', FRONT_INVOICES_PATH);
        $this->assertSame('assets/front/receipt/', FRONT_RECEIPT_PATH);
        $this->assertSame('assets/front/files/tender_modules/', FRONT_TENDER_FILES_PATH);
    }

    public function test_base_path_relative_constants_are_defined_correctly(): void
    {
        $this->assertSame('../assets/front/img/', FRONT_IMG_DIR);
        $this->assertSame('public/assets/front/img/', FRONT_IMG_PUBLIC_DIR);
        $this->assertSame('../assets/admin/img/', FRONT_ADMIN_IMG_DIR);
        $this->assertSame('../assets/front/invoices/tender/', FRONT_TENDER_INVOICE_DIR);
        $this->assertSame('../assets/front/files/tender_modules', FRONT_TENDER_FILES_DIR);
        $this->assertSame('../assets/lfm/files', FRONT_LFM_FILES_DIR);
    }

    public function test_base_path_relative_constants_start_with_parent_traversal(): void
    {
        // These are consumed via base_path(), so they must all resolve up
        // one level from core/ to the project root where assets/ lives.
        foreach ([
            FRONT_IMG_DIR,
            FRONT_ADMIN_IMG_DIR,
            FRONT_TENDER_INVOICE_DIR,
            FRONT_TENDER_FILES_DIR,
            FRONT_LFM_FILES_DIR,
        ] as $dir) {
            $this->assertStringStartsWith('../assets/', $dir);
        }
    }
}
