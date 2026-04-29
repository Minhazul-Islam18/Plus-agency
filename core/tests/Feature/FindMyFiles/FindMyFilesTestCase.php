<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;
use App\Language;
use App\RateLimitAttempt;
use App\SecureToken;
use App\TenderModule;
use App\TenderPurchase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class FindMyFilesTestCase extends TestCase
{
    use DatabaseTransactions;

    protected Language $lang;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET SESSION sql_mode=""');
        $this->lang = $this->seedLanguage();
    }

    protected function seedLanguage(): Language
    {
        // Use existing default language if present (DatabaseTransactions keeps prod data)
        $existing = Language::where('is_default', 1)->first();
        if ($existing && $existing->basic_setting && $existing->basic_extended) {
            return $existing;
        }

        $lang = Language::create([
            'name'       => 'Test English',
            'is_default' => 1,
            'code'       => 'test-en',
            'rtl'        => 0,
            'status'     => 1,
        ]);

        DB::table('basic_settings')->insert([
            'language_id'                 => $lang->id,
            'is_recaptcha'                => 0,
            'google_recaptcha_site_key'   => '',
            'google_recaptcha_secret_key' => '',
        ]);

        DB::table('basic_settings_extended')->insert([
            'language_id'   => $lang->id,
            'theme_version' => 'default',
            'is_smtp'       => 0,
            'from_mail'     => 'noreply@test.local',
            'from_name'     => 'Test App',
        ]);

        DB::table('basic_settings_extra')->insert([
            'language_id' => $lang->id,
        ]);

        // Seed permalink for the main index route (loaded at boot — already happened,
        // but needed so route('find_my_files') resolves in URL generation)
        if (!DB::table('permalinks')->where('type', 'find_my_files')->exists()) {
            DB::table('permalinks')->insert([
                'permalink' => 'find-my-files',
                'type'      => 'find_my_files',
                'details'   => 0,
            ]);
        }

        return $lang;
    }

    protected function makePurchase(array $overrides = []): TenderPurchase
    {
        return TenderPurchase::create(array_merge([
            'tender_id'      => 1,
            'order_number'   => 'ORD-' . strtoupper(Str::random(6)),
            'first_name'     => 'John',
            'last_name'      => 'Doe',
            'email'          => 'buyer@example.com',
            'phone_number'   => '+8801234567890',
            'payment_status' => 'Completed',
            'payment_method' => 'offline',
            'gateway_type'   => 'offline',
            'qty'            => 1,
        ], $overrides));
    }

    /**
     * Returns ['raw' => string, 'token' => SecureToken]
     */
    protected function makeActiveToken(TenderPurchase $purchase, array $overrides = []): array
    {
        $raw       = hash_hmac('sha256', Str::random(32), config('app.key'));
        $tokenHash = hash('sha256', $raw);

        $token = SecureToken::create(array_merge([
            'order_id'       => $purchase->order_number,
            'email_hash'     => hash('sha256', strtolower($purchase->email)),
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(24),
            'max_downloads'  => 3,
            'download_count' => 0,
            'status'         => 'active',
            'ip'             => '127.0.0.1',
        ], $overrides));

        return ['raw' => $raw, 'token' => $token];
    }

    protected function makeTenderModule(int $tenderId, string $filename, array $overrides = []): TenderModule
    {
        $data = array_merge([
            'tender_id'   => $tenderId,
            'name'        => 'Test Module',
            'tender_file' => $filename,
            'status'      => 1,
        ], $overrides);

        $id = \DB::table('tender_modules')->insertGetId($data);
        return TenderModule::find($id);
    }

    protected function postRequestLink(array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('find_my_files.request_link'), array_merge([
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-000001',
        ], $data));
    }
}
