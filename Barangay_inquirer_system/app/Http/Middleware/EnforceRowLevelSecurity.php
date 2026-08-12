<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DocumentRequest;
use App\Models\User;

class EnforceRowLevelSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is accessing another user's data
        if (auth()->check()) {
            $user = auth()->user();

            // Check document request access
            if ($request->route()->getName() && in_array($request->route()->getName(), [
                'resident.request.show',
                'resident.request.download',
                'resident.request.destroy',
                'resident.notification.read',
            ])) {
                // Get the resource ID from route parameters
                if ($request->route('request')) {
                    $documentRequest = $request->route('request');
                    
                    // Non-admin/super_admin users can only access their own requests
                    if (!in_array($user->role, ['admin', 'super_admin']) && $documentRequest->user_id !== $user->id) {
                        abort(403, 'You do not have access to this resource.');
                    }
                }
            }

            // Check profile access
            if ($request->route()->getName() && in_array($request->route()->getName(), [
                'resident.profile.edit',
                'resident.profile.update',
                'resident.settings',
                'resident.settings.profile',
                'resident.settings.password',
                'resident.settings.photo',
            ])) {
                // Residents can only edit their own profile
                if ($user->role === 'resident') {
                    // Allowed - residents access their own profile through their authenticated session
                }
            }
        }

        return $next($request);
    }
}
