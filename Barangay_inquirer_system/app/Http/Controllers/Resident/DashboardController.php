<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            
            // Count requests by status
            $totalRequests = DocumentRequest::where('user_id', auth()->id())->count();
            $pendingRequests = DocumentRequest::where('user_id', auth()->id())
                ->where('status', 'pending')->count();
            $processingRequests = DocumentRequest::where('user_id', auth()->id())
                ->where('status', 'processing')->count();
            $approvedRequests = DocumentRequest::where('user_id', auth()->id())
                ->where('status', 'approved')->count();
            $rejectedRequests = DocumentRequest::where('user_id', auth()->id())
                ->where('status', 'rejected')->count();
            $completedRequests = DocumentRequest::where('user_id', auth()->id())
                ->where('status', 'completed')->count();
            
            // Get documents ready for pickup (approved documents)
            $readyForPickup = DocumentRequest::where('user_id', auth()->id())
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();
            
            $recentRequests = DocumentRequest::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            $announcements = Announcement::orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            
            // Get unread notifications
            $notifications = $user->unreadNotifications()->limit(5)->get();
            $unreadCount = $user->unreadNotifications()->count();
            
            return view('resident.dashboard', compact(
                'user',
                'totalRequests',
                'pendingRequests',
                'processingRequests',
                'approvedRequests',
                'rejectedRequests',
                'completedRequests',
                'recentRequests',
                'announcements',
                'notifications',
                'unreadCount',
                'readyForPickup'
            ));
        } catch (Exception $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = \App\Services\ErrorHandler::genericErrorMessage($statusCode);
            Log::error('Error in Resident\DashboardController@index', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
            return view('resident.dashboard', ['error' => $message]);
        }
    }
}
