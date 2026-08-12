<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that forgot password form is accessible
     */
    public function test_forgot_password_form_is_accessible(): void
    {
        $response = $this->get('/password/reset');

        $response->assertStatus(200);
        $response->assertSee('Forgot Password');
    }

    /**
     * Test that password reset token table exists
     */
    public function test_password_reset_tokens_table_exists(): void
    {
        $this->assertTrue(
            DB::connection()->getSchemaBuilder()->hasTable('password_reset_tokens'),
            'password_reset_tokens table does not exist'
        );
    }

    /**
     * Test that password reset email can be sent
     */
    public function test_password_reset_email_can_be_sent(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // First, get the form page to establish a session
        $this->get('/password/reset');

        $response = $this->post('/password/email', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        // Verify a token was created in the database
        $this->assertTrue(
            DB::table('password_reset_tokens')->where('email', 'test@example.com')->exists(),
            'Password reset token was not created in database'
        );
    }

    /**
     * Test that password can be reset with valid token
     */
    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword')
        ]);

        // Generate a password reset token
        $token = Password::createToken($user);

        // Verify token exists in database
        $this->assertTrue(
            DB::table('password_reset_tokens')->where('email', 'test@example.com')->exists()
        );

        // First get the reset form to establish session and CSRF token
        $this->get("/password/reset/{$token}?email=test@example.com");

        // Submit password reset form
        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        // Verify user's password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        // Verify token was deleted after use
        $this->assertFalse(
            DB::table('password_reset_tokens')->where('email', 'test@example.com')->exists()
        );
    }

    /**
     * Test that password reset fails with invalid token
     */
    public function test_password_reset_fails_with_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // First get the reset form to establish session
        $this->get('/password/reset/invalid_token_12345?email=test@example.com');

        $response = $this->post('/password/reset', [
            'token' => 'invalid_token_12345',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that password reset requires matching confirmation
     */
    public function test_password_reset_requires_matching_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $token = Password::createToken($user);

        // First get the reset form to establish session
        $this->get("/password/reset/{$token}?email=test@example.com");

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
}

