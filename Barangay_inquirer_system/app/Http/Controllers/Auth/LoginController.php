<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        try {
            return view('auth.login');
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in LoginController@showLoginForm', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('auth.login', ['error' => $message]);
        }
    }

    // Handle login
    public function login(Request $request, LoginAttemptService $loginAttemptService)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // Reset login attempts on successful authentication
            $loginAttemptService->resetAttempts($credentials['email']);
            
            $request->session()->regenerate();

            $user = Auth::user();

            // 🔑 ROLE-BASED REDIRECTION
            if ($user->role === 'super_admin') {
                return redirect()->route('superadmin.dashboard');
            }

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            // default → resident
            return redirect()->route('resident.dashboard');
        }

        // Record failed login attempt
        $loginAttemptService->recordFailedAttempt($credentials['email']);
        $attempts = $loginAttemptService->getAttempts($credentials['email']);
        $remainingAttempts = max(0, 5 - $attempts);

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email')->with('remaining_attempts', $remainingAttempts);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
