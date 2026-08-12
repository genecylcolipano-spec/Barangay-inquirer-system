<?php

namespace App\Providers;

use App\Models\DocumentRequest;
use App\Policies\DocumentRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DocumentRequest::class => DocumentRequestPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        // Define custom authorization gates if needed
        // Gate::define('download-file', function ($user, $file) {
        //     return $user->id === $file->user_id || in_array($user->role, ['admin', 'super_admin']);
        // });
    }
}
