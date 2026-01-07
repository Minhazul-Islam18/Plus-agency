<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Product;
use App\ProductOrder;
use App\Language;
use App\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Mockery;

class MonerooProductPaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $product;
    protected $language;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test language
        $this->language = Language::factory()->create([
            'code' => 'en',
            'is_default' => 1,
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'title' => 'Test Product',
            'slug' => 'test-product',
            'current_price' => 49.99,
            'previous_price' => 79.99,
        ]);

        // Create Moneroo gateway
        PaymentGateway::create([
            'id' => 20,
            'name' => 'Moneroo',
            'keyword' => 'moneroo',
            'type' => 'automatic',
            'information' => json_encode([
                'public_key' => 'test_public_key',
                'secret_key' => 'test_secret_key',
                'text' => 'Pay via Moneroo',
            ]),
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_returns_404_when_cart_is_empty()
    {
        $response = $this->post(route('product.moneroo.submit'), [
            'billing_fname' => 'John',
            'billing_lname' => 'Doe',
            'billing_email' => 'john@example.com',
            'billing_address' => '123 Main St',
            'billing_city' => 'New York',
            'billing_country' => 'USA',
            'billing_number' => '+1234567890',
            'shpping_fname' => 'John',
            'shpping_lname' => 'Doe',
            'shpping_email' => 'john@example.com',
            'shpping_address' => '123 Main St',
            'shpping_city' => 'New York',
            'shpping_country' => 'USA',
            'shpping_number' => '+1234567890',
            'shipping_charge' => 0,
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_validates_required_billing_information()
    {
        // Add product to cart
        Session::put('cart', [
            $this->product->id => [
                'qty' => 2,
                'price' => $this->product->current_price,
            ]
        ]);

        $response = $this->post(route('product.moneroo.submit'), [
            'billing_fname' => '',
            'billing_email' => 'invalid-email',
            'shipping_charge' => 0,
        ]);

        $response->assertSessionHasErrors(['billing_fname', 'billing_email']);
    }

    /** @test */
    public function it_initializes_moneroo_payment_with_valid_cart()
    {
        // Add product to cart
        Session::put('cart', [
            $this->product->id => [
                'qty' => 2,
                'price' => $this->product->current_price,
            ]
        ]);

        // Mock Moneroo Payment
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->andReturn([
                'transaction_id' => 'product_transaction_123',
                'checkout_url' => 'https://checkout.moneroo.io/product_checkout',
            ]);

        $response = $this->post(route('product.moneroo.submit'), [
            'billing_fname' => 'John',
            'billing_lname' => 'Doe',
            'billing_email' => 'john@example.com',
            'billing_address' => '123 Main St',
            'billing_city' => 'New York',
            'billing_country' => 'USA',
            'billing_number' => '+1234567890',
            'shpping_fname' => 'John',
            'shpping_lname' => 'Doe',
            'shpping_email' => 'john@example.com',
            'shpping_address' => '123 Main St',
            'shpping_city' => 'New York',
            'shpping_country' => 'USA',
            'shpping_number' => '+1234567890',
            'shipping_charge' => 0,
        ]);

        // Assert session data
        $this->assertTrue(Session::has('monerooOrderData'));
        $this->assertTrue(Session::has('monerooTransactionId'));
        $this->assertEquals('product_transaction_123', Session::get('monerooTransactionId'));

        // Assert redirect
        $response->assertRedirect('https://checkout.moneroo.io/product_checkout');
    }

    /** @test */
    public function it_completes_payment_and_creates_order()
    {
        // Set up cart
        Session::put('cart', [
            $this->product->id => [
                'qty' => 1,
                'price' => $this->product->current_price,
            ]
        ]);

        // Set up session data
        $orderData = [
            'billing_fname' => 'John',
            'billing_lname' => 'Doe',
            'billing_email' => 'john@example.com',
            'billing_address' => '123 Main St',
            'billing_city' => 'New York',
            'billing_country' => 'USA',
            'billing_number' => '+1234567890',
            'shpping_fname' => 'John',
            'shpping_lname' => 'Doe',
            'shpping_email' => 'john@example.com',
            'shpping_address' => '123 Main St',
            'shpping_city' => 'New York',
            'shpping_country' => 'USA',
            'shpping_number' => '+1234567890',
            'shipping_charge' => 0,
            'method' => 'moneroo',
        ];

        Session::put('monerooOrderData', $orderData);
        Session::put('monerooTransactionId', 'product_transaction_123');

        // Mock payment verification
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('verify')
            ->with('product_transaction_123')
            ->once()
            ->andReturn([
                'status' => 'success',
                'id' => 'moneroo_charge_123',
            ]);

        // Mock PDF
        $pdfMock = Mockery::mock('alias:PDF');
        $pdfMock->shouldReceive('loadView')
            ->andReturnSelf();
        $pdfMock->shouldReceive('save')
            ->andReturn(true);

        $response = $this->get(route('product.moneroo.notify'));

        // Assert order was created
        $this->assertDatabaseHas('product_orders', [
            'billing_email' => 'john@example.com',
            'billing_fname' => 'John',
            'method' => 'moneroo',
            'gateway_type' => 'online',
        ]);

        // Assert order items were created
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Assert cart was cleared
        $this->assertFalse(Session::has('cart'));
        $this->assertFalse(Session::has('monerooOrderData'));
        $this->assertFalse(Session::has('monerooTransactionId'));

        // Assert redirect to success page
        $response->assertRedirect(route('product.payment.return'));
    }

    /** @test */
    public function it_handles_failed_product_payment()
    {
        Session::put('monerooOrderData', ['billing_email' => 'test@example.com']);
        Session::put('monerooTransactionId', 'product_transaction_123');

        // Mock failed payment
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('verify')
            ->once()
            ->andReturn(['status' => 'failed']);

        $response = $this->get(route('product.moneroo.notify'));

        // Assert no order was created
        $this->assertDatabaseMissing('product_orders', [
            'billing_email' => 'test@example.com',
        ]);

        // Assert redirect to cancel page
        $response->assertRedirect(route('product.payment.cancle'));
    }

    /** @test */
    public function it_calculates_order_total_with_shipping()
    {
        // Add product to cart
        Session::put('cart', [
            $this->product->id => [
                'qty' => 2,
                'price' => $this->product->current_price,
            ]
        ]);

        // Create shipping charge
        $shipping = \App\ShippingCharge::create([
            'title' => 'Express Shipping',
            'charge' => 15.00,
        ]);

        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->with(Mockery::on(function ($arg) {
                // Total should be (49.99 * 2) + 15.00 = 114.98
                return abs($arg['amount'] - 114.98) < 0.01;
            }))
            ->andReturn([
                'transaction_id' => 'test_123',
                'checkout_url' => 'https://checkout.moneroo.io/test',
            ]);

        $this->post(route('product.moneroo.submit'), [
            'billing_fname' => 'John',
            'billing_lname' => 'Doe',
            'billing_email' => 'john@example.com',
            'billing_address' => '123 Main St',
            'billing_city' => 'New York',
            'billing_country' => 'USA',
            'billing_number' => '+1234567890',
            'shpping_fname' => 'John',
            'shpping_lname' => 'Doe',
            'shpping_email' => 'john@example.com',
            'shpping_address' => '123 Main St',
            'shpping_city' => 'New York',
            'shpping_country' => 'USA',
            'shpping_number' => '+1234567890',
            'shipping_charge' => $shipping->id,
        ]);
    }

    /** @test */
    public function it_handles_payment_initialization_exception()
    {
        Session::put('cart', [
            $this->product->id => ['qty' => 1, 'price' => 49.99]
        ]);

        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->andThrow(new \Exception('API Error'));

        $response = $this->post(route('product.moneroo.submit'), [
            'billing_fname' => 'John',
            'billing_lname' => 'Doe',
            'billing_email' => 'john@example.com',
            'billing_address' => '123 Main St',
            'billing_city' => 'New York',
            'billing_country' => 'USA',
            'billing_number' => '+1234567890',
            'shpping_fname' => 'John',
            'shpping_lname' => 'Doe',
            'shpping_email' => 'john@example.com',
            'shpping_address' => '123 Main St',
            'shpping_city' => 'New York',
            'shpping_country' => 'USA',
            'shpping_number' => '+1234567890',
            'shipping_charge' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('unsuccess');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
