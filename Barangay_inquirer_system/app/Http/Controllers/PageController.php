<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Notifications\ContactMessageSubmitted;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Exception;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    // Home Page
    public function home()
    {
        try {
            // Get latest announcements for homepage
            $announcements = Announcement::latest()->limit(3)->get();
            return view('home', compact('announcements')); 
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in PageController@home', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('home', ['error' => $message]);
        }
    }

    // Login Page
    public function login()
    {
        try {
            return view('auth.login'); 
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in PageController@login', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('auth.login', ['error' => $message]);
        }
    }

    // Sign Up Page
    public function signup()
    {
        try {
            return view('auth.register'); 
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in PageController@signup', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('auth.register', ['error' => $message]);
        }
    }

    // Dashboard Page (after login)
    public function dashboard()
    {
        try {
            // If user is authenticated, redirect to resident dashboard
            if (auth()->check()) {
                return redirect()->route('resident.dashboard');
            }

            // Public or generic dashboard view
            return view('dashboard.index');
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in PageController@dashboard', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('dashboard.index', ['error' => $message]);
        }
    }

/**for navbar page ni */
// About Page
public function about()
{
    try {
        return view('pages.about');
    } catch (Exception $e) {
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
        Log::error('Error in PageController@about', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
        return view('pages.about', ['error' => $message]);
    }
}

// Services Page
public function services()
{
    try {
        return view('pages.services');
    } catch (Exception $e) {
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
        Log::error('Error in PageController@services', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
        return view('pages.services', ['error' => $message]);
    }
}

// Announcements Page
public function announcements()
{
    try {
        $announcements = Announcement::latest()->paginate(10);
        return view('pages.announcements', compact('announcements'));
    } catch (Exception $e) {
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
        Log::error('Error in PageController@announcements', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
        return view('pages.announcements', ['error' => $message]);
    }
}

// Announcements list (admin)
public function announcementsIndex()
{
    try {
        $announcements = Announcement::latest()->paginate(15);
        return view('announcements.index', compact('announcements'));
    } catch (Exception $e) {
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
        Log::error('Error in PageController@announcementsIndex', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
        return view('announcements.index', ['error' => $message]);
    }
}

// Contact Page
public function contact()
{
    try {
        return view('pages.contact');
    } catch (Exception $e) {
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
        Log::error('Error in PageController@contact', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
        return view('pages.contact', ['error' => $message]);
    }
}

// Handle contact form submission
public function submitContact(Request $request)
{
    // Validate the form data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|min:10|max:5000',
    ], [
        'name.required' => 'Full name is required.',
        'email.required' => 'Email address is required.',
        'email.email' => 'Please enter a valid email address.',
        'subject.required' => 'Subject is required.',
        'message.required' => 'Message cannot be empty.',
        'message.min' => 'Message must be at least 10 characters.',
    ]);

    // Here you can:
    // 1. Send email to admin
    // 2. Save to database
    // 3. Both

    // Notify all admins and super-admins in-app and via mail (if configured)
    try {
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        \Log::info('Contact form submitted', [
            'admin_count' => $admins->count(),
            'from' => $validated['email'],
            'subject' => $validated['subject'],
        ]);
        
        if ($admins->count() > 0) {
            Notification::send($admins, new ContactMessageSubmitted($validated));
            \Log::info('Notifications sent to ' . $admins->count() . ' admins');
        }
    } catch (\Exception $e) {
        \Log::error('Contact form notification error: ' . $e->getMessage());
    }

    // Redirect back with success message
    return redirect('/')->with('success', 'Thank you! Your message has been sent successfully. We will respond to you soon.');
}

    // Privacy Policy Page
    public function privacyPolicy()
    {
        $content = \App\Models\Setting::get('privacy_policy', 'Privacy Policy content not yet configured.');
        return view('pages.privacy-policy', compact('content'));
    }

    // Terms of Service Page
    public function termsOfService()
    {
        $content = \App\Models\Setting::get('terms_of_service', 'Terms of Service content not yet configured.');
        return view('pages.terms-of-service', compact('content'));
    }

}