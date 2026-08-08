<?php

namespace Tests\Unit\Security;

use App\Http\Middleware\VerifyCsrfToken;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CsrfExceptionsTest extends TestCase
{
    public function test_no_routes_are_exempted_from_csrf_protection(): void
    {
        // Regression guard: this list used to contain ~30 payment-gateway
        // paths, none of which corresponded to an actual registered route
        // (dead leftovers from the base template). The one real payment
        // callback (Tender's Razorpay notify) is CSRF-protected properly
        // via a token in the form instead of an exemption — see
        // resources/views/front/razorpay.blade.php.
        $except = (new ReflectionClass(VerifyCsrfToken::class))->getDefaultProperties()['except'];

        $this->assertSame([], $except);
    }
}
