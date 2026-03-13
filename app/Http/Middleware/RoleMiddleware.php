<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->role !== $role) {
            Log::warning('Role access denied', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_role' => $role,
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            abort(403, 'Insufficient permissions.');
        }

        return $next($request);
    }
}

