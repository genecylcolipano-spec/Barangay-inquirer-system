<?php

namespace Tests\Feature;

use App\Services\LoginAttemptService;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test suite for login attempt limiting functionality
 * 
 * Tests the brute force protection mechanism that limits failed login attempts
 * to 5 within 1 minute, then locks the account for 1 minute.
 */
class LoginAttemptLimitingTest extends TestCase
{
    /**
     * Clear cache before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test that failed attempts are tracked correctly
     */
    public function test_failed_login_attempts_are_recorded()
    {
        $email = 'testuser@example.com';
        $service = app(LoginAttemptService::class);

        for ($i = 1; $i <= 3; $i++) {
            $attempts = $service->recordFailedAttempt($email);
            $this->assertEquals($i, $attempts);
        }

        $this->assertEquals(3, $service->getAttempts($email));
    }

    /**
     * Test that account is locked after 5 failed attempts
     */
    public function test_account_locked_after_five_failed_attempts()
    {
        $email = 'testuser@example.com';
        $service = app(LoginAttemptService::class);

        // Record 5 failed attempts
        for ($i = 1; $i <= 5; $i++) {
            $service->recordFailedAttempt($email);
        }

        // Verify account is locked
        $this->assertTrue($service->isLockedOut($email));
    }

    /**
     * Test that login fails when account is locked out
     */
    public function test_login_blocked_when_account_is_locked()
    {
        $email = 'testuser@example.com';
        $password = 'password123';

        // Create a test user
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        // Lock the account
        $service = app(LoginAttemptService::class);
        for ($i = 1; $i <= 5; $i++) {
            $service->recordFailedAttempt($email);
        }

        // Try to login - should be blocked at middleware
        $response = $this->postJson('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // Should be redirected with lockout message
        $response->assertRedirect();
        $response->assertSessionHas('lockout', true);
    }

    /**
     * Test that successful login resets attempts
     */
    public function test_successful_login_resets_attempts()
    {
        $email = 'testuser@example.com';
        $password = 'password123';

        // Create test user
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        // Record some failed attempts
        $service = app(LoginAttemptService::class);
        for ($i = 1; $i <= 3; $i++) {
            $service->recordFailedAttempt($email);
        }

        // Verify attempts were recorded
        $this->assertEquals(3, $service->getAttempts($email));

        // Successful login resets attempts
        $service->resetAttempts($email);

        // Verify attempts are reset
        $this->assertEquals(0, $service->getAttempts($email));
        $this->assertFalse($service->isLockedOut($email));
    }

    /**
     * Test that different emails have separate attempt counters
     */
    public function test_different_emails_have_separate_attempt_counters()
    {
        $email1 = 'user1@example.com';
        $email2 = 'user2@example.com';
        $service = app(LoginAttemptService::class);

        // Record attempts for email1
        for ($i = 1; $i <= 4; $i++) {
            $service->recordFailedAttempt($email1);
        }

        // Lock email2
        for ($i = 1; $i <= 5; $i++) {
            $service->recordFailedAttempt($email2);
        }

        // Verify separate counters
        $this->assertEquals(4, $service->getAttempts($email1));
        $this->assertTrue($service->isLockedOut($email2));
        $this->assertFalse($service->isLockedOut($email1));
    }

    /**
     * Test that lockout status can be retrieved
     */
    public function test_can_check_lockout_status()
    {
        $email = 'testuser@example.com';
        $service = app(LoginAttemptService::class);

        // Initially not locked
        $this->assertFalse($service->isLockedOut($email));

        // Lock the account
        for ($i = 1; $i <= 5; $i++) {
            $service->recordFailedAttempt($email);
        }

        // Now locked
        $this->assertTrue($service->isLockedOut($email));
    }

    /**
     * Test that remaining minutes can be retrieved
     */
    public function test_can_get_minutes_until_available()
    {
        $email = 'testuser@example.com';
        $service = app(LoginAttemptService::class);

        // Lock the account
        for ($i = 1; $i <= 5; $i++) {
            $service->recordFailedAttempt($email);
        }

        // Get minutes remaining
        $minutes = $service->getMinutesUntilAvailable($email);
        $this->assertGreaterThan(0, $minutes);
        $this->assertLessThanOrEqual(1, $minutes);
    }

    /**
     * Test that attempting login shows remaining attempts
     */
    public function test_login_failure_shows_remaining_attempts()
    {
        $email = 'testuser@example.com';
        $password = 'wrongpassword';

        // Create test user
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('correctpassword'),
        ]);

        // Attempt 1
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertSessionHas('remaining_attempts', 4);

        // Attempt 2
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertSessionHas('remaining_attempts', 3);
    }

    /**
     * Test that reset clears lockout flag
     */
    public function test_reset_attempts_clears_lockout_flag()
    {
        $email = 'testuser@example.com';
        $service = app(LoginAttemptService::class);

        // Lock account
        for ($i = 1; $i <= 5; $i++) {
            $service->recordFailedAttempt($email);
        }

        $this->assertTrue($service->isLockedOut($email));

        // Reset
        $service->resetAttempts($email);

        $this->assertFalse($service->isLockedOut($email));
    }

    /**
     * Test edge case: attempt count doesn't exceed max
     */
    public function test_attempt_count_continues_after_lockout()
    {
        $email = 'testuser@example.com';
        $service = app(LoginAttemptService::class);

        // Record 5 attempts
        for ($i = 1; $i <= 5; $i++) {
            $service->recordFailedAttempt($email);
        }

        // Record 6th attempt (while locked)
        $service->recordFailedAttempt($email);

        // Should still show as locked
        $this->assertTrue($service->isLockedOut($email));
    }
}
