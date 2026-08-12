<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = DocumentRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('resident.documents', compact('documents'));
    }

    public function show(DocumentRequest $document)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('resident.document-detail', compact('document'));
    }

    public function download(DocumentRequest $document)
    {
        if ($document->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($document->file_path) {
            return response()->download(storage_path('app/' . $document->file_path));
        }

        return redirect()->back()->with('error', 'Document not found');
    }
}
