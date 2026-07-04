<?php

namespace App\Console\Commands;

use App\SecureToken;
use App\Tender;
use App\TenderModule;
use App\TenderPurchase;
use Illuminate\Console\Command;

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
            $src = $this->resolveSourcePdf();
            if (!$src) {
                $this->error('No source PDF found. Pass one with --pdf=/path/to/file.pdf');
                return 1;
            }
            copy($src, $destPdf);
            $this->line('Copied sample PDF -> ' . $destPdf);
        }

        // ── 2. Free module (cost = null → always downloadable) ────────────────
        $module = TenderModule::firstOrNew([
            'tender_id' => $tender->id,
            'name'      => self::MODULE_NAME,
        ]);
        $module->tender_file = self::FILE_NAME;
        $module->cost        = null;
        $module->status      = 1;
        $module->summary     = 'Disposable watermark-test module.';
        $module->save();

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
            'qty'               => 1,
            'purchased_modules' => json_encode([['name' => self::MODULE_NAME, 'cost' => 0]]),
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
        $this->line('  Module:       ' . self::MODULE_NAME . ' (free, ' . self::FILE_NAME . ')');
        $this->newLine();
        $this->line('Open in a BROWSER (runs under php-fpm — real web path):');
        $this->line('  Direct ZIP:   ' . $streamUrl);
        $this->line('  Confirm page: ' . $confirmUrl);
        $this->newLine();
        $this->comment('When done:  php artisan tender:wm-test --cleanup');

        return 0;
    }

    private function resolveSourcePdf(): ?string
    {
        if ($this->option('pdf') && is_file($this->option('pdf'))) {
            return $this->option('pdf');
        }
        // Prefer a content-rich sample so the watermark is seen over real text.
        $preferred = base_path('setapdf/demos/assets/pdfs/Brand-Guide-tagged.pdf');
        if (is_file($preferred)) {
            return $preferred;
        }
        // Otherwise any PDF shipped with the SetaPDF demos works as a sample.
        $root = base_path('setapdf');
        if (is_dir($root)) {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if (strtolower($file->getExtension()) === 'pdf') {
                    return $file->getPathname();
                }
            }
        }
        return null;
    }

    private function cleanup(string $modulesDir): int
    {
        $orders = TenderPurchase::where('order_number', 'like', self::ORDER_PREFIX . '%')->pluck('order_number');

        SecureToken::whereIn('order_id', $orders)->delete();
        $purchases = TenderPurchase::whereIn('order_number', $orders)->delete();
        $modules   = TenderModule::where('name', self::MODULE_NAME)->delete();

        $file = rtrim($modulesDir, '/') . '/' . self::FILE_NAME;
        if (is_file($file)) {
            @unlink($file);
        }

        $this->info('Cleanup done.');
        $this->line("  Purchases removed: {$purchases}");
        $this->line("  Modules removed:   {$modules}");
        $this->line('  Sample PDF removed: ' . ($file));

        return 0;
    }
}
