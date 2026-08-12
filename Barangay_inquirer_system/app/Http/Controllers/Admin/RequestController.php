<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentRequest::with('user');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10);

        return view('admin.requests.index', compact('requests'));
    }

    public function show(DocumentRequest $request)
    {
        $request->load('user');
        return view('admin.requests.show', compact('request'));
    }

    public function updateNotes(DocumentRequest $request, Request $httpRequest)
    {
        // Load the user relationship if not already loaded
        $request->load('user');

        \Log::info('updateNotes method called', [
            'request_id' => $request->id,
            'user_id' => $request->user_id,
            'has_user' => $request->user ? 'yes' : 'no',
            'request_data' => $httpRequest->all(),
        ]);

        $validated = $httpRequest->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldNotes = $request->notes;
        $newNotes = $validated['notes'];

        \Log::info('Notes update attempt', [
            'request_id' => $request->id,
            'user_id' => $request->user_id,
            'has_user' => $request->user ? 'yes' : 'no',
            'old_notes' => substr($oldNotes ?? '', 0, 50),
            'new_notes' => substr($newNotes ?? '', 0, 50),
        ]);

        // Only update and notify if notes actually changed
        if ($oldNotes !== $newNotes) {
            $request->update($validated);
            \App\Models\Activity::log("Notes updated for request #{$request->id}", 'request_notes');

            // Notify the resident about the notes update (if user exists)
            if ($request->user) {
                \Log::info('Sending notification to user', ['user_id' => $request->user->id]);
                $request->user->notify(new \App\Notifications\AdminNotesUpdated($request, $oldNotes, $newNotes));
            } else {
                \Log::warning('Updated notes for request with no user: ' . $request->id);
            }

            return back()->with('success', 'Notes updated successfully. Resident has been notified.');
        }

        return back()->with('info', 'No changes were made to the notes.');
    }

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

        return back()->with('success', 'Request approved successfully.');
    }

    public function reject(DocumentRequest $request)
    {
        $request->update(['status' => 'rejected']);
        \App\Models\Activity::log("Request #{$request->id} rejected", 'request_reject');
        
        // Notify the resident about rejection
        if ($request->user) {
            $request->user->notify(new \App\Notifications\DocumentRequestStatusChanged($request, 'rejected'));
        }
        
        return back()->with('success', 'Request rejected successfully.');
    }

    public function processing(DocumentRequest $request)
    {
        $request->update(['status' => 'processing']);
        \App\Models\Activity::log("Request #{$request->id} marked as processing", 'request_processing');
        
        // Notify the resident that document is being prepared
        if ($request->user) {
            $request->user->notify(new \App\Notifications\DocumentRequestStatusChanged($request, 'processing'));
        }
        
        return back()->with('success', 'Request marked as processing. Resident has been notified.');
    }

    public function pending(DocumentRequest $request)
    {
        $request->update(['status' => 'pending']);
        \App\Models\Activity::log("Request #{$request->id} marked pending", 'request_pending');
        return back()->with('success', 'Request marked as pending.');
    }

    public function downloadAttachment(DocumentRequest $request)
    {
        // Admin can download any attachment
        if (!$request->attachment) {
            abort(404, 'No file attached to this request.');
        }

        $attachment = $request->attachment;
        $localDisk = \Storage::disk('local');
        $publicDisk = \Storage::disk('public');

        // Try to find file in local (private) disk first, then public disk
        $disk = null;
        $diskName = null;

        if ($localDisk->exists($attachment)) {
            $disk = $localDisk;
            $diskName = 'local';
        } elseif ($publicDisk->exists($attachment)) {
            $disk = $publicDisk;
            $diskName = 'public';
        }

        if (!$disk) {
            \Log::error('File not found for download', [
                'request_id' => $request->id,
                'attachment_path' => $attachment,
                'checked_disks' => ['local', 'public'],
            ]);
            abort(404, 'File not found on server. The file may have been deleted.');
        }

        try {
            \Log::info('Downloading file', [
                'request_id' => $request->id,
                'disk' => $diskName,
            ]);
            return $disk->download($attachment);
        } catch (\Exception $e) {
            \Log::error('Error downloading file', [
                'request_id' => $request->id,
                'attachment_path' => $attachment,
                'disk' => $diskName,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error downloading file. Please try again.');
        }
    }

    public function viewAttachment(DocumentRequest $request)
    {
        // Admin can view any attachment
        if (!$request->attachment) {
            abort(404, 'No file attached to this request.');
        }

        $attachment = $request->attachment;
        $localDisk = \Storage::disk('local');
        $publicDisk = \Storage::disk('public');

        // Try to find file in local (private) disk first, then public disk
        $disk = null;
        $diskName = null;

        if ($localDisk->exists($attachment)) {
            $disk = $localDisk;
            $diskName = 'local';
        } elseif ($publicDisk->exists($attachment)) {
            $disk = $publicDisk;
            $diskName = 'public';
        }

        if (!$disk) {
            \Log::error('File not found for viewing', [
                'request_id' => $request->id,
                'attachment_path' => $attachment,
                'checked_disks' => ['local', 'public'],
            ]);
            abort(404, 'File not found on server.');
        }

        try {
            $fileContent = $disk->get($attachment);
            $mimeType = $disk->mimeType($attachment);
            
            // Set Content-Disposition to inline (view in browser) instead of attachment (download)
            return response($fileContent, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . basename($attachment) . '"');
        } catch (\Exception $e) {
            \Log::error('Error viewing file', [
                'request_id' => $request->id,
                'attachment_path' => $attachment,
                'disk' => $diskName,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error viewing file. Please try again.');
        }
    }
}
