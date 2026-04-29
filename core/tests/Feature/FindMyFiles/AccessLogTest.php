<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;

class AccessLogTest extends FindMyFilesTestCase
{
    /** @test */
    public function every_request_link_call_logs_link_requested()
    {
        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-NOTEXIST',
        ]);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_REQUESTED,
        ]);
    }

    /** @test */
    public function access_log_stores_ip_address()
    {
        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-NOTEXIST',
        ]);

        $log = AccessLog::where('event_type', AccessLog::LINK_REQUESTED)->first();
        $this->assertNotNull($log->ip);
    }

    /** @test */
    public function access_log_never_stores_email_in_plaintext()
    {
        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-NOTEXIST',
        ]);

        // Assert no access_log row has the plaintext email in the email_hash column
        $this->assertDatabaseMissing('access_logs', [
            'email_hash' => 'buyer@example.com',
        ]);

        // Assert the hash is stored instead
        $expectedHash = hash('sha256', 'buyer@example.com');
        $this->assertDatabaseHas('access_logs', [
            'email_hash' => $expectedHash,
        ]);
    }

    /** @test */
    public function access_log_is_immutable_has_no_updated_at()
    {
        $log = AccessLog::record(AccessLog::LINK_REQUESTED, [
            'ip'         => '1.2.3.4',
            'risk_score' => 0,
        ]);

        // AccessLog has $timestamps = false (no updated_at column tracked)
        $model = AccessLog::first();
        $this->assertFalse($model->usesTimestamps());
    }

    /** @test */
    public function successful_link_request_logs_link_sent_event()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_SENT,
            'order_id'   => 'ORD-001',
            'result'     => 'OK',
        ]);
    }

    /** @test */
    public function download_page_visit_with_valid_token_logs_link_clicked()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase);

        $this->get('/find-my-files/download?t=' . $raw);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_CLICKED,
            'order_id'   => 'ORD-001',
        ]);
    }

    /** @test */
    public function download_page_with_invalid_token_logs_download_failed()
    {
        $this->get('/find-my-files/download?t=bogus');

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::DOWNLOAD_FAILED,
            'result'     => 'TOKEN_NOT_FOUND',
        ]);
    }

    /** @test */
    public function rate_limit_hit_logs_rate_limit_triggered()
    {
        \App\RateLimitAttempt::create([
            'key'             => 'ip:127.0.0.1',
            'attempts'        => 5,
            'blocked_until'   => now()->addMinutes(30),
            'last_attempt_at' => now(),
        ]);

        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::RATE_LIMIT_TRIGGERED,
            'result'     => 'RATE_LIMITED',
        ]);
    }

    /** @test */
    public function access_log_risk_score_is_zero_for_fresh_ip()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $log = AccessLog::where('event_type', AccessLog::LINK_SENT)->first();
        $this->assertEquals(0.0, $log->risk_score);
    }

    /** @test */
    public function repeated_requests_increase_risk_score()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        // Make 3 failed requests first to raise the risk score
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/find-my-files/request-link', [
                'email'        => 'buyer@example.com',
                'order_number' => 'ORD-FAIL',
            ]);
        }

        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $log = AccessLog::where('event_type', AccessLog::LINK_SENT)->first();
        $this->assertGreaterThan(0, $log->risk_score);
    }
}
