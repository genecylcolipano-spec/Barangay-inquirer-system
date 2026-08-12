<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * URIs that should be excluded from maintenance mode checks.
     *
     * @var string[]
     */
    protected $except = [
        'login',
        'logout',
        'password/*',
        'superadmin/*',
    ];

    /**
     * Handle requests during maintenance mode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Allow super_admin users to access the site during maintenance mode
        if (auth()->check() && auth()->user()->role === 'super_admin') {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}