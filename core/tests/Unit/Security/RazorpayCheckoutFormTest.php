<?php

namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

class RazorpayCheckoutFormTest extends TestCase
{
    private function viewPath(): string
    {
        return __DIR__ . '/../../../resources/views/front/razorpay.blade.php';
    }

    public function test_notify_form_includes_a_csrf_token(): void
    {
        // Regression guard: Razorpay's checkout.js submits this form via a
        // plain document.razorpayform.submit() straight to the CSRF-guarded
        // notify route. Without @csrf here, every tender purchase paid via
        // Razorpay would 419 right after a successful payment.
        $view = file_get_contents($this->viewPath());

        $this->assertStringContainsString('@csrf', $view);
    }

    public function test_notify_form_still_posts_to_the_notify_url(): void
    {
        $view = file_get_contents($this->viewPath());

        $this->assertMatchesRegularExpression('/action="\{\{\s*\$notify_url\s*\}\}"/', $view);
        $this->assertStringContainsString('method="POST"', $view);
    }
}
