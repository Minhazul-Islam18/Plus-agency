<?php

namespace Tests\Unit\Tender;

use App\Services\TenderPdfStamper;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Pure-logic coverage for the watermark text/colour helpers, so the personalised
 * download stamp keeps rendering the buyer's details even as the service evolves.
 */
class WatermarkTextTest extends TestCase
{
    private function invokePrivate(string $method, array $args)
    {
        $m = new ReflectionMethod(TenderPdfStamper::class, $method);
        $m->setAccessible(true);
        return $m->invoke(new TenderPdfStamper(), ...$args);
    }

    /** @test */
    public function build_text_replaces_known_tokens_and_drops_blank_lines()
    {
        $out = $this->invokePrivate('buildText', [
            "{company}\nDownloaded by: {name}\n{missing}\nTender ID: {tender_code}",
            ['company' => 'ICA', 'name' => 'John Doe', 'tender_code' => 'AO-2026-001'],
        ]);

        $this->assertSame("ICA\nDownloaded by: John Doe\nTender ID: AO-2026-001", $out);
    }

    /** @test */
    public function build_text_falls_back_to_a_default_template_when_empty()
    {
        $out = $this->invokePrivate('buildText', ['   ', ['company' => 'ICA', 'name' => 'J', 'tender_code' => 'X', 'datetime' => 'now']]);
        $this->assertStringContainsString('ICA', $out);
        $this->assertStringContainsString('Downloaded by: J', $out);
    }

    /** @test */
    public function hex_to_rgb_parses_six_digit_hex_with_hash()
    {
        $this->assertSame([1.0, 0.0, 0.0], $this->invokePrivate('hexToRgb', ['#FF0000']));
    }

    /** @test */
    public function hex_to_rgb_expands_shorthand()
    {
        $this->assertSame([0.0, 1.0, 0.0], $this->invokePrivate('hexToRgb', ['0f0']));
    }

    /** @test */
    public function hex_to_rgb_falls_back_to_red_on_invalid_input()
    {
        $this->assertSame([1.0, 0.0, 0.0], $this->invokePrivate('hexToRgb', ['not-a-color']));
    }
}
