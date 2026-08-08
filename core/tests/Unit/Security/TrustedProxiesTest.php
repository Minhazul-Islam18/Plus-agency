<?php

namespace Tests\Unit\Security;

use App\Http\Middleware\TrustProxies;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Reads the declared default property value via reflection rather than
 * instantiating the middleware — TrustProxies has no constructor deps,
 * but this keeps the test framework-free and fast either way.
 */
class TrustedProxiesTest extends TestCase
{
    private function proxies(): array
    {
        return (new ReflectionClass(TrustProxies::class))->getDefaultProperties()['proxies'];
    }

    public function test_proxies_list_is_not_empty(): void
    {
        // Regression guard: this was `null` (trust nothing) before Cloudflare
        // was put in front of the site, which would have made every visitor
        // appear to originate from Cloudflare's edge IP once proxied.
        $this->assertNotEmpty($this->proxies());
    }

    public function test_proxies_include_known_cloudflare_ipv4_ranges(): void
    {
        $proxies = $this->proxies();

        $this->assertContains('173.245.48.0/20', $proxies);
        $this->assertContains('104.16.0.0/13', $proxies);
        $this->assertContains('172.64.0.0/13', $proxies);
    }

    public function test_proxies_include_ipv6_ranges(): void
    {
        $proxies = $this->proxies();
        $ipv6 = array_filter($proxies, fn ($p) => str_contains($p, ':'));

        $this->assertNotEmpty($ipv6, 'Cloudflare IPv6 ranges must be trusted too, not just IPv4');
    }

    public function test_every_entry_is_a_valid_cidr_range(): void
    {
        foreach ($this->proxies() as $range) {
            [$ip, $mask] = array_pad(explode('/', $range, 2), 2, null);
            $this->assertNotNull($mask, "$range is missing a CIDR mask");
            $this->assertNotFalse(
                filter_var($ip, FILTER_VALIDATE_IP),
                "$range does not start with a valid IP address"
            );
        }
    }
}
