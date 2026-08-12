<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which is
| assigned the "api" middleware group.
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    /**
     * Issue JWT and refresh tokens
     * POST /api/auth/token
     * Requires: Clerk authentication or web session
     */
    Route::post('/token', [AuthController::class, 'issueToken'])
        ->middleware(['auth:web'])
        ->name('api.auth.token')
        ->withoutMiddleware('web');

    /**
     * Refresh access token
     * POST /api/auth/refresh
     * Body: {"refresh_token": "..."}
     * Public endpoint - refresh token is proof of identity
     */
    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->name('api.auth.refresh')
        ->withoutMiddleware('web');
});

// Protected API routes (require valid JWT)
Route::middleware(['jwt'])->group(function () {
    Route::prefix('auth')->group(function () {
        /**
         * Logout from current device
         * POST /api/auth/logout
         * Body: {"refresh_token": "..."}
         */
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('api.auth.logout');

        /**
         * Logout from all devices
         * POST /api/auth/logout-all
         */
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])
            ->name('api.auth.logout-all');

        /**
         * Get active sessions
         * GET /api/auth/sessions
         */
        Route::get('/sessions', [AuthController::class, 'getSessions'])
            ->name('api.auth.sessions');

        /**
         * Revoke specific session
         * DELETE /api/auth/sessions/{sessionId}
         */
        Route::delete('/sessions/{sessionId}', [AuthController::class, 'revokeSession'])
            ->name('api.auth.revoke-session');
    });

    // Example protected endpoint
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'jwt_payload' => $request->attributes->get('jwt_payload'),
        ]);
    })->name('api.user');
});

// Resident announcements API
use App\Http\Controllers\Api\Resident\AnnouncementApiController;

Route::middleware(['throttle_public'])->group(function () {
    Route::get('/resident/announcements', [AnnouncementApiController::class, 'index'])
        ->name('api.resident.announcements');

    Route::get('/resident/announcements/{announcement}', [AnnouncementApiController::class, 'show'])
        ->name('api.resident.announcements.show');
});

// Healthcheck endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health');
