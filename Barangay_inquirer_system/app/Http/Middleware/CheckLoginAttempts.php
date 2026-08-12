<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\LoginAttemptService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginAttempts
{
    /**
     * Create a new middleware instance
     */
    public function __construct(private LoginAttemptService $loginAttemptService)
    {
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check on login POST requests
        if ($request->isMethod('post') && $request->filled('email')) {
            $email = $request->input('email');

            // Check if account is locked out
            if ($this->loginAttemptService->isLockedOut($email)) {
                $minutesUntilAvailable = $this->loginAttemptService->getMinutesUntilAvailable($email);
                return back()->withErrors([
                    'email' => 'Too many login attempts. Please try again later.',
                ])->onlyInput('email')->with('lockout', true)->with('minutes', $minutesUntilAvailable);
            }
        }

        return $next($request);
    }
}
