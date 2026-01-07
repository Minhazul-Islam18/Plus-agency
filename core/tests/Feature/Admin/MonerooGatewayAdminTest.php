<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Admin;
use App\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;

class MonerooGatewayAdminTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = Admin::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role' => 'super',
        ]);

        // Create Moneroo gateway
        $this->gateway = PaymentGateway::create([
            'id' => 20,
            'name' => 'Moneroo',
            'keyword' => 'moneroo',
            'type' => 'automatic',
            'information' => json_encode([
                'public_key' => '',
                'secret_key' => '',
                'text' => 'Pay via Moneroo',
            ]),
            'status' => 0,
        ]);
    }

    /** @test */
    public function admin_can_view_gateway_index_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.gateway.index'));

        $response->assertOk();
        $response->assertViewIs('admin.gateways.index');
        $response->assertViewHas('moneroo');
    }

    /** @test */
    public function gateway_index_loads_moneroo_configuration()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.gateway.index'));

        $response->assertOk();

        // Check if Moneroo gateway is loaded
        $moneroo = $response->viewData('moneroo');
        $this->assertNotNull($moneroo);
        $this->assertEquals(20, $moneroo->id);
        $this->assertEquals('moneroo', $moneroo->keyword);
    }

    /** @test */
    public function admin_can_update_moneroo_credentials()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 1,
                'public_key' => 'pk_test_1234567890',
                'secret_key' => 'sk_test_0987654321',
            ]);

        // Assert redirect back
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Moneroo information updated successfully!');

        // Assert database was updated
        $this->assertDatabaseHas('payment_gateways', [
            'id' => 20,
            'status' => 1,
        ]);

        // Assert credentials stored in JSON
        $gateway = PaymentGateway::find(20);
        $info = json_decode($gateway->information, true);
        $this->assertEquals('pk_test_1234567890', $info['public_key']);
        $this->assertEquals('sk_test_0987654321', $info['secret_key']);
    }

    /** @test */
    public function moneroo_update_changes_gateway_status()
    {
        // Initially inactive
        $this->assertEquals(0, $this->gateway->status);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 1,
                'public_key' => 'pk_test_key',
                'secret_key' => 'sk_test_secret',
            ]);

        // Assert status changed to active
        $this->gateway->refresh();
        $this->assertEquals(1, $this->gateway->status);
    }

    /** @test */
    public function moneroo_update_saves_text_message()
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 1,
                'public_key' => 'pk_test',
                'secret_key' => 'sk_test',
            ]);

        $gateway = PaymentGateway::find(20);
        $info = json_decode($gateway->information, true);

        $this->assertArrayHasKey('text', $info);
        $this->assertEquals('Pay via Moneroo - Multiple payment options across Africa.', $info['text']);
    }

    /** @test */
    public function moneroo_update_updates_env_file()
    {
        // Create a temporary .env file for testing
        $envPath = base_path('.env.testing');
        file_put_contents($envPath, "APP_NAME=TestApp\nAPP_ENV=testing\n");

        // Temporarily override the base_path for testing
        $this->app->useEnvironmentPath(dirname($envPath));
        $this->app->loadEnvironmentFrom(basename($envPath));

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 1,
                'public_key' => 'pk_test_key',
                'secret_key' => 'sk_test_secret',
            ]);

        // Check if env file was updated
        $envContent = file_get_contents($envPath);
        $this->assertStringContainsString('MONEROO_PUBLIC_KEY=pk_test_key', $envContent);
        $this->assertStringContainsString('MONEROO_SECRET_KEY=sk_test_secret', $envContent);

        // Cleanup
        if (file_exists($envPath)) {
            unlink($envPath);
        }
    }

    /** @test */
    public function unauthorized_users_cannot_update_moneroo_settings()
    {
        $response = $this->post(route('admin.moneroo.update'), [
            'status' => 1,
            'public_key' => 'pk_test',
            'secret_key' => 'sk_test',
        ]);

        // Should redirect to login
        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function moneroo_gateway_appears_in_gateway_list()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.gateway.index'));

        $response->assertOk();
        $response->assertSee('Moneroo');
        $response->assertSee('Moneroo Public Key');
        $response->assertSee('Moneroo Secret Key');
    }

    /** @test */
    public function admin_can_activate_moneroo_gateway()
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 1,
                'public_key' => 'pk_live_key',
                'secret_key' => 'sk_live_secret',
            ]);

        $gateway = PaymentGateway::find(20);
        $this->assertEquals(1, $gateway->status);
    }

    /** @test */
    public function admin_can_deactivate_moneroo_gateway()
    {
        // First activate
        $this->gateway->status = 1;
        $this->gateway->save();

        // Then deactivate
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 0,
                'public_key' => 'pk_test_key',
                'secret_key' => 'sk_test_secret',
            ]);

        $gateway = PaymentGateway::find(20);
        $this->assertEquals(0, $gateway->status);
    }

    /** @test */
    public function moneroo_credentials_can_be_updated_multiple_times()
    {
        // First update
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 1,
                'public_key' => 'pk_test_old',
                'secret_key' => 'sk_test_old',
            ]);

        // Second update
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 1,
                'public_key' => 'pk_test_new',
                'secret_key' => 'sk_test_new',
            ]);

        $gateway = PaymentGateway::find(20);
        $info = json_decode($gateway->information, true);

        $this->assertEquals('pk_test_new', $info['public_key']);
        $this->assertEquals('sk_test_new', $info['secret_key']);
    }

    /** @test */
    public function empty_credentials_are_saved_correctly()
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.moneroo.update'), [
                'status' => 0,
                'public_key' => '',
                'secret_key' => '',
            ]);

        $gateway = PaymentGateway::find(20);
        $info = json_decode($gateway->information, true);

        $this->assertEquals('', $info['public_key']);
        $this->assertEquals('', $info['secret_key']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
