<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index()
    {
        $announcements = Announcement::paginate(15);
        return view('superadmin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create()
    {
        return view('superadmin.announcements.create');
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'tag' => 'required|string|in:info,today,featured,success,warning,danger',
            'category' => 'required|string|in:general,maintenance,feature,policy,event,document',
            'priority' => 'required|string|in:low,normal,high',
            'announcement_date' => 'nullable|date',
            'icon' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
            'show_on_homepage' => 'nullable|boolean',
        ]);

        $validated['created_by'] = auth()->id() ?? User::whereIn('role', ['admin', 'super_admin'])->value('id') ?? User::first()->id;
        $validated['is_published'] = $request->has('is_published') ? 1 : 0;
        $validated['show_on_homepage'] = $request->has('show_on_homepage') ? 1 : 0;

        $announcement = Announcement::create($validated);
        \App\Models\Activity::log("Announcement created: {$announcement->title}", 'announcement_create');

        // Notify all residents about the new announcement (only if published)
        if ($announcement->is_published) {
            $residents = \App\Models\User::where('role', 'resident')->get();
            foreach ($residents as $resident) {
                $resident->notify(new \App\Notifications\NewAnnouncementPosted($announcement));
            }
        }

        return redirect()->route('superadmin.announcements.index')
            ->with('success', 'Announcement created successfully');
    }

    /**
     * Display the specified announcement.
     */
    public function show(Announcement $announcement)
    {
        return view('superadmin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(Announcement $announcement)
    {
        return view('superadmin.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified announcement in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'tag' => 'required|string|in:info,today,featured,success,warning,danger',
            'category' => 'required|string|in:general,maintenance,feature,policy,event,document',
            'priority' => 'required|string|in:low,normal,high',
            'announcement_date' => 'nullable|date',
            'icon' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
            'show_on_homepage' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->has('is_published') ? 1 : 0;
        $validated['show_on_homepage'] = $request->has('show_on_homepage') ? 1 : 0;

        $announcement->update($validated);
        \App\Models\Activity::log("Announcement updated: {$announcement->title}", 'announcement_update');

        return redirect()->route('superadmin.announcements.show', $announcement)
            ->with('success', 'Announcement updated successfully');
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $title = $announcement->title;
        $announcement->delete();
        \App\Models\Activity::log("Announcement deleted: {$title}", 'announcement_delete');

        return redirect()->route('superadmin.announcements.index')
            ->with('success', 'Announcement deleted successfully');
    }
}
