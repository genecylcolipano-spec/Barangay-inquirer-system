<?php

namespace Tests\Unit;

use App\Services\RedirectUrlValidator;
use Tests\TestCase;

class RedirectUrlValidatorTest extends TestCase
{
    protected RedirectUrlValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RedirectUrlValidator();
    }

    /**
     * Test that internal routes are allowed
     */
    public function test_internal_routes_are_allowed(): void
    {
        $this->assertTrue($this->validator->isValidRedirectUrl('/admin/dashboard'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/resident/dashboard'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/superadmin/dashboard'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/login'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/register'));
    }

    /**
     * Test that external URLs are blocked
     */
    public function test_external_urls_are_blocked(): void
    {
        $this->assertFalse($this->validator->isValidRedirectUrl('https://evil.com'));
        $this->assertFalse($this->validator->isValidRedirectUrl('http://phishing.example.com'));
        $this->assertFalse($this->validator->isValidRedirectUrl('https://attacker.com/admin'));
    }

    /**
     * Test that protocol-relative URLs are blocked
     */
    public function test_protocol_relative_urls_are_blocked(): void
    {
        $this->assertFalse($this->validator->isValidRedirectUrl('//evil.com'));
        $this->assertFalse($this->validator->isValidRedirectUrl('//attacker.com/path'));
    }

    /**
     * Test that dangerous protocols are blocked
     */
    public function test_dangerous_protocols_are_blocked(): void
    {
        $this->assertFalse($this->validator->isValidRedirectUrl('javascript:alert("xss")'));
        $this->assertFalse($this->validator->isValidRedirectUrl('data:text/html,<script>alert(1)</script>'));
        $this->assertFalse($this->validator->isValidRedirectUrl('vbscript:alert("xss")'));
        $this->assertFalse($this->validator->isValidRedirectUrl('file:///etc/passwd'));
    }

    /**
     * Test that relative URLs with query strings are allowed
     */
    public function test_relative_urls_with_query_strings_are_allowed(): void
    {
        $this->assertTrue($this->validator->isValidRedirectUrl('/admin/dashboard?tab=users'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/resident/requests?status=pending'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/superadmin/users?page=2&sort=name'));
    }

    /**
     * Test that relative URLs with fragments are allowed
     */
    public function test_relative_urls_with_fragments_are_allowed(): void
    {
        $this->assertTrue($this->validator->isValidRedirectUrl('/admin/dashboard#section1'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/resident/profile#bio'));
    }

    /**
     * Test that empty URLs are rejected
     */
    public function test_empty_urls_are_rejected(): void
    {
        $this->assertFalse($this->validator->isValidRedirectUrl(''));
        $this->assertFalse($this->validator->isValidRedirectUrl('   '));
    }

    /**
     * Test that allowed routes can be retrieved
     */
    public function test_get_allowed_routes(): void
    {
        $routes = $this->validator->getAllowedRoutes();
        
        $this->assertIsArray($routes);
        $this->assertContains('admin.dashboard', $routes);
        $this->assertContains('resident.dashboard', $routes);
        $this->assertContains('superadmin.dashboard', $routes);
        $this->assertContains('home', $routes);
    }

    /**
     * Test that routes can be added to allowlist
     */
    public function test_add_routes_to_allowlist(): void
    {
        $initialCount = count($this->validator->getAllowedRoutes());
        
        $this->validator->addAllowedRoutes('custom.route');
        
        $this->assertCount($initialCount + 1, $this->validator->getAllowedRoutes());
        $this->assertContains('custom.route', $this->validator->getAllowedRoutes());
    }

    /**
     * Test that multiple routes can be added at once
     */
    public function test_add_multiple_routes_to_allowlist(): void
    {
        $initialCount = count($this->validator->getAllowedRoutes());
        
        $this->validator->addAllowedRoutes(['custom.route1', 'custom.route2']);
        
        $this->assertGreaterThan($initialCount, count($this->validator->getAllowedRoutes()));
        $this->assertContains('custom.route1', $this->validator->getAllowedRoutes());
        $this->assertContains('custom.route2', $this->validator->getAllowedRoutes());
    }

    /**
     * Test getSafeRedirectUrl with valid URL
     */
    public function test_get_safe_redirect_url_with_valid_url(): void
    {
        $result = $this->validator->getSafeRedirectUrl('/admin/dashboard', '/home');
        
        $this->assertEquals('/admin/dashboard', $result);
    }

    /**
     * Test getSafeRedirectUrl with invalid URL
     */
    public function test_get_safe_redirect_url_with_invalid_url(): void
    {
        $result = $this->validator->getSafeRedirectUrl('https://evil.com', '/home');
        
        $this->assertEquals('/home', $result);
    }

    /**
     * Test that case-insensitive protocol checking works
     */
    public function test_case_insensitive_dangerous_protocols(): void
    {
        $this->assertFalse($this->validator->isValidRedirectUrl('JAVASCRIPT:alert("xss")'));
        $this->assertFalse($this->validator->isValidRedirectUrl('JavaScript:alert("xss")'));
        $this->assertFalse($this->validator->isValidRedirectUrl('DATA:text/html,<script>'));
    }

    /**
     * Test that trailing whitespace is handled
     */
    public function test_whitespace_trimming(): void
    {
        // Valid URL with spaces should be accepted (trimmed by getSafeRedirectUrl)
        $result = $this->validator->getSafeRedirectUrl('   /admin/dashboard   ', '/home');
        // The validator trims input internally, so this should pass validation
        $this->assertTrue($this->validator->isValidRedirectUrl('   /admin/dashboard   '));
    }

    /**
     * Test common attack vectors
     */
    public function test_common_attack_vectors(): void
    {
        // Open redirect attempts with query parameters should still be valid
        // because the path itself (/admin/dashboard) is valid
        $this->assertTrue($this->validator->isValidRedirectUrl('/admin/dashboard?redirect=https://evil.com'));
        $this->assertTrue($this->validator->isValidRedirectUrl('/admin/dashboard?url=javascript:alert(1)'));
        
        // But protocol-relative and fully external URLs are blocked
        $this->assertFalse($this->validator->isValidRedirectUrl('//evil.com?redirect=true'));
        $this->assertFalse($this->validator->isValidRedirectUrl('https://attacker.com/admin'));
    }
}
