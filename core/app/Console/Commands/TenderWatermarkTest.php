<?php

namespace App\Console\Commands;

use App\SecureToken;
use App\Tender;
use App\TenderModule;
use App\TenderPurchase;
use Illuminate\Console\Command;
use PDF;

/**
 * Seeds a disposable "Completed" tender purchase with a free PDF module and a
 * valid download token, so the full web download + watermark pipeline can be
 * exercised WITHOUT making a real payment.
 *
 *   php artisan tender:wm-test              # seed + print URL
 *   php artisan tender:wm-test --cleanup    # remove everything it created
 *
 * Marker: order_number starts with "WMTEST-" and module name "WM Test PDF".
 */
class TenderWatermarkTest extends Command
{
    protected $signature = 'tender:wm-test
                            {--cleanup : Remove all watermark-test data}
                            {--tender= : Tender id to attach the test module to (default: first tender)}
                            {--pdf= : Path to a source PDF to use (default: bundled/sample)}';

    protected $description = 'Seed a disposable purchase + token to test the PDF download watermark without paying.';

    private const MODULE_NAME = 'WM Test PDF';
    private const ORDER_PREFIX = 'WMTEST-';
    private const FILE_NAME = 'wm_test_sample.pdf';

    // A second module uploaded as a ZIP bundling several PDFs — exercises the
    // in-archive watermarking path (proposals shipped as one .zip).
    private const ZIP_MODULE_NAME = 'WM Test ZIP (bundle)';
    private const ZIP_FILE_NAME   = 'wm_test_bundle.zip';

    public function handle()
    {
        $modulesDir = env('FMF_MODULES_PATH', base_path('../assets/front/files/tender_modules'));

        if ($this->option('cleanup')) {
            return $this->cleanup($modulesDir);
        }

        $tender = $this->option('tender')
            ? Tender::find($this->option('tender'))
            : Tender::orderBy('id')->first();

        if (!$tender) {
            $this->error('No tender found. Create a tender first.');
            return 1;
        }

        // ── 1. Ensure a real PDF exists in the modules directory ──────────────
        if (!is_dir($modulesDir)) {
            mkdir($modulesDir, 0755, true);
        }
        $destPdf = rtrim($modulesDir, '/') . '/' . self::FILE_NAME;

        if (!is_file($destPdf)) {
            $src = $this->resolveSourcePdf($modulesDir);
            if (!$src) {
                $this->error('No source PDF found. Pass one with --pdf=/path/to/file.pdf');
                return 1;
            }
            copy($src, $destPdf);
            $this->line('Copied sample PDF -> ' . $destPdf);
        }

        // ── 1b. Ensure a ZIP module (several PDFs bundled) exists ──────────────
        $destZip = rtrim($modulesDir, '/') . '/' . self::ZIP_FILE_NAME;
        if (!is_file($destZip)) {
            if (!$this->buildSampleZip($destZip)) {
                $this->error('Could not build the sample ZIP bundle.');
                return 1;
            }
            $this->line('Built sample ZIP  -> ' . $destZip);
        }

        // ── 2. Free modules (cost = null → always downloadable) ───────────────
        $module = TenderModule::firstOrNew([
            'tender_id' => $tender->id,
            'name'      => self::MODULE_NAME,
        ]);
        $module->tender_file = self::FILE_NAME;
        $module->cost        = null;
        $module->status      = 1;
        $module->summary     = 'Disposable watermark-test module.';
        $module->save();

        $zipModule = TenderModule::firstOrNew([
            'tender_id' => $tender->id,
            'name'      => self::ZIP_MODULE_NAME,
        ]);
        $zipModule->tender_file = self::ZIP_FILE_NAME;
        $zipModule->cost        = null;
        $zipModule->status      = 1;
        $zipModule->summary     = 'Disposable watermark-test ZIP bundle.';
        $zipModule->save();

        // ── 3. Disposable Completed purchase ──────────────────────────────────
        $orderNumber = self::ORDER_PREFIX . strtoupper(substr(md5(uniqid()), 0, 8));

        $purchase = TenderPurchase::create([
            'tender_id'         => $tender->id,
            'user_id'           => null,
            'order_number'      => $orderNumber,
            'first_name'        => 'John',
            'last_name'         => 'Doe',
            'email'             => 'wm-test@example.com',
            'phone_number'      => '+10000000000',
            'country'           => 'Test',
            'city'              => 'Test City',
            'company_name'      => 'Initiatives Consulting Africa',
            'company_address'   => 'Test Address',
            'qty'               => 2,
            'purchased_modules' => json_encode([
                ['name' => self::MODULE_NAME,     'cost' => 0],
                ['name' => self::ZIP_MODULE_NAME, 'cost' => 0],
            ]),
            'currency_code'     => 'USD',
            'payment_method'    => 'Test',
            'gateway_type'      => 'offline',
            'payment_status'    => 'Completed',
            'access_status'     => 'active',
            'paid_at'           => now(),
        ]);

        // ── 4. Mint a valid download token ────────────────────────────────────
        $rawToken = bin2hex(random_bytes(20));
        SecureToken::create([
            'order_id'       => $orderNumber,
            'email_hash'     => hash('sha256', 'wm-test@example.com'),
            'token_hash'     => hash('sha256', $rawToken),
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(24),
            'max_downloads'  => 100,
            'download_count' => 0,
            'status'         => 'active',
            'device_hash'    => 'wm-test',
            'ip'             => '127.0.0.1',
        ]);

        $streamUrl  = route('find_my_files.stream', ['t' => $rawToken]);
        $confirmUrl = route('find_my_files.download', ['t' => $rawToken]);

        $this->info('Watermark test data seeded.');
        $this->line('  Tender:       #' . $tender->id . ' (' . $tender->tender_code . ')');
        $this->line('  Order:        ' . $orderNumber);
        $this->line('  Module (PDF): ' . self::MODULE_NAME . ' (free, ' . self::FILE_NAME . ')');
        $this->line('  Module (ZIP): ' . self::ZIP_MODULE_NAME . ' (free, ' . self::ZIP_FILE_NAME . ' — PDFs inside get stamped)');
        $this->newLine();
        $this->line('Open in a BROWSER (runs under php-fpm — real web path):');
        $this->line('  Direct ZIP:   ' . $streamUrl);
        $this->line('  Confirm page: ' . $confirmUrl);
        $this->newLine();
        $this->comment('When done:  php artisan tender:wm-test --cleanup');

        return 0;
    }

    /**
     * A PDF to stamp. Tried in order, so the test still runs on a production box
     * where only the SetaPDF library (no demo assets) was uploaded:
     *   1. --pdf=/path
     *   2. a real tender module PDF already on disk — the most realistic test
     *   3. a SetaPDF demo PDF, when the full distribution is present (dev)
     *   4. one generated on the fly with dompdf — always available
     */
    private function resolveSourcePdf(string $modulesDir): ?string
    {
        if ($this->option('pdf') && is_file($this->option('pdf'))) {
            return $this->option('pdf');
        }

        if ($found = $this->firstPdfIn($modulesDir)) {
            return $found;
        }

        // Content-rich sample, when the SetaPDF demos happen to be installed.
        $preferred = base_path('setapdf/demos/assets/pdfs/Brand-Guide-tagged.pdf');
        if (is_file($preferred)) {
            return $preferred;
        }
        if ($found = $this->firstPdfIn(base_path('setapdf'))) {
            return $found;
        }

        return $this->generateSamplePdf();
    }

    /** First PDF found anywhere under $dir, or null. */
    private function firstPdfIn(string $dir): ?string
    {
        if (!is_dir($dir)) {
            return null;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (strtolower($file->getExtension()) === 'pdf' && $file->getSize() > 0) {
                return $file->getPathname();
            }
        }
        return null;
    }

    /**
     * Last resort: render a sample PDF with dompdf, so the watermark test never
     * depends on any file being shipped alongside the library.
     */
    private function generateSamplePdf(): ?string
    {
        try {
            $path = storage_path('app/watermark-test-sample.pdf');

            $html = '<html><body style="font-family:DejaVu Sans;padding:40px;">'
                . '<h1>Watermark Test Document</h1>'
                . '<p>This sample was generated so the watermark can be checked over real text.</p>'
                . str_repeat('<p>Tender document body text used purely to give the stamp something to sit on top of.</p>', 12)
                . '</body></html>';

            PDF::loadHTML($html)->save($path);

            return is_file($path) ? $path : null;
        } catch (\Throwable $e) {
            $this->error('Could not generate a sample PDF: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build a ZIP bundling several PDFs (one in a sub-folder) plus a non-PDF
     * file, mirroring how a real technical/financial proposal is uploaded. The
     * download pipeline must stamp every PDF inside and pass README.txt through.
     */
    private function buildSampleZip(string $destZip): bool
    {
        $tmpA = storage_path('app/wm_zip_a_' . uniqid() . '.pdf');
        $tmpB = storage_path('app/wm_zip_b_' . uniqid() . '.pdf');

        try {
            $this->renderPdf('Technical Proposal', $tmpA);
            $this->renderPdf('Financial Proposal', $tmpB);

            $zip = new \ZipArchive();
            if ($zip->open($destZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return false;
            }
            // One PDF at the root, one in a sub-folder (checks structure is kept),
            // and a plain-text file that must survive unstamped.
            $zip->addFile($tmpA, 'technical_proposal.pdf');
            $zip->addFile($tmpB, 'proposals/financial_proposal.pdf');
            $zip->addFromString('README.txt', "Watermark-test bundle. The PDFs inside must be stamped; this file must not be.\n");
            $zip->close();

            return is_file($destZip);
        } catch (\Throwable $e) {
            $this->error('ZIP build failed: ' . $e->getMessage());
            return false;
        } finally {
            @unlink($tmpA);
            @unlink($tmpB);
        }
    }

    /** Render a one-off sample PDF with dompdf. */
    private function renderPdf(string $title, string $path): void
    {
        $html = '<html><body style="font-family:DejaVu Sans;padding:40px;">'
            . '<h1>' . htmlspecialchars($title) . '</h1>'
            . '<p>Sample PDF bundled inside a ZIP module to test in-archive watermarking.</p>'
            . str_repeat('<p>Body text so the watermark has something to sit over.</p>', 10)
            . '</body></html>';

        PDF::loadHTML($html)->save($path);
    }

    private function cleanup(string $modulesDir): int
    {
        $orders = TenderPurchase::where('order_number', 'like', self::ORDER_PREFIX . '%')->pluck('order_number');

        SecureToken::whereIn('order_id', $orders)->delete();
        $purchases = TenderPurchase::whereIn('order_number', $orders)->delete();
        $modules   = TenderModule::whereIn('name', [self::MODULE_NAME, self::ZIP_MODULE_NAME])->delete();

        $file = rtrim($modulesDir, '/') . '/' . self::FILE_NAME;
        $zip  = rtrim($modulesDir, '/') . '/' . self::ZIP_FILE_NAME;
        foreach ([$file, $zip] as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }

        $this->info('Cleanup done.');
        $this->line("  Purchases removed: {$purchases}");
        $this->line("  Modules removed:   {$modules}");
        $this->line('  Sample PDF removed: ' . $file);
        $this->line('  Sample ZIP removed: ' . $zip);

        return 0;
    }
}
