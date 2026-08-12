<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DocumentRequest;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Total Users Count
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Admin Statistics
        $totalAdmins = User::where('role', 'admin')->count();
        $activeAdmins = User::where('role', 'admin')
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        // Request Statistics
        $pendingRequests = DocumentRequest::where('status', 'pending')->count();
        $approvedRequests = DocumentRequest::where('status', 'approved')->count();
        $rejectedRequests = DocumentRequest::where('status', 'rejected')->count();

        // System Health (default 95%)
        $systemHealth = '95%';

        // Prepare data for charts
        $userGrowthData = $this->getUserGrowthData();
        $requestStatusData = $this->getRequestStatusData();

        // Latest records for tables
        $latestUsers = User::latest()->take(5)->get();
        $pendingApprovals = DocumentRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
        // recent activity
        $recentActivities = Activity::latest()->take(5)->get();

        return view('superadmin.dashboard.index', [
            'totalUsers' => $totalUsers,
            'newUsersThisMonth' => $newUsersThisMonth,
            'totalAdmins' => $totalAdmins,
            'activeAdmins' => $activeAdmins,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'systemHealth' => $systemHealth,
            'userGrowthData' => $userGrowthData,
            'requestStatusData' => $requestStatusData,
            'latestUsers' => $latestUsers,
            'pendingApprovals' => $pendingApprovals,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Get user growth data for the last 7 days.
     *
     * @return array
     */
    private function getUserGrowthData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = User::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * Get request status distribution.
     *
     * @return array
     */
    private function getRequestStatusData()
    {
        return [
            'approved' => DocumentRequest::where('status', 'approved')->count(),
            'pending' => DocumentRequest::where('status', 'pending')->count(),
            'rejected' => DocumentRequest::where('status', 'rejected')->count(),
        ];
    }
}
