<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of all requests.
     */
    public function index(Request $request)
    {
        $query = DocumentRequest::query();

        if ($request->has('status') && $request->status !== 'all') {
            // only allow known statuses
            $allowed = ['pending','approved','rejected'];
            if (in_array($request->status, $allowed, true)) {
                $query->where('status', $request->status);
            }
        }

        $requests = $query->paginate(15);
        
        return view('superadmin.requests.index', compact('requests'));
    }

    /**
     * Display the specified request.
     */
    public function show(DocumentRequest $request)
    {
        $request->load('user');
        return view('superadmin.requests.show', compact('request'));
    }

    /**
     * Approve a request.
     */
    public function approve(DocumentRequest $request)
    {
        $request->update(['status' => 'approved']);
        \App\Models\Activity::log("Request #{$request->id} approved", 'request_approve');

        // Notify the resident about the approved request (if user exists)
        if ($request->user) {
            $request->user->notify(new \App\Notifications\DocumentRequestStatusChanged($request, 'approved'));
        } else {
            \Log::warning('Approved request has no user associated: ' . $request->id);
        }

        return back()->with('success', 'Request approved successfully');
    }

    /**
     * Reject a request.
     */
    public function reject(DocumentRequest $request, Request $httpRequest)
    {
        $validated = $httpRequest->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $request->update(['status' => 'rejected']);
        \App\Models\Activity::log("Request #{$request->id} rejected", 'request_reject');
        
        return back()->with('success', 'Request rejected successfully');
    }

    /**
     * Update request notes.
     */
    public function updateNotes(DocumentRequest $request, Request $httpRequest)
    {
        // Load the user relationship if not already loaded
        $request->load('user');

        $validated = $httpRequest->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldNotes = $request->notes;
        $newNotes = $validated['notes'];

        // Only update and notify if notes actually changed
        if ($oldNotes !== $newNotes) {
            $request->update(['notes' => $newNotes]);
            \App\Models\Activity::log("Notes updated for request #{$request->id}", 'request_notes');

            // Notify the resident about the notes update (if user exists)
            if ($request->user) {
                $request->user->notify(new \App\Notifications\AdminNotesUpdated($request, $oldNotes, $newNotes));
            } else {
                \Log::warning('Updated notes for request with no user: ' . $request->id);
            }

            return back()->with('success', 'Notes updated successfully. Resident has been notified.');
        }

        return back()->with('info', 'No changes were made to the notes.');
    }
}
