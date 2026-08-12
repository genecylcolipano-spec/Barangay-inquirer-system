<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PasswordResetRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected string $email = 'test@example.com';
    protected string $resetRoute = '/password/email';

    protected function setUp(): void
    {
        parent::setUp();
        // Clear rate limit cache before each test
        Cache::flush();
    }

    /**
     * Test that first password reset request is allowed
     */
    public function test_first_password_reset_request_allowed(): void
    {
        $response = $this->post($this->resetRoute, ['email' => $this->email]);

        // Should not return 429 (Too Many Requests)
        $this->assertNotEquals(429, $response->status());
    }

    /**
     * Test that up to 3 password reset requests per email are allowed
     */
    public function test_three_password_reset_requests_allowed(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->post($this->resetRoute, ['email' => $this->email]);
            $this->assertNotEquals(429, $response->status(), "Request #{$i} should be allowed");
            $this->assertEquals(3 - $i, (int)$response->header('X-RateLimit-Remaining'));
        }
    }

    /**
     * Test that 4th password reset request within hour is blocked
     */
    public function test_fourth_password_reset_request_blocked(): void
    {
        // Make 3 successful requests
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $this->email]);
        }

        // 4th request should be blocked
        $response = $this->post($this->resetRoute, ['email' => $this->email]);

        $this->assertEquals(429, $response->status());
        $this->assertJson($response->content());
        $this->assertStringContainsString('Too many password reset requests', $response->json('message'));
    }

    /**
     * Test that rate limit is applied per email (case-insensitive)
     */
    public function test_rate_limit_applied_per_email(): void
    {
        $email1 = 'user1@example.com';
        $email2 = 'user2@example.com';

        // Make 3 requests with email1
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $email1]);
        }

        // 4th request with email1 should fail
        $response = $this->post($this->resetRoute, ['email' => $email1]);
        $this->assertEquals(429, $response->status());

        // But email2 should still be allowed (3 attempts remaining)
        $response = $this->post($this->resetRoute, ['email' => $email2]);
        $this->assertNotEquals(429, $response->status());
    }

    /**
     * Test that email addresses are case-insensitive for rate limiting
     */
    public function test_email_rate_limit_case_insensitive(): void
    {
        $email = 'Test@Example.COM';

        // Make 3 requests with mixed case
        for ($i = 1; $i <= 3; $i++) {
            $caseVariant = $i === 1 ? $email : strtolower($email);
            $this->post($this->resetRoute, ['email' => $caseVariant]);
        }

        // 4th request should be blocked even with different case
        $response = $this->post($this->resetRoute, ['email' => strtoupper($email)]);
        $this->assertEquals(429, $response->status());
    }

    /**
     * Test rate limit headers are included in response
     */
    public function test_rate_limit_headers_in_response(): void
    {
        $response = $this->post($this->resetRoute, ['email' => $this->email]);

        // Should include rate limit headers
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
        $this->assertTrue($response->headers->has('X-RateLimit-Reset'));

        $this->assertEquals(3, $response->header('X-RateLimit-Limit'));
        $this->assertEquals(2, $response->header('X-RateLimit-Remaining'));
    }

    /**
     * Test Retry-After header is included when rate limited
     */
    public function test_retry_after_header_when_limited(): void
    {
        // Exhaust rate limit
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $this->email]);
        }

        $response = $this->post($this->resetRoute, ['email' => $this->email]);

        $this->assertTrue($response->headers->has('Retry-After'));
        $this->assertGreaterThan(0, (int)$response->header('Retry-After'));
    }

    /**
     * Test response includes retry-after information in JSON
     */
    public function test_response_includes_retry_information(): void
    {
        // Exhaust rate limit
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $this->email]);
        }

        $response = $this->post($this->resetRoute, ['email' => $this->email]);

        $this->assertEquals(429, $response->status());
        $this->assertJson($response->content());
        $data = $response->json();

        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('retry_after', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertGreaterThan(0, $data['retry_after']);
    }

    /**
     * Test missing email returns 422
     */
    public function test_missing_email_returns_422(): void
    {
        $response = $this->post($this->resetRoute, []);

        $this->assertEquals(422, $response->status());
        $this->assertStringContainsString('Email address is required', $response->json('message'));
    }

    /**
     * Test invalid email format returns 422
     */
    public function test_invalid_email_returns_422(): void
    {
        $response = $this->post($this->resetRoute, ['email' => 'not-an-email']);

        $this->assertEquals(422, $response->status());
    }

    /**
     * Test rate limit logging for violations
     */
    public function test_rate_limit_violation_is_logged(): void
    {
        Log::shouldReceive('warning')
            ->withArgs(function ($message, $context) {
                return $message === 'Password reset rate limit exceeded' &&
                       isset($context['email']) &&
                       isset($context['ip']) &&
                       isset($context['retry_after']);
            })
            ->once();

        // Exhaust limit
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $this->email]);
        }

        // Trigger violation
        $this->post($this->resetRoute, ['email' => $this->email]);
    }

    /**
     * Test rate limit request tracking is logged
     */
    public function test_rate_limit_requests_are_logged(): void
    {
        Log::shouldReceive('info')
            ->withArgs(function ($message, $context) {
                return $message === 'Password reset request' &&
                       isset($context['email']) &&
                       isset($context['ip']) &&
                       isset($context['attempt']) &&
                       isset($context['max_attempts']);
            })
            ->times(1);

        $this->post($this->resetRoute, ['email' => $this->email]);
    }

    /**
     * Test multiple different email addresses are tracked separately
     */
    public function test_multiple_emails_tracked_separately(): void
    {
        $emails = [
            'user1@example.com',
            'user2@example.com',
            'user3@example.com',
        ];

        // Make 2 requests each with 3 different emails
        foreach ($emails as $email) {
            for ($i = 0; $i < 2; $i++) {
                $response = $this->post($this->resetRoute, ['email' => $email]);
                $requestNumber = $i + 1;
                $this->assertNotEquals(429, $response->status(), "Email {$email}, request {$requestNumber} should succeed");
            }
        }

        // All emails should still have 1 request remaining (3 - 2 = 1)
        foreach ($emails as $email) {
            $response = $this->post($this->resetRoute, ['email' => $email]);
            $this->assertEquals(1, (int)$response->header('X-RateLimit-Remaining'));
        }
    }

    /**
     * Test rate limit response structure
     */
    public function test_rate_limit_response_structure(): void
    {
        // Exhaust limit
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $this->email]);
        }

        $response = $this->post($this->resetRoute, ['email' => $this->email]);

        $this->assertEquals(429, $response->status());
        $this->assertJsonStructure([
            'message',
            'email',
            'retry_after',
        ], $response->json());
    }

    /**
     * Test that response contains helpful error message with retry time
     */
    public function test_error_message_contains_retry_time(): void
    {
        // Exhaust limit
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $this->email]);
        }

        $response = $this->post($this->resetRoute, ['email' => $this->email]);
        $message = $response->json('message');

        $this->assertStringContainsString('minute', $message);
        $this->assertMatchesRegularExpression('/\d+/', $message);
    }

    /**
     * Test that rate limit resets after the hour window
     */
    public function test_rate_limit_resets_after_hour(): void
    {
        // Make 3 requests
        for ($i = 0; $i < 3; $i++) {
            $this->post($this->resetRoute, ['email' => $this->email]);
        }

        // Should be blocked
        $response = $this->post($this->resetRoute, ['email' => $this->email]);
        $this->assertEquals(429, $response->status());

        // Move time forward by 61 minutes
        $this->travelTo(now()->addMinutes(61));

        // Should be allowed again
        $response = $this->post($this->resetRoute, ['email' => $this->email]);
        $this->assertNotEquals(429, $response->status());
    }

    /**
     * Test that X-RateLimit headers are consistent
     */
    public function test_rate_limit_headers_consistent(): void
    {
        $response1 = $this->post($this->resetRoute, ['email' => $this->email]);
        $response2 = $this->post($this->resetRoute, ['email' => $this->email]);

        // Limit should be constant
        $this->assertEquals(
            $response1->header('X-RateLimit-Limit'),
            $response2->header('X-RateLimit-Limit')
        );

        // Remaining should decrease
        $this->assertEquals(
            (int)$response1->header('X-RateLimit-Remaining') - 1,
            (int)$response2->header('X-RateLimit-Remaining')
        );
    }

    /**
     * Test empty email string is rejected
     */
    public function test_empty_email_is_rejected(): void
    {
        $response = $this->post($this->resetRoute, ['email' => '']);

        $this->assertEquals(422, $response->status());
    }

    /**
     * Test whitespace email is rejected
     */
    public function test_whitespace_email_is_rejected(): void
    {
        $response = $this->post($this->resetRoute, ['email' => '   ']);

        $this->assertEquals(422, $response->status());
    }

    /**
     * Test email with special characters but invalid format is rejected
     */
    public function test_invalid_email_format_rejected(): void
    {
        $invalidEmails = [
            'user@',
            '@example.com',
            'user@.com',
            'user..name@example.com',
            'user name@example.com',
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->post($this->resetRoute, ['email' => $email]);
            $this->assertEquals(422, $response->status(), "Email '{$email}' should be rejected");
        }
    }
}
