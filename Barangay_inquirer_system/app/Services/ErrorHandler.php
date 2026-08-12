<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Centralized error handling service
 * - Returns generic messages to users (security)
 * - Logs detailed errors server-side (debugging)
 * - Tracks error context (IP, user agent, request ID)
 */
class ErrorHandler
{
    /**
     * Handle and log an error with full context
     *
     * @param Exception $exception
     * @param Request $request
     * @param string $context - Brief context of what was being done (e.g., "password_reset_email_send")
     * @param array $additionalData - Extra data to log
     * @return void
     */
    public static function logError(Exception $exception, Request $request, string $context = 'general_error', array $additionalData = []): void
    {
        $errorData = [
            'context' => $context,
            'error' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'timestamp' => now()->toIso8601String(),
            'request_id' => uniqid('req_'),
        ];

        // Add any additional user/authentication data
        if ($request->user()) {
            $errorData['user_id'] = $request->user()->id;
            $errorData['user_email'] = $request->user()->email;
        }

        // Merge additional data
        $errorData = array_merge($errorData, $additionalData);

        // Log the error
        Log::error("Error in {$context}", $errorData);
    }

    /**
     * Log a warning with context
     *
     * @param string $message
     * @param Request $request
     * @param string $context - Brief context of what was being done
     * @param array $additionalData - Extra data to log
     * @return void
     */
    public static function logWarning(string $message, Request $request, string $context = 'general_warning', array $additionalData = []): void
    {
        $warningData = [
            'context' => $context,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'timestamp' => now()->toIso8601String(),
            'request_id' => uniqid('req_'),
        ];

        // Add any user data
        if ($request->user()) {
            $warningData['user_id'] = $request->user()->id;
            $warningData['user_email'] = $request->user()->email;
        }

        // Merge additional data
        $warningData = array_merge($warningData, $additionalData);

        // Log the warning
        Log::warning($message, $warningData);
    }

    /**
     * Log an info message with context
     *
     * @param string $message
     * @param Request $request
     * @param string $context - Brief context
     * @param array $additionalData - Extra data to log
     * @return void
     */
    public static function logInfo(string $message, Request $request, string $context = 'general_info', array $additionalData = []): void
    {
        $infoData = [
            'context' => $context,
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
            'request_id' => uniqid('req_'),
        ];

        // Add user data only in info logs
        if ($request->user()) {
            $infoData['user_id'] = $request->user()->id;
        }

        // Merge additional data
        $infoData = array_merge($infoData, $additionalData);

        // Log the info
        Log::info($message, $infoData);
    }

    /**
     * Get generic error message based on HTTP status code
     * Used to return safe messages to users
     *
     * @param int $statusCode
     * @return string
     */
    public static function genericErrorMessage(int $statusCode = 500): string
    {
        return match($statusCode) {
            400 => 'The request was invalid. Please check your input and try again.',
            401 => 'You are not authorized to perform this action.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The requested resource was not found.',
            409 => 'A conflict occurred. Please try again.',
            422 => 'The provided information is invalid. Please check and try again.',
            429 => 'Too many requests. Please try again later.',
            500 => 'An error occurred. Please try again later.',
            502 => 'The server is temporarily unavailable. Please try again later.',
            503 => 'The service is temporarily unavailable. Please try again later.',
            default => 'An unexpected error occurred. Please try again later.',
        };
    }

    /**
     * Create a safe error response for APIs
     * Returns only what's safe for users to see
     *
     * @param int $statusCode
     * @param string|null $message - Override default message (should be safe for users)
     * @param array $errors - Validation errors (no sensitive data)
     * @return array
     */
    public static function apiErrorResponse(int $statusCode = 500, ?string $message = null, array $errors = []): array
    {
        $response = [
            'message' => $message ?? self::genericErrorMessage($statusCode),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Sanitize data for logging (remove sensitive information)
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeForLogging(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'reset_token', 'secret', 'api_key', 'credit_card'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }
}
