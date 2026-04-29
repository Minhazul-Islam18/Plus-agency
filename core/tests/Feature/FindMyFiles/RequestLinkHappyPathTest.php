<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;
use App\SecureToken;

class RequestLinkHappyPathTest extends FindMyFilesTestCase
{
    private function validRequest(string $email, string $order): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/find-my-files/request-link', [
            'email'        => $email,
            'order_number' => $order,
        ]);
    }

    /** @test */
    public function valid_request_returns_success_with_redirect()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $response = $this->validRequest('buyer@example.com', 'ORD-001');

        $response->assertOk()
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['status', 'redirect']);
    }

    /** @test */
    public function valid_request_creates_secure_token_in_db()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->validRequest('buyer@example.com', 'ORD-001');

        $this->assertDatabaseHas('secure_tokens', [
            'order_id' => 'ORD-001',
            'status'   => 'active',
        ]);
    }

    /** @test */
    public function raw_token_is_never_stored_only_hash()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->validRequest('buyer@example.com', 'ORD-001');

        $token = SecureToken::where('order_id', 'ORD-001')->first();
        $this->assertNotNull($token);

        // token_hash should be a 64-char hex SHA-256, not a raw HMAC key
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token->token_hash);

        // email is not stored in plaintext anywhere in secure_tokens
        $this->assertFalse(
            SecureToken::where('order_id', 'ORD-001')
                ->where('email_hash', 'buyer@example.com')
                ->exists()
        );
    }

    /** @test */
    public function email_stored_as_sha256_hash_not_plaintext()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->validRequest('buyer@example.com', 'ORD-001');

        $expectedHash = hash('sha256', 'buyer@example.com');

        $this->assertDatabaseHas('secure_tokens', [
            'order_id'   => 'ORD-001',
            'email_hash' => $expectedHash,
        ]);
    }

    /** @test */
    public function token_expires_24_hours_from_creation()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->validRequest('buyer@example.com', 'ORD-001');

        $token = SecureToken::where('order_id', 'ORD-001')->first();

        $this->assertTrue(
            now()->addHours(23)->lt($token->expires_at) &&
            $token->expires_at->lt(now()->addHours(25))
        );
    }

    /** @test */
    public function re_request_revokes_old_active_tokens_before_creating_new()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        // Create existing active token
        $this->makeActiveToken($purchase);

        $this->assertDatabaseHas('secure_tokens', ['order_id' => 'ORD-001', 'status' => 'active']);

        $this->validRequest('buyer@example.com', 'ORD-001');

        // Old token should be revoked
        $allTokens = SecureToken::where('order_id', 'ORD-001')->get();
        $this->assertCount(2, $allTokens);

        $revoked = $allTokens->where('status', 'revoked');
        $active  = $allTokens->where('status', 'active');

        $this->assertCount(1, $revoked);
        $this->assertCount(1, $active);
    }

    /** @test */
    public function successful_request_logs_link_sent()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->validRequest('buyer@example.com', 'ORD-001');

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_SENT,
            'order_id'   => 'ORD-001',
            'result'     => 'OK',
        ]);
    }

    /** @test */
    public function order_number_is_uppercased_before_lookup()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'ord-001', // lowercase input
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'success']);
    }

    /** @test */
    public function redirect_points_to_link_sent_route()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $response = $this->validRequest('buyer@example.com', 'ORD-001');

        $data = $response->json();
        $this->assertStringContainsString('find-my-files/link-sent', $data['redirect']);
    }

    /** @test */
    public function successful_request_does_not_increment_rate_limit_counters()
    {
        $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);

        $this->validRequest('buyer@example.com', 'ORD-001');

        $this->assertDatabaseMissing('rate_limit_attempts', [
            'key' => 'ip:127.0.0.1',
        ]);
    }
}
