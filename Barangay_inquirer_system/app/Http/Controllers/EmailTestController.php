<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class EmailTestController extends Controller
{
    /**
     * Show email test form
     */
    public function show()
    {
        return view('email-test');
    }

    /**
     * Send a test email
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            Mail::raw('This is a test email from Barangay Inquirer System. If you received this, email is working correctly!', function($msg) {
                $msg->to($request->email)
                    ->subject('Test Email - Barangay Inquirer System');
            });

            return back()->with('success', 'Test email sent successfully to ' . $request->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Send a password reset email
     */
    public function sendPasswordResetEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Get or create a test user
            $user = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name' => 'Test User',
                    'password' => bcrypt('password'),
                    'role' => 'resident'
                ]
            );

            // Generate password reset token
            $token = Password::createToken($user);

            // Send notification
            $user->notify(new \App\Notifications\ResetPasswordNotification($token));

            return back()->with('success', 'Password reset email sent successfully to ' . $request->email . '. Check spam if not in inbox.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send password reset email: ' . $e->getMessage());
        }
    }

    /**
     * Check email configuration
     */
    public function checkConfiguration()
    {
        $config = [
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', 'tls'),
            'MAIL_USERNAME' => env('MAIL_USERNAME') ? '***hidden***' : 'not set',
            'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '***hidden***' : 'not set',
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
        ];

        return view('email-config', ['config' => $config]);
    }
}
