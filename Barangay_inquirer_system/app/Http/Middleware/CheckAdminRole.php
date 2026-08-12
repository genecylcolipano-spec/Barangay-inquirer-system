<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and has admin role
        if (!auth()->check()) {
            return redirect('login')->with('error', 'Please log in to access admin panel.');
        }

        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized. Only admins can access this section.');
        }

        return $next($request);
    }
}
