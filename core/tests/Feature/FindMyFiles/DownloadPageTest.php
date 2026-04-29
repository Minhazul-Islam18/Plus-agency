<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;
use App\SecureToken;

class DownloadPageTest extends FindMyFilesTestCase
{
    private function downloadUrl(string $raw): string
    {
        return '/find-my-files/download?t=' . $raw;
    }

    /** @test */
    public function valid_token_shows_download_page()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase);

        $this->get($this->downloadUrl($raw))->assertOk();
    }

    /** @test */
    public function download_page_increments_download_count_on_visit()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw, 'token' => $token] = $this->makeActiveToken($purchase);

        $this->assertEquals(0, $token->fresh()->download_count);

        $this->get($this->downloadUrl($raw));

        $this->assertEquals(1, $token->fresh()->download_count);
    }

    /** @test */
    public function download_page_marks_token_expired_when_max_downloads_reached()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw, 'token' => $token] = $this->makeActiveToken($purchase, [
            'download_count' => 2,
        ]);

        $this->get($this->downloadUrl($raw));

        $this->assertEquals('expired', $token->fresh()->status);
    }

    /** @test */
    public function download_page_logs_link_clicked_on_valid_token()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase);

        $this->get($this->downloadUrl($raw));

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_CLICKED,
            'order_id'   => 'ORD-001',
            'result'     => 'OK',
        ]);
    }

    /** @test */
    public function missing_token_parameter_shows_error_page()
    {
        $this->get('/find-my-files/download')
            ->assertOk()
            ->assertSee('Link Invalid or Expired');
    }

    /** @test */
    public function unknown_token_string_shows_error_page()
    {
        $this->get('/find-my-files/download?t=totally-fake-token')
            ->assertOk()
            ->assertSee('Link Invalid or Expired');
    }

    /** @test */
    public function expired_token_shows_error_page()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase, [
            'expires_at' => now()->subHour(),
        ]);

        $this->get($this->downloadUrl($raw))
            ->assertOk()
            ->assertSee('Link Invalid or Expired');
    }

    /** @test */
    public function revoked_token_shows_error_page()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase, ['status' => 'revoked']);

        $this->get($this->downloadUrl($raw))
            ->assertOk()
            ->assertSee('Link Invalid or Expired');
    }

    /** @test */
    public function exhausted_token_shows_error_page()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase, [
            'download_count' => 3,
            'max_downloads'  => 3,
        ]);

        $this->get($this->downloadUrl($raw))
            ->assertOk()
            ->assertSee('Link Invalid or Expired');
    }

    /** @test */
    public function invalid_token_logs_download_failed()
    {
        $this->get('/find-my-files/download?t=bad-token');

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::DOWNLOAD_FAILED,
            'result'     => 'TOKEN_NOT_FOUND',
        ]);
    }

    /** @test */
    public function download_page_lazy_expires_overdue_token()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw, 'token' => $token] = $this->makeActiveToken($purchase, [
            'expires_at' => now()->subHour(),
        ]);

        $this->get($this->downloadUrl($raw));

        $this->assertEquals('expired', $token->fresh()->status);
    }
}
