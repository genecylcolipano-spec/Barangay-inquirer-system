<?php

namespace Tests\Feature;

use App\Services\ClerkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClerkAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Clerk login route is accessible
     */
    public function test_clerk_login_route_is_accessible(): void
    {
        $response = $this->get('/clerk/login');

        $response->assertStatus(200);
    }

    /**
     * Test that Clerk service can be instantiated
     */
    public function test_clerk_service_can_be_instantiated(): void
    {
        $clerkService = app(ClerkService::class);

        $this->assertInstanceOf(ClerkService::class, $clerkService);
    }

    /**
     * Test that Clerk service returns null when secret key is not configured
     */
    public function test_clerk_service_returns_null_without_secret_key(): void
    {
        $clerkService = app(ClerkService::class);

        // Test verifyToken without proper configuration
        $result = $clerkService->verifyToken('fake_token');

        $this->assertNull($result);
    }

    /**
     * Test that User model has clerk_id in fillable
     */
    public function test_user_model_has_clerk_id_fillable(): void
    {
        $user = new \App\Models\User();

        $this->assertContains('clerk_id', $user->getFillable());
    }
}
