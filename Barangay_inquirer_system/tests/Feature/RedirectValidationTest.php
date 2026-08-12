<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;

class RedirectValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid redirects pass through unmolested
     */
    public function test_valid_redirects_pass_through(): void
    {
        $response = $this->get('/login');
        
        // Redirect to login should be allowed
        $this->assertEquals(200, $response->status());
    }

    /**
     * Test that post-login redirect to admin dashboard works
     */
    public function test_admin_login_redirect(): void
    {
        $admin = User::factory(['role' => 'admin'])->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        // Should redirect to admin dashboard
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test that post-login redirect to resident dashboard works
     */
    public function test_resident_login_redirect(): void
    {
        $resident = User::factory(['role' => 'resident'])->create([
            'email' => 'resident@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'resident@test.com',
            'password' => 'password',
        ]);

        // Should redirect to resident dashboard
        $response->assertRedirect(route('resident.dashboard'));
    }

    /**
     * Test that post-login redirect to super admin dashboard works
     */
    public function test_superadmin_login_redirect(): void
    {
        $superadmin = User::factory(['role' => 'super_admin'])->create([
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'superadmin@test.com',
            'password' => 'password',
        ]);

        // Should redirect to superadmin dashboard
        $response->assertRedirect(route('superadmin.dashboard'));
    }

    /**
     * Test that logout redirect works
     */
    public function test_logout_redirect(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * Test that password reset redirect works
     */
    public function test_password_reset_redirect(): void
    {
        $user = User::factory()->create();

        // Request password reset
        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        // Should redirect back to password request form
        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    /**
     * Test that malicious redirect URLs are blocked
     *
     * Note: This tests the middleware logic by attempting to manipulate
     * redirect behavior. The middleware should catch invalid URLs.
     */
    public function test_open_redirect_vulnerability_blocked(): void
    {
        $user = User::factory(['role' => 'resident'])->create();

        // Attempting to redirect to external site should fail
        // (Note: This would require a controller that takes redirect input,
        // which we don't have in this application by design)
        
        // Testing that our validator blocks external URLs
        $validator = app('redirect.validator');
        $this->assertFalse($validator->isValidRedirectUrl('https://evil.com'));
        $this->assertFalse($validator->isValidRedirectUrl('//phishing.com'));
    }

    /**
     * Test that JavaScript protocol URLs are blocked
     */
    public function test_javascript_protocol_blocked(): void
    {
        $validator = app('redirect.validator');
        
        $this->assertFalse($validator->isValidRedirectUrl('javascript:alert("xss")'));
        $this->assertFalse($validator->isValidRedirectUrl('JavaScript:void(0)'));
    }

    /**
     * Test that data URLs are blocked
     */
    public function test_data_urls_blocked(): void
    {
        $validator = app('redirect.validator');
        
        $this->assertFalse($validator->isValidRedirectUrl('data:text/html,<script>alert(1)</script>'));
    }

    /**
     * Test that internal routes are allowed
     */
    public function test_internal_routes_allowed(): void
    {
        $validator = app('redirect.validator');
        
        $this->assertTrue($validator->isValidRedirectUrl('/admin/dashboard'));
        $this->assertTrue($validator->isValidRedirectUrl('/resident/dashboard'));
        $this->assertTrue($validator->isValidRedirectUrl('/superadmin/dashboard'));
        $this->assertTrue($validator->isValidRedirectUrl('/login'));
    }

    /**
     * Test that safe redirect helper works with valid URLs
     */
    public function test_safe_redirect_helper_with_valid_url(): void
    {
        $url = safe_redirect_route('admin.dashboard');
        
        $this->assertInstanceOf(RedirectResponse::class, $url);
    }

    /**
     * Test helper function blocks invalid URLs
     */
    public function test_safe_redirect_function_validates_urls(): void
    {
        $isValid = is_safe_redirect_url('/admin/dashboard');
        $this->assertTrue($isValid);
        
        $isValid = is_safe_redirect_url('https://evil.com');
        $this->assertFalse($isValid);
    }

    /**
     * Test get_safe_redirect_url helper returns valid URLs
     */
    public function test_get_safe_redirect_url_helper(): void
    {
        $safe = get_safe_redirect_url('/admin/dashboard', '/fallback');
        $this->assertEquals('/admin/dashboard', $safe);
        
        $safe = get_safe_redirect_url('https://evil.com', '/fallback');
        $this->assertEquals('/fallback', $safe);
    }

    /**
     * Test protocol relative URLs are blocked
     */
    public function test_protocol_relative_urls_blocked(): void
    {
        $validator = app('redirect.validator');
        
        $this->assertFalse($validator->isValidRedirectUrl('//evil.com'));
        $this->assertFalse($validator->isValidRedirectUrl('//attacker.com/admin'));
    }

    /**
     * Test that file protocol is blocked
     */
    public function test_file_protocol_blocked(): void
    {
        $validator = app('redirect.validator');
        
        $this->assertFalse($validator->isValidRedirectUrl('file:///etc/passwd'));
        $this->assertFalse($validator->isValidRedirectUrl('file://C:\\Windows\\System32'));
    }
}
