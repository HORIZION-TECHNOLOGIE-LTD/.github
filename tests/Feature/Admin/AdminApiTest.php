<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin\Admin;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminApiTest extends TestCase
{
    /**
     * Admin user instance for testing
     *
     * @var \App\Models\Admin\Admin
     */
    protected $admin;

    /**
     * Set up the test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user for all tests
        $this->admin = Admin::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * Test stats endpoint returns correct structure
     *
     * @return void
     */
    public function test_stats_endpoint_returns_json()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/admin/api/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'todayAmount',
                'orders',
                'newUsers',
                'refunds'
            ]);
    }

    /**
     * Test transactions endpoint returns array
     *
     * @return void
     */
    public function test_transactions_endpoint_returns_array()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/admin/api/transactions');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    /**
     * Test users endpoint returns array
     *
     * @return void
     */
    public function test_users_endpoint_returns_array()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/admin/api/users');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    /**
     * Test settings endpoint returns correct structure
     *
     * @return void
     */
    public function test_settings_endpoint_returns_json()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson('/admin/api/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'siteName',
                'callbackUrl'
            ]);
    }

    /**
     * Test settings update endpoint
     *
     * @return void
     */
    public function test_settings_update_endpoint()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/api/settings', [
                'siteName' => 'Test Site',
                'callbackUrl' => 'https://test.com/callback'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);
    }

    /**
     * Test unauthorized access to stats endpoint
     *
     * @return void
     */
    public function test_unauthorized_access_to_stats_endpoint()
    {
        $response = $this->getJson('/admin/api/stats');

        // Should redirect to login or return 401/403
        $this->assertTrue(
            $response->status() === 401 || 
            $response->status() === 403 || 
            $response->status() === 302
        );
    }
}
