<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Course;
use App\CoursePurchase;
use App\Language;
use App\PaymentGateway;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Mockery;

class MonerooCoursePaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $course;
    protected $language;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'fname' => 'Test',
            'lname' => 'User',
            'email' => 'test@example.com',
        ]);

        // Create test language
        $this->language = Language::factory()->create([
            'code' => 'en',
            'is_default' => 1,
        ]);

        // Create test course
        $this->course = Course::factory()->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'current_price' => 99.99,
            'previous_price' => 149.99,
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
    public function it_redirects_unauthenticated_users_to_login()
    {
        $response = $this->post(route('course.payment.moneroo'), [
            'course_id' => $this->course->id,
        ]);

        $response->assertRedirect(route('user.login'));
    }

    /** @test */
    public function it_initializes_moneroo_payment_for_authenticated_user()
    {
        $this->actingAs($this->user);

        // Mock Moneroo Payment facade
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->andReturn([
                'transaction_id' => 'test_transaction_123',
                'checkout_url' => 'https://checkout.moneroo.io/test_checkout_url',
            ]);

        $response = $this->post(route('course.payment.moneroo'), [
            'course_id' => $this->course->id,
        ]);

        // Assert session has required data
        $this->assertTrue(Session::has('courseData'));
        $this->assertTrue(Session::has('currency'));
        $this->assertTrue(Session::has('monerooTransactionId'));
        $this->assertEquals('test_transaction_123', Session::get('monerooTransactionId'));

        // Assert redirect to Moneroo checkout
        $response->assertRedirect('https://checkout.moneroo.io/test_checkout_url');
    }

    /** @test */
    public function it_handles_payment_initialization_failure()
    {
        $this->actingAs($this->user);

        // Mock Moneroo Payment facade to throw exception
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->andThrow(new \Exception('Invalid API credentials'));

        $response = $this->post(route('course.payment.moneroo'), [
            'course_id' => $this->course->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('unsuccess');
    }

    /** @test */
    public function it_verifies_and_completes_successful_payment()
    {
        $this->actingAs($this->user);

        // Set up session data
        Session::put('courseData', $this->course);
        Session::put('currency', 'USD');
        Session::put('monerooTransactionId', 'test_transaction_123');

        // Mock Moneroo Payment verification
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('verify')
            ->with('test_transaction_123')
            ->once()
            ->andReturn([
                'status' => 'success',
                'id' => 'moneroo_payment_id_123',
            ]);

        // Mock PDF facade
        $pdfMock = Mockery::mock('alias:PDF');
        $pdfMock->shouldReceive('loadView')
            ->andReturnSelf();
        $pdfMock->shouldReceive('setPaper')
            ->andReturnSelf();
        $pdfMock->shouldReceive('save')
            ->andReturn(true);

        $response = $this->get(route('course.moneroo.notify'));

        // Assert course purchase was created
        $this->assertDatabaseHas('course_purchases', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'payment_method' => 'moneroo',
            'payment_status' => 'Completed',
        ]);

        // Assert session was cleared
        $this->assertFalse(Session::has('courseData'));
        $this->assertFalse(Session::has('currency'));
        $this->assertFalse(Session::has('monerooTransactionId'));

        // Assert redirect to complete page
        $response->assertRedirect(route('course.moneroo.complete'));
    }

    /** @test */
    public function it_handles_failed_payment_verification()
    {
        $this->actingAs($this->user);

        Session::put('courseData', $this->course);
        Session::put('currency', 'USD');
        Session::put('monerooTransactionId', 'test_transaction_123');

        // Mock failed payment verification
        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('verify')
            ->with('test_transaction_123')
            ->once()
            ->andReturn([
                'status' => 'failed',
            ]);

        $response = $this->get(route('course.moneroo.notify'));

        // Assert no course purchase was created
        $this->assertDatabaseMissing('course_purchases', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);

        // Assert redirect to cancel page
        $response->assertRedirect(route('course.moneroo.cancel'));
    }

    /** @test */
    public function it_redirects_to_cancel_when_transaction_id_is_missing()
    {
        $this->actingAs($this->user);

        // Don't set transaction ID in session
        Session::put('courseData', $this->course);
        Session::put('currency', 'USD');

        $response = $this->get(route('course.moneroo.notify'));

        $response->assertRedirect(route('course.moneroo.cancel'));
    }

    /** @test */
    public function it_converts_currency_correctly()
    {
        $this->actingAs($this->user);

        // Update language settings to use different currency
        $basicExtra = $this->language->basic_extra;
        $basicExtra->base_currency_text = 'BDT';
        $basicExtra->base_currency_rate = 110;
        $basicExtra->save();

        $paymentMock = Mockery::mock('alias:Moneroo\Payment');
        $paymentMock->shouldReceive('init')
            ->once()
            ->with(Mockery::on(function ($arg) {
                // Check if amount is converted correctly (99.99 / 110 = 0.91)
                return abs($arg['amount'] - 0.91) < 0.01;
            }))
            ->andReturn([
                'transaction_id' => 'test_transaction_123',
                'checkout_url' => 'https://checkout.moneroo.io/test',
            ]);

        $this->post(route('course.payment.moneroo'), [
            'course_id' => $this->course->id,
        ]);
    }

    /** @test */
    public function complete_page_loads_successfully()
    {
        $response = $this->get(route('course.moneroo.complete'));

        $response->assertOk();
        $response->assertViewIs('front.course.success');
    }

    /** @test */
    public function cancel_page_redirects_back_with_error_message()
    {
        $response = $this->from(route('course_details', $this->course->slug))
            ->get(route('course.moneroo.cancel'));

        $response->assertRedirect(route('course_details', $this->course->slug));
        $response->assertSessionHas('unsuccess', 'Payment Unsuccessful');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
