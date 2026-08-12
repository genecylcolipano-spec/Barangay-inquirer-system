<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        try {
            return view('auth.passwords.email');
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in ForgotPasswordController@showLinkRequestForm', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('auth.passwords.email', ['error' => $message]);
        }
    }

    public function sendResetLinkEmail(Request $request)
    {
        try {
            // Validate email
            $validated = $request->validate(['email' => 'required|email']);

            // Log the password reset request attempt
            \Log::info('Password reset link request', [
                'email' => $validated['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('req_'),
            ]);

            // Attempt to send reset link
            $status = Password::sendResetLink($request->only('email'));

            // Check if email sending was successful
            if ($status === Password::RESET_LINK_SENT) {
                // Log successful password reset link sent
                \Log::info('Password reset link sent successfully', [
                    'email' => $validated['email'],
                    'timestamp' => now()->toIso8601String(),
                ]);

                // Return JSON response for async request or redirect for traditional request
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Password reset link sent to your email. Check your inbox and spam folder.',
                    ], 200);
                }

                return back()->with('status', 'If an account with that email address exists, we have sent a password reset link.');
            }

            // Log when email is not found (without leaking info to user)
            \Log::warning('Password reset request for non-existent email', [
                'email' => $validated['email'],
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            // Always show the same message for security (don't leak if email exists)
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'If an account with that email address exists, we have sent a password reset link.',
                ], 200);
            }

            return back()->with('status', 'If an account with that email address exists, we have sent a password reset link.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error - log with full context
            \Log::warning('Password reset validation failed', [
                'errors' => $e->errors(),
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            // Return generic error to user
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Request format is invalid.',
                    'errors' => ['email' => ['Please enter a valid email address.']],
                ], 422);
            }

            return back()->withErrors($e->errors());

        } catch (\Exception $e) {
            // Catch all other exceptions (mail failures, database errors, etc.)
            \Log::error('Password reset email sending error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('req_'),
            ]);

            // Return generic error to user
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An error occurred. Please try again later.',
                ], 500);
            }

            return back()->with('error', 'An error occurred while sending the password reset link. Please try again later.');
        }
    }

    public function showResetForm(Request $request, $token = null)
    {
        try {
            return view('auth.passwords.reset')->with([
                'token' => $token,
                'email' => $request->email,
            ]);
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in ForgotPasswordController@showResetForm', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('auth.passwords.reset', ['error' => $message, 'token' => $token, 'email' => $request->email]);
        }
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', new StrongPassword()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in with your new password.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}
