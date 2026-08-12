<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestDocument;
use App\Models\DocumentRequest;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalRequests = DocumentRequest::count();
        $pendingRequests = DocumentRequest::where('status', 'pending')->count();
        $approvedRequests = DocumentRequest::where('status', 'approved')->count();
        $rejectedRequests = DocumentRequest::where('status', 'rejected')->count();
        
        $totalUsers = User::count();
        $totalAnnouncements = Announcement::count();
        
        // Get recent requests (last 10)
        $recentRequests = DocumentRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get pending requests for urgent attention (last 5)
        $pendingRequestsList = DocumentRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();
        
        // Get recent announcements
        $recentAnnouncements = Announcement::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get requests by type
        $requestsByType = DocumentRequest::select('document_type', DB::raw('count(*) as count'))
            ->groupBy('document_type')
            ->get();
        
        // Get requests by status for chart
        $requestsByStatus = [
            'pending' => $pendingRequests,
            'approved' => $approvedRequests,
            'rejected' => $rejectedRequests,
        ];
        
        // Get this month's requests
        $thisMonthRequests = DocumentRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Get this month's new users
        $thisMonthUsers = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return view('admin.dashboard.index', compact(
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'totalUsers',
            'totalAnnouncements',
            'recentRequests',
            'pendingRequestsList',
            'recentAnnouncements',
            'requestsByType',
            'requestsByStatus',
            'thisMonthRequests',
            'thisMonthUsers'
        ));
    }
}
