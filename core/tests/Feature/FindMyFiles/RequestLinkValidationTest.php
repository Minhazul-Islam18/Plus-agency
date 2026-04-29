<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;

class RequestLinkValidationTest extends FindMyFilesTestCase
{
    /** @test */
    public function missing_email_returns_validation_error()
    {
        $response = $this->postJson('/find-my-files/request-link', [
            'order_number' => 'ORD-001',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'validation']);
    }

    /** @test */
    public function missing_order_number_returns_validation_error()
    {
        $response = $this->postJson('/find-my-files/request-link', [
            'email' => 'buyer@example.com',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'validation']);
    }

    /** @test */
    public function invalid_email_format_returns_validation_error()
    {
        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'not-an-email',
            'order_number' => 'ORD-001',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'validation']);
    }

    /** @test */
    public function order_number_too_short_returns_validation_error()
    {
        $response = $this->postJson('/find-my-files/request-link', [
            'email'        => 'buyer@example.com',
            'order_number' => 'AB',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'validation']);
    }

    /** @test */
    public function empty_body_returns_validation_error()
    {
        $response = $this->postJson('/find-my-files/request-link', []);

        $response->assertOk()
            ->assertJson(['status' => 'error', 'type' => 'validation']);
    }

    /** @test */
    public function validation_failure_logs_link_requested_with_invalid_input()
    {
        $this->postJson('/find-my-files/request-link', [
            'email'        => 'bad-email',
            'order_number' => 'ORD-001',
        ]);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::LINK_REQUESTED,
            'result'     => 'INVALID_INPUT',
        ]);
    }
}
