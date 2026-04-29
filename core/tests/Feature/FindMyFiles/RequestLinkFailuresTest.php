<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;
use App\RateLimitAttempt;

class RequestLinkFailuresTest extends FindMyFilesTestCase
{
    /** @test */
    public function order_not_found_returns_order_not_found_error()
    {
        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-DOESNOTEXIST',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'order_not_found']);
    }

    /** @test */
    public function order_not_found_increments_rate_limit_counters()
    {
        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-DOESNOTEXIST',
        ]);

        $this->assertDatabaseHas('rate_limit_attempts', [
            'key' => 'ip:127.0.0.1',
        ]);
    }

    /** @test */
    public function order_not_found_logs_correct_result()
    {
        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-DOESNOTEXIST',
        ]);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_REQUESTED,
            'result'     => 'ORDER_NOT_FOUND',
        ]);
    }

    /** @test */
    public function email_mismatch_returns_email_mismatch_error()
    {
        $purchase = $this->makePurchase(['email' => 'real@example.com', 'order_number' => 'ORD-001']);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'wrong@example.com',
            'order_number' => 'ORD-001',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'email_mismatch']);
    }

    /** @test */
    public function email_mismatch_is_case_insensitive()
    {
        $this->makePurchase(['email' => 'Buyer@Example.COM', 'order_number' => 'ORD-001']);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        // Case-normalised match — should NOT return email_mismatch
        $response->assertOk()
            ->assertJsonMissing(['type' => 'email_mismatch']);
    }

    /** @test */
    public function email_mismatch_increments_rate_limit_counters()
    {
        $this->makePurchase(['email' => 'real@example.com', 'order_number' => 'ORD-001']);

        $this->postJson('/find-my-files/request-link', [
            'email'        => 'wrong@example.com',
            'order_number' => 'ORD-001',
        ]);

        $this->assertDatabaseHas('rate_limit_attempts', [
            'key' => 'ip:127.0.0.1',
        ]);
    }

    /** @test */
    public function payment_not_completed_returns_invalid_status_error()
    {
        $this->makePurchase([
            'email'          => 'buyer@example.com',
            'order_number'   => 'ORD-001',
            'payment_status' => 'Pending',
        ]);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'invalid_status']);
    }

    /** @test */
    public function payment_not_completed_increments_rate_limit_counters()
    {
        $this->makePurchase([
            'email'          => 'buyer@example.com',
            'order_number'   => 'ORD-001',
            'payment_status' => 'Pending',
        ]);

        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $this->assertDatabaseHas('rate_limit_attempts', [
            'key' => 'ip:127.0.0.1',
        ]);
    }

    /** @test */
    public function payment_not_completed_logs_invalid_status()
    {
        $this->makePurchase([
            'email'          => 'buyer@example.com',
            'order_number'   => 'ORD-001',
            'payment_status' => 'Refunded',
        ]);

        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_REQUESTED,
            'result'     => 'INVALID_STATUS',
        ]);
    }
}
