<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('resident.announcements', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        return view('resident.announcement-detail', compact('announcement'));
    }
}
