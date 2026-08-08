<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit tests for PaymentGateway model Moneroo integration
 */
class MonerooPaymentGatewayModelTest extends TestCase
{
    use RefreshDatabase;

    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = PaymentGateway::create([
            'id' => 20,
            'name' => 'Moneroo',
            'keyword' => 'moneroo',
            'type' => 'automatic',
            'information' => json_encode([
                'public_key' => 'pk_test_123',
                'secret_key' => 'sk_test_456',
                'text' => 'Pay via Moneroo',
            ]),
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_can_convert_auto_data_from_json()
    {
        $data = $this->gateway->convertAutoData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('public_key', $data);
        $this->assertArrayHasKey('secret_key', $data);
        $this->assertArrayHasKey('text', $data);
        $this->assertEquals('pk_test_123', $data['public_key']);
        $this->assertEquals('sk_test_456', $data['secret_key']);
    }

    /** @test */
    public function show_keyword_returns_moneroo_keyword()
    {
        $keyword = $this->gateway->showKeyword();

        $this->assertEquals('moneroo', $keyword);
    }

    /** @test */
    public function show_keyword_returns_other_when_keyword_is_null()
    {
        $gateway = PaymentGateway::create([
            'name' => 'Custom Gateway',
            'keyword' => null,
            'type' => 'automatic',
            'information' => '{}',
            'status' => 1,
        ]);

        $this->assertEquals('other', $gateway->showKeyword());
    }

    /** @test */
    public function show_form_returns_no_for_moneroo()
    {
        $showForm = $this->gateway->showForm();

        $this->assertEquals('no', $showForm);
    }

    /** @test */
    public function show_form_returns_no_for_paypal_and_moneroo()
    {
        // Test Moneroo
        $moneroo = PaymentGateway::create([
            'name' => 'Moneroo',
            'keyword' => 'moneroo',
            'type' => 'automatic',
            'information' => '{}',
            'status' => 1,
        ]);

        $this->assertEquals('no', $moneroo->showForm());

        // Test PayPal
        $paypal = PaymentGateway::create([
            'name' => 'PayPal',
            'keyword' => 'paypal',
            'type' => 'automatic',
            'information' => '{}',
            'status' => 1,
        ]);

        $this->assertEquals('no', $paypal->showForm());
    }

    /** @test */
    public function show_form_returns_yes_for_stripe()
    {
        $stripe = PaymentGateway::create([
            'name' => 'Stripe',
            'keyword' => 'stripe',
            'type' => 'automatic',
            'information' => '{}',
            'status' => 1,
        ]);

        $this->assertEquals('yes', $stripe->showForm());
    }

    /** @test */
    public function gateway_status_can_be_toggled()
    {
        $this->assertEquals(1, $this->gateway->status);

        $this->gateway->status = 0;
        $this->gateway->save();

        $this->gateway->refresh();
        $this->assertEquals(0, $this->gateway->status);
    }

    /** @test */
    public function gateway_information_can_be_updated()
    {
        $newInfo = [
            'public_key' => 'pk_live_new',
            'secret_key' => 'sk_live_new',
            'text' => 'Updated text',
        ];

        $this->gateway->information = json_encode($newInfo);
        $this->gateway->save();

        $this->gateway->refresh();
        $data = $this->gateway->convertAutoData();

        $this->assertEquals('pk_live_new', $data['public_key']);
        $this->assertEquals('sk_live_new', $data['secret_key']);
        $this->assertEquals('Updated text', $data['text']);
    }

    /** @test */
    public function gateway_type_is_automatic()
    {
        $this->assertEquals('automatic', $this->gateway->type);
    }

    /** @test */
    public function moneroo_gateway_has_correct_id()
    {
        $this->assertEquals(20, $this->gateway->id);
    }

    /** @test */
    public function moneroo_can_be_found_by_keyword()
    {
        $found = PaymentGateway::where('keyword', 'moneroo')->first();

        $this->assertNotNull($found);
        $this->assertEquals('Moneroo', $found->name);
        $this->assertEquals(20, $found->id);
    }

    /** @test */
    public function active_moneroo_gateway_can_be_queried()
    {
        $active = PaymentGateway::where('keyword', 'moneroo')
            ->where('status', 1)
            ->first();

        $this->assertNotNull($active);
        $this->assertEquals(1, $active->status);
    }

    /** @test */
    public function inactive_moneroo_gateway_is_not_in_active_query()
    {
        $this->gateway->status = 0;
        $this->gateway->save();

        $active = PaymentGateway::where('keyword', 'moneroo')
            ->where('status', 1)
            ->first();

        $this->assertNull($active);
    }

    /** @test */
    public function gateway_has_no_timestamps()
    {
        $this->assertFalse($this->gateway->timestamps);
    }

    /** @test */
    public function information_field_is_fillable()
    {
        $fillable = $this->gateway->getFillable();

        $this->assertContains('information', $fillable);
    }

    /** @test */
    public function get_auto_data_text_returns_last_element()
    {
        $text = $this->gateway->getAutoDataText();

        $this->assertEquals('Pay via Moneroo', $text);
    }

    /** @test */
    public function all_payment_gateways_can_be_listed()
    {
        // Create additional gateways
        PaymentGateway::create([
            'name' => 'Stripe',
            'keyword' => 'stripe',
            'type' => 'automatic',
            'information' => '{}',
            'status' => 1,
        ]);

        PaymentGateway::create([
            'name' => 'PayPal',
            'keyword' => 'paypal',
            'type' => 'automatic',
            'information' => '{}',
            'status' => 1,
        ]);

        $gateways = PaymentGateway::all();

        $this->assertGreaterThanOrEqual(3, $gateways->count());
        $this->assertTrue($gateways->contains('keyword', 'moneroo'));
    }
}
