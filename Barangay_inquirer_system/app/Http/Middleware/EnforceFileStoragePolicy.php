<?php

namespace App\Http\Middleware;

use App\Services\FileStoragePolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce file storage access policies
 * Ensures users can only download/access files they own
 */
class EnforceFileStoragePolicy
{
    protected FileStoragePolicy $filePolicy;

    public function __construct(FileStoragePolicy $filePolicy)
    {
        $this->filePolicy = $filePolicy;
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process file download/access requests
        if ($this->isFileAccessRequest($request)) {
            if (!$this->authorizeFileAccess($request)) {
                // Log unauthorized attempt
                \Log::warning('Unauthorized file access blocked', [
                    'user_id' => $request->user()?->id,
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'path' => $request->path(),
                ]);

                abort(403, 'You do not have permission to access this file.');
            }
        }

        return $next($request);
    }

    /**
     * Check if this is a file access request
     *
     * @param Request $request
     * @return bool
     */
    protected function isFileAccessRequest(Request $request): bool
    {
        // Check for common file download route patterns
        $fileRoutes = [
            'download',
            'file',
            'attachment',
            'storage',
        ];

        foreach ($fileRoutes as $route) {
            if (str_contains($request->path(), $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authorize file access for the current user
     *
     * @param Request $request
     * @return bool
     */
    protected function authorizeFileAccess(Request $request): bool
    {
        $user = $request->user();

        // Unauthenticated users cannot access files
        if (!$user) {
            return false;
        }

        // Get the file path from the request
        $filePath = $this->extractFilePathFromRequest($request);

        if (!$filePath) {
            return false;
        }

        // Enforce the file storage policy
        return $this->filePolicy->enforceFileAccessPolicy($user, $filePath);
    }

    /**
     * Extract file path from the request
     *
     * @param Request $request
     * @return string|null
     */
    protected function extractFilePathFromRequest(Request $request): ?string
    {
        // Try to get from query parameters
        if ($request->has('file')) {
            return $request->query('file');
        }

        // Try to get from route parameters
        if ($request->route('file')) {
            return $request->route('file');
        }

        // Try to get from POST data
        if ($request->has('attachment')) {
            return $request->input('attachment');
        }

        // Try from path segments
        $pathSegments = explode('/', trim($request->path(), '/'));
        if (count($pathSegments) >= 2) {
            // For routes like /download/{file_path}
            if (in_array($pathSegments[0], ['download', 'file', 'attachment'])) {
                return implode('/', array_slice($pathSegments, 1));
            }
        }

        return null;
    }
}
