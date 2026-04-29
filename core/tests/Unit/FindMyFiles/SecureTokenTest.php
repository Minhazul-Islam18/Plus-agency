<?php

namespace Tests\Unit\FindMyFiles;

use App\SecureToken;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecureTokenTest extends TestCase
{
    use DatabaseTransactions;

    private function makeToken(array $overrides = []): SecureToken
    {
        return SecureToken::create(array_merge([
            'order_id'       => 'ORD-UNIT-001',
            'email_hash'     => hash('sha256', 'test@example.com'),
            'token_hash'     => hash('sha256', Str::random(32)),
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(24),
            'max_downloads'  => 3,
            'download_count' => 0,
            'status'         => 'active',
            'ip'             => '127.0.0.1',
        ], $overrides));
    }

    /** @test */
    public function is_valid_returns_true_for_active_unexpired_under_limit_token()
    {
        $token = $this->makeToken();
        $this->assertTrue($token->isValid());
    }

    /** @test */
    public function is_valid_returns_false_when_status_is_revoked()
    {
        $token = $this->makeToken(['status' => 'revoked']);
        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function is_valid_returns_false_when_status_is_expired()
    {
        $token = $this->makeToken(['status' => 'expired']);
        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function is_valid_returns_false_when_expires_at_is_in_the_past()
    {
        $token = $this->makeToken(['expires_at' => now()->subHour()]);
        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function is_valid_returns_false_when_download_count_equals_max()
    {
        $token = $this->makeToken(['download_count' => 3, 'max_downloads' => 3]);
        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function is_valid_returns_false_when_download_count_exceeds_max()
    {
        $token = $this->makeToken(['download_count' => 5, 'max_downloads' => 3]);
        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function is_valid_returns_true_when_download_count_is_one_below_max()
    {
        $token = $this->makeToken(['download_count' => 2, 'max_downloads' => 3]);
        $this->assertTrue($token->isValid());
    }

    /** @test */
    public function token_gets_uuid_on_create()
    {
        $token = $this->makeToken();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $token->id
        );
    }

    /** @test */
    public function token_primary_key_is_not_auto_increment()
    {
        $token = $this->makeToken();
        $this->assertFalse($token->getIncrementing());
        $this->assertEquals('string', $token->getKeyType());
    }

    /** @test */
    public function two_tokens_get_different_uuids()
    {
        $t1 = $this->makeToken(['token_hash' => hash('sha256', 'a')]);
        $t2 = $this->makeToken(['token_hash' => hash('sha256', 'b')]);

        $this->assertNotEquals($t1->id, $t2->id);
    }

    /** @test */
    public function expires_at_is_cast_to_datetime()
    {
        $token = $this->makeToken();
        $this->assertInstanceOf(\Carbon\Carbon::class, $token->expires_at);
    }
}
