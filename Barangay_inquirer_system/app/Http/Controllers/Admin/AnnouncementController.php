<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

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

        // Notify all residents about the new announcement (only if published)
        if ($announcement->is_published) {
            $residents = \App\Models\User::where('role', 'resident')->get();
            foreach ($residents as $resident) {
                $resident->notify(new \App\Notifications\NewAnnouncementPosted($announcement));
            }
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

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

        return redirect()->route('admin.announcements.show', $announcement)
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}
