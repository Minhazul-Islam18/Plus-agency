<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Package;
use App\PackageOrder;
use App\Language;
use App\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Mockery;

class MonerooPackagePaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $package;
    protected $language;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test language
        $this->language = Language::factory()->create([
            'code' => 'en',
            'is_default' => 1,
        ]);

        // Create test package
        $this->package = Package::factory()->create([
            'title' => 'Premium Package',
            'price' => 199.99,
            'term' => 'monthly',
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
    public function it_validates_required_package_order_fields()
    {
        $response = $this->post(route('front.moneroo.submit'), [
            'package_id' => $this->package->id,
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /** @test */
    public function it_initializes_moneroo_payment_for_package()
    {
        // Mock Moneroo Payment
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['description'] === 'Order Package: Premium Package';
            }))
            ->andReturn([
                'transaction_id' => 'package_transaction_123',
                'checkout_url' => 'https://checkout.moneroo.io/package_checkout',
            ]);

        $response = $this->post(route('front.moneroo.submit'), [
            'package_id' => $this->package->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'method' => 'moneroo',
        ]);

        // Assert session data
        $this->assertTrue(Session::has('monerooPackageOrderId'));
        $this->assertTrue(Session::has('monerooPackageId'));
        $this->assertTrue(Session::has('monerooTransactionId'));
        $this->assertEquals('package_transaction_123', Session::get('monerooTransactionId'));

        // Assert redirect to Moneroo checkout
        $response->assertRedirect('https://checkout.moneroo.io/package_checkout');
    }

    /** @test */
    public function it_creates_package_order_with_pending_status()
    {
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->andReturn([
                'transaction_id' => 'package_transaction_123',
                'checkout_url' => 'https://checkout.moneroo.io/test',
            ]);

        $this->post(route('front.moneroo.submit'), [
            'package_id' => $this->package->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'method' => 'moneroo',
        ]);

        // Assert order was created with pending status (0)
        $this->assertDatabaseHas('package_orders', [
            'package_id' => $this->package->id,
            'email' => 'john@example.com',
            'payment_status' => 0,
        ]);
    }

    /** @test */
    public function it_completes_package_payment_successfully()
    {
        // Create pending order
        $order = PackageOrder::create([
            'package_id' => $this->package->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'payment_status' => 0,
            'method' => 'moneroo',
            'gateway_type' => 'online',
        ]);

        // Set session data
        Session::put('monerooPackageOrderId', $order->id);
        Session::put('monerooPackageId', $this->package->id);
        Session::put('monerooTransactionId', 'package_transaction_123');

        // Mock successful payment verification
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('verify')
            ->with('package_transaction_123')
            ->once()
            ->andReturn([
                'status' => 'completed',
                'id' => 'moneroo_payment_id',
            ]);

        // Mock PDF
        $pdfMock = Mockery::mock('alias:PDF');
        $pdfMock->shouldReceive('loadView')
            ->andReturnSelf();
        $pdfMock->shouldReceive('setPaper')
            ->andReturnSelf();
        $pdfMock->shouldReceive('save')
            ->andReturn(true);

        $response = $this->get(route('front.moneroo.notify'));

        // Assert order status updated to completed (1)
        $this->assertDatabaseHas('package_orders', [
            'id' => $order->id,
            'payment_status' => 1,
        ]);

        // Assert session cleared
        $this->assertFalse(Session::has('monerooPackageOrderId'));
        $this->assertFalse(Session::has('monerooPackageId'));
        $this->assertFalse(Session::has('monerooTransactionId'));

        // Assert redirect to confirmation page
        $response->assertRedirect(route('front.packageorder.confirmation', [$this->package->id, $order->id]));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function it_handles_failed_package_payment()
    {
        $order = PackageOrder::create([
            'package_id' => $this->package->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'payment_status' => 0,
        ]);

        Session::put('monerooPackageOrderId', $order->id);
        Session::put('monerooPackageId', $this->package->id);
        Session::put('monerooTransactionId', 'package_transaction_123');

        // Mock failed payment
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('verify')
            ->once()
            ->andReturn(['status' => 'failed']);

        $response = $this->get(route('front.moneroo.notify'));

        // Assert order status remains pending
        $this->assertDatabaseHas('package_orders', [
            'id' => $order->id,
            'payment_status' => 0,
        ]);

        // Assert redirect to cancel page
        $response->assertRedirect(route('front.payment.cancle', $this->package->id));
    }

    /** @test */
    public function it_handles_missing_transaction_id_in_callback()
    {
        Session::put('monerooPackageId', $this->package->id);
        // Don't set transaction ID

        $response = $this->get(route('front.moneroo.notify'));

        $response->assertRedirect(route('front.payment.cancle', $this->package->id));
    }

    /** @test */
    public function it_converts_package_price_to_usd()
    {
        // Update base currency
        $basicExtra = $this->language->basic_extra;
        $basicExtra->base_currency_text = 'EUR';
        $basicExtra->base_currency_rate = 0.85;
        $basicExtra->save();

        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->with(Mockery::on(function ($arg) {
                // Price should be converted: 199.99 / 0.85 = 235.28
                return abs($arg['amount'] - 235.28) < 0.01 && $arg['currency'] === 'USD';
            }))
            ->andReturn([
                'transaction_id' => 'test_123',
                'checkout_url' => 'https://checkout.moneroo.io/test',
            ]);

        $this->post(route('front.moneroo.submit'), [
            'package_id' => $this->package->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_handles_payment_verification_exception()
    {
        $order = PackageOrder::create([
            'package_id' => $this->package->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'payment_status' => 0,
        ]);

        Session::put('monerooPackageOrderId', $order->id);
        Session::put('monerooPackageId', $this->package->id);
        Session::put('monerooTransactionId', 'package_transaction_123');

        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('verify')
            ->andThrow(new \Exception('Network error'));

        $response = $this->get(route('front.moneroo.notify'));

        $response->assertRedirect(route('front.payment.cancle', $this->package->id));
        $response->assertSessionHas('unsuccess');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
