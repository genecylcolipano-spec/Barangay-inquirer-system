<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\NewDocumentRequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class RequestController extends Controller
{
    public function create()
    {
        return view('resident.request-create');
    }

    public function store(Request $request)
    {
        // Validate incoming data with new fields
        $validated = $request->validate([
            'document_type' => 'required|string|in:barangay_clearance,purok_clearance,business_permit_clearance,certificate_of_indigency,residency_certificate,cedula,other',
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'address' => 'required|string|max:500',
            'details' => 'required|string|max:2000',
            'attachment' => 'required|file|mimetypes:application/pdf,image/jpeg,image/png,image/jpg|max:5120', // Use mimetypes for better security
        ], [
            'document_type.required' => 'Please select a document type.',
            'document_type.in' => 'Invalid document type selected.',
            'full_name.required' => 'Full name is required.',
            'full_name.regex' => 'Full name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'details.required' => 'Purpose is required.',
            'details.max' => 'Purpose cannot exceed 2000 characters.',
            'attachment.required' => 'ID document is required.',
            'attachment.mimetypes' => 'ID must be a valid PDF, JPG, JPEG, or PNG file.',
            'attachment.max' => 'ID file size cannot exceed 5MB.',
        ]);

        // Additional content validation for security
        $file = $validated['attachment'];
        $mimeType = $file->getMimeType();
        $content = file_get_contents($file->getRealPath());

        if ($mimeType === 'application/pdf') {
            if (!str_starts_with($content, '%PDF-')) {
                return back()->withErrors(['attachment' => 'Invalid PDF file.']);
            }
        } elseif (in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
            $imageInfo = @getimagesize($file->getRealPath());
            if (!$imageInfo) {
                return back()->withErrors(['attachment' => 'Invalid image file.']);
            }
        }

        $filePath = null;

        if (!empty($validated['attachment'])) {
            // Store the file with user and request context in secure storage
            $filePath = $validated['attachment']->store('requests/' . auth()->id(), 'local');
        }

        $req = DocumentRequest::create([
            'user_id' => auth()->id(),
            'full_name' => trim($validated['full_name']),
            'address' => trim($validated['address']),
            'document_type' => $validated['document_type'],
            'details' => trim($validated['details']),
            'attachment' => $filePath,
            'status' => 'pending',
        ]);
        
        \App\Models\Activity::log("Document request #{$req->id} created for {$validated['document_type']}", 'request_create');

        // Notify all admins and super-admins about the new request
        try {
            $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
            if ($admins->count() > 0) {
                Notification::send($admins, new NewDocumentRequestSubmitted($req, auth()->user()));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error("Failed to send notification to admins: " . $e->getMessage());
        }

        return redirect()->route('resident.requests')
            ->with('success', ' Request submitted successfully! Please wait for further response from the admin.');
    }

    public function index()
    {
        $requests = DocumentRequest::userOwned()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('resident.my-requests', compact('requests'));
    }

    public function show(DocumentRequest $request)
    {
        // Only allow user to view their own requests
        if ($request->user_id !== auth()->id()) {
            return redirect()->route('resident.requests')->with('error', 'Unauthorized action.');
        }

        return view('resident.request-show', compact('request'));
    }

    public function downloadAttachment(DocumentRequest $request)
    {
        // Only allow user to download their own request attachments
        if ($request->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

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
                'user_id' => auth()->id(),
                'attachment_path' => $attachment,
                'checked_disks' => ['local', 'public'],
            ]);
            abort(404, 'File not found on server. The file may have been deleted.');
        }

        try {
            \Log::info('Downloading file', [
                'request_id' => $request->id,
                'user_id' => auth()->id(),
                'disk' => $diskName,
            ]);
            return $disk->download($attachment);
        } catch (\Exception $e) {
            \Log::error('Error downloading file', [
                'request_id' => $request->id,
                'user_id' => auth()->id(),
                'attachment_path' => $attachment,
                'disk' => $diskName,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error downloading file. Please try again.');
        }
    }

    public function viewAttachment(DocumentRequest $request)
    {
        // Only allow user to view their own request attachments
        if ($request->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

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
                'user_id' => auth()->id(),
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
                'user_id' => auth()->id(),
                'attachment_path' => $attachment,
                'disk' => $diskName,
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error viewing file. Please try again.');
        }
    }

    public function destroy(DocumentRequest $request)
    {
        // Only allow user to delete their own requests
        if ($request->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Only allow deletion of pending requests
        if ($request->status !== 'pending') {
            return redirect()->back()->with('error', 'You can only delete pending requests.');
        }

        // Delete associated file if exists
        if ($request->attachment) {
            \Storage::disk('local')->delete($request->attachment);
        }

        $request->delete();

        return redirect()->route('resident.requests')
            ->with('success', 'Request deleted successfully.');
    }
}

