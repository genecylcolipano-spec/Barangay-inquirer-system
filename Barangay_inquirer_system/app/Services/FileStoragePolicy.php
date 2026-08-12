<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Service to enforce file storage access policies
 * Ensures users can only access files they own/uploaded
 */
class FileStoragePolicy
{
    /**
     * Check if a user can download a file from the storage
     *
     * @param User $user The user attempting to download
     * @param string $filePath The file path in storage
     * @param string $disk The disk name (local, public, etc.)
     * @return bool True if user can access the file
     */
    public function canDownloadFile(User $user, string $filePath, string $disk = 'local'): bool
    {
        // If user is super_admin or admin, they can download any file
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        // For residents, only allow files from their own request directory
        if ($user->role === 'resident') {
            return $this->isFileOwnedByUser($user, $filePath);
        }

        return false;
    }

    /**
     * Check if a user can access a view a file from storage
     *
     * @param User $user
     * @param string $filePath
     * @param string $disk
     * @return bool
     */
    public function canViewFile(User $user, string $filePath, string $disk = 'local'): bool
    {
        return $this->canDownloadFile($user, $filePath, $disk);
    }

    /**
     * Check if a file is uploaded by a specific user
     *
     * @param User $user
     * @param string $filePath
     * @return bool
     */
    public function isFileOwnedByUser(User $user, string $filePath): bool
    {
        // Extract the user_id from the file path
        // Files are stored in: requests/{user_id}/{filename}
        $pathParts = explode('/', trim($filePath, '/'));

        if (count($pathParts) < 2) {
            return false;
        }

        // Check if file is in user's request directory
        if ($pathParts[0] === 'requests' && is_numeric($pathParts[1])) {
            $fileUserId = (int) $pathParts[1];
            return $fileUserId === $user->id;
        }

        // Check if file is associated with a document request owned by user
        $documentRequest = DocumentRequest::where('attachment', $filePath)
            ->where('user_id', $user->id)
            ->first();

        return $documentRequest !== null;
    }

    /**
     * Get the allowed directory for a user's uploads
     *
     * @param User $user
     * @return string
     */
    public function getAllowedUploadDirectory(User $user): string
    {
        if ($user->role === 'resident') {
            return "requests/{$user->id}";
        }

        if ($user->role === 'admin') {
            return "admin/{$user->id}";
        }

        if ($user->role === 'super_admin') {
            return "superadmin/{$user->id}";
        }

        return "uploads/{$user->id}";
    }

    /**
     * Validate a file path is safe and within allowed boundaries
     *
     * @param string $filePath
     * @return bool True if path is valid and safe
     */
    public function isValidFilePath(string $filePath): bool
    {
        // Prevent directory traversal
        if (str_contains($filePath, '..') || str_contains($filePath, '\\')) {
            return false;
        }

        // Check path doesn't start or end with unintended characters
        $path = trim($filePath, '/');

        if (empty($path)) {
            return false;
        }

        // Path must not contain suspicious patterns
        $suspiciousPatterns = [
            '/.env',
            '/config/',
            '/bootstrap/',
            '/vendor/',
            '/database/',
            '/.git',
            '/storage/logs/',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains('/' . $path, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Enforce file access policy by validating against database records
     *
     * @param User $user
     * @param string $filePath
     * @return bool True if access is allowed
     */
    public function enforceFileAccessPolicy(User $user, string $filePath): bool
    {
        // First validate path is safe
        if (!$this->isValidFilePath($filePath)) {
            \Log::warning('Invalid file path attempted', [
                'user_id' => $user->id,
                'file_path' => $filePath,
            ]);

            return false;
        }

        // Only file owner and admins can download
        if (!$this->canDownloadFile($user, $filePath)) {
            \Log::warning('Unauthorized file access attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'file_path' => $filePath,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get all files a user has access to
     *
     * @param User $user
     * @param string $disk
     * @return array List of file paths user can access
     */
    public function getAccessibleFiles(User $user, string $disk = 'local'): array
    {
        $storage = Storage::disk($disk);

        if (in_array($user->role, ['admin', 'super_admin'])) {
            // Admins can list all files
            return $storage->allFiles();
        }

        if ($user->role === 'resident') {
            // Residents can only list their own files
            $userDir = "requests/{$user->id}";

            if (!$storage->exists($userDir)) {
                return [];
            }

            return $storage->files($userDir);
        }

        return [];
    }

    /**
     * Delete a file with authorization check
     *
     * @param User $user
     * @param string $filePath
     * @param string $disk
     * @return bool True if deletion was successful
     */
    public function deleteFile(User $user, string $filePath, string $disk = 'local'): bool
    {
        if (!$this->canDownloadFile($user, $filePath, $disk)) {
            \Log::warning('Unauthorized file deletion attempt', [
                'user_id' => $user->id,
                'file_path' => $filePath,
            ]);

            return false;
        }

        $storage = Storage::disk($disk);

        if ($storage->exists($filePath)) {
            return $storage->delete($filePath);
        }

        return false;
    }
}
