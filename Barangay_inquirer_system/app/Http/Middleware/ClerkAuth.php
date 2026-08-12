<?php

namespace App\Http\Middleware;

use App\Services\ClerkService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClerkAuth
{
    protected ClerkService $clerkService;

    public function __construct(ClerkService $clerkService)
    {
        $this->clerkService = $clerkService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Clerk JWT token in Authorization header or session
        $token = $request->bearerToken() ?? $request->session()->get('clerk_token');

        if (!$token) {
            // If no token, redirect to Clerk auth
            return redirect()->route('clerk.login');
        }

        // Verify token with Clerk
        $tokenData = $this->clerkService->verifyToken($token);

        if (!$tokenData || !isset($tokenData['user_id'])) {
            // Token invalid, redirect to Clerk auth
            return redirect()->route('clerk.login');
        }

        // Get user from Clerk
        $clerkUser = $this->clerkService->getUser($tokenData['user_id']);

        if (!$clerkUser) {
            // User not found in Clerk, redirect to auth
            return redirect()->route('clerk.login');
        }

        // Sync user with local database
        $user = $this->clerkService->syncUser($clerkUser);

        if (!$user) {
            // Failed to sync user
            return redirect()->route('clerk.login')->withErrors(['auth' => 'Failed to authenticate user.']);
        }

        // Log user in
        Auth::login($user);

        // Store token in session for subsequent requests
        $request->session()->put('clerk_token', $token);

        return $next($request);
    }
}
