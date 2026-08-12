<?php

namespace App\Http\Controllers;

use App\Services\ClerkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClerkAuthController extends Controller
{
    protected ClerkService $clerkService;

    public function __construct(ClerkService $clerkService)
    {
        $this->clerkService = $clerkService;
    }

    /**
     * Show the Clerk login page
     */
    public function showLogin()
    {
        return view('auth.clerk-login', [
            'clerkPublishableKey' => $this->clerkService->getPublishableKey()
        ]);
    }

    /**
     * Handle Clerk authentication callback
     */
    public function callback(Request $request)
    {
        $token = $request->input('token') ?? $request->bearerToken();

        if (!$token) {
            return redirect()->route('clerk.login')->withErrors(['token' => 'No authentication token provided.']);
        }

        // Verify token with Clerk
        $tokenData = $this->clerkService->verifyToken($token);

        if (!$tokenData || !isset($tokenData['user_id'])) {
            return redirect()->route('clerk.login')->withErrors(['auth' => 'Invalid authentication token.']);
        }

        // Get user from Clerk
        $clerkUser = $this->clerkService->getUser($tokenData['user_id']);

        if (!$clerkUser) {
            return redirect()->route('clerk.login')->withErrors(['auth' => 'User not found.']);
        }

        // Sync user with local database
        $user = $this->clerkService->syncUser($clerkUser);

        if (!$user) {
            return redirect()->route('clerk.login')->withErrors(['auth' => 'Failed to create user account.']);
        }

        // Log user in
        Auth::login($user);

        // Store token in session
        $request->session()->put('clerk_token', $token);

        // Redirect based on user role
        if ($user->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('resident.dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        // Clear Clerk token from session
        $request->session()->forget('clerk_token');

        // Logout from Laravel
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
