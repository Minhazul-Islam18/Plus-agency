<?php

namespace Tests\Unit\FindMyFiles;

use App\RateLimitAttempt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RateLimitAttemptTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRecord(array $overrides = []): RateLimitAttempt
    {
        return RateLimitAttempt::create(array_merge([
            'key'             => 'ip:1.2.3.4',
            'attempts'        => 1,
            'blocked_until'   => null,
            'last_attempt_at' => now(),
        ], $overrides));
    }

    /** @test */
    public function is_blocked_returns_false_when_blocked_until_is_null()
    {
        $record = $this->makeRecord(['blocked_until' => null]);
        $this->assertFalse($record->isBlocked());
    }

    /** @test */
    public function is_blocked_returns_true_when_blocked_until_is_future()
    {
        $record = $this->makeRecord(['blocked_until' => now()->addMinutes(30)]);
        $this->assertTrue($record->isBlocked());
    }

    /** @test */
    public function is_blocked_returns_false_when_blocked_until_is_past()
    {
        $record = $this->makeRecord(['blocked_until' => now()->subMinute()]);
        $this->assertFalse($record->isBlocked());
    }

    /** @test */
    public function minutes_until_unblock_returns_zero_when_not_blocked()
    {
        $record = $this->makeRecord(['blocked_until' => null]);
        $this->assertEquals(0, $record->minutesUntilUnblock());
    }

    /** @test */
    public function minutes_until_unblock_returns_zero_when_block_expired()
    {
        $record = $this->makeRecord(['blocked_until' => now()->subMinute()]);
        $this->assertEquals(0, $record->minutesUntilUnblock());
    }

    /** @test */
    public function minutes_until_unblock_returns_positive_value_when_blocked()
    {
        $record = $this->makeRecord(['blocked_until' => now()->addMinutes(30)]);
        $minutes = $record->minutesUntilUnblock();

        $this->assertGreaterThan(0, $minutes);
        $this->assertLessThanOrEqual(30, $minutes);
    }

    /** @test */
    public function minutes_until_unblock_is_rounded_up()
    {
        // 29 minutes and 30 seconds remaining → should ceil to 30
        $record = $this->makeRecord(['blocked_until' => now()->addSeconds(1770)]);
        $this->assertEquals(30, $record->minutesUntilUnblock());
    }

    /** @test */
    public function blocked_until_is_cast_to_datetime()
    {
        $record = $this->makeRecord(['blocked_until' => now()->addMinutes(30)]);
        $this->assertInstanceOf(\Carbon\Carbon::class, $record->blocked_until);
    }
}
