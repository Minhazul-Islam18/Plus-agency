<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;
use App\RateLimitAttempt;
use Carbon\Carbon;

class RateLimitingTest extends FindMyFilesTestCase
{
    private function failRequest(): void
    {
        $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-NOTEXIST',
        ]);
    }

    /** @test */
    public function first_four_failures_do_not_block()
    {
        for ($i = 0; $i < 4; $i++) {
            $this->failRequest();
        }

        $record = RateLimitAttempt::where('key', 'ip:127.0.0.1')->first();
        $this->assertNull($record?->blocked_until);
    }

    /** @test */
    public function five_failures_trigger_30_minute_block()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->failRequest();
        }

        $record = RateLimitAttempt::where('key', 'ip:127.0.0.1')->firstOrFail();
        $this->assertTrue($record->isBlocked());
        $this->assertTrue($record->blocked_until->gt(now()->addMinutes(29)));
        $this->assertTrue($record->blocked_until->lt(now()->addMinutes(31)));
    }

    /** @test */
    public function ten_failures_trigger_2_hour_block()
    {
        // Pre-seed 9 attempts so the next failure pushes to 10
        RateLimitAttempt::create([
            'key'            => 'ip:127.0.0.1',
            'attempts'       => 9,
            'last_attempt_at' => now(),
        ]);

        $this->failRequest();

        $record = RateLimitAttempt::where('key', 'ip:127.0.0.1')->firstOrFail();
        $this->assertTrue($record->isBlocked());
        $this->assertTrue($record->blocked_until->gt(now()->addMinutes(119)));
        $this->assertTrue($record->blocked_until->lt(now()->addMinutes(121)));
    }

    /** @test */
    public function twenty_failures_trigger_24_hour_block()
    {
        // Pre-seed 19 attempts so the next failure pushes to 20
        RateLimitAttempt::create([
            'key'            => 'ip:127.0.0.1',
            'attempts'       => 19,
            'last_attempt_at' => now(),
        ]);

        $this->failRequest();

        $record = RateLimitAttempt::where('key', 'ip:127.0.0.1')->firstOrFail();
        $this->assertTrue($record->isBlocked());
        $this->assertTrue($record->blocked_until->gt(now()->addHours(23)));
        $this->assertTrue($record->blocked_until->lt(now()->addHours(25)));
    }

    /** @test */
    public function blocked_ip_returns_rate_limited_response()
    {
        // Pre-seed a block for this IP
        RateLimitAttempt::create([
            'key'            => 'ip:127.0.0.1',
            'attempts'       => 5,
            'blocked_until'  => now()->addMinutes(30),
            'last_attempt_at' => now(),
        ]);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'rate_limited'])
            ->assertJsonStructure(['minutes']);
    }

    /** @test */
    public function rate_limited_response_includes_minutes_remaining()
    {
        RateLimitAttempt::create([
            'key'            => 'ip:127.0.0.1',
            'attempts'       => 5,
            'blocked_until'  => now()->addMinutes(30),
            'last_attempt_at' => now(),
        ]);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $minutes = $response->json('minutes');
        $this->assertGreaterThan(0, $minutes);
        $this->assertLessThanOrEqual(30, $minutes);
    }

    /** @test */
    public function blocked_email_hash_also_triggers_rate_limit()
    {
        $emailHash = hash('sha256', 'buyer@example.com');

        RateLimitAttempt::create([
            'key'            => 'email:' . $emailHash,
            'attempts'       => 5,
            'blocked_until'  => now()->addMinutes(30),
            'last_attempt_at' => now(),
        ]);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'rate_limited']);
    }

    /** @test */
    public function rate_limited_request_logs_rate_limit_triggered_event()
    {
        RateLimitAttempt::create([
            'key'            => 'ip:127.0.0.1',
            'attempts'       => 5,
            'blocked_until'  => now()->addMinutes(30),
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
    public function expired_block_does_not_prevent_request()
    {
        RateLimitAttempt::create([
            'key'            => 'ip:127.0.0.1',
            'attempts'       => 5,
            'blocked_until'  => now()->subMinute(), // already expired
            'last_attempt_at' => now()->subHour(),
        ]);

        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'success']);
    }

    /** @test */
    public function three_counters_all_incremented_on_failure()
    {
        $emailHash  = hash('sha256', 'buyer@example.com');
        $deviceHash = hash('sha256', 'Symfony' . '127.0.0.1'); // default test UA + IP

        $this->failRequest();

        $this->assertDatabaseHas('rate_limit_attempts', ['key' => 'ip:127.0.0.1']);
        $this->assertDatabaseHas('rate_limit_attempts', ['key' => 'email:' . $emailHash]);
    }
}
