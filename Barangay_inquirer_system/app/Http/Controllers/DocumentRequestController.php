<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentRequestController extends Controller
{
    public function create()
    {
        return view('requests.create');
    }

    public function store(Request $request)
    {
        DocumentRequest::create([
            'user_id' => auth()->id(),
            'document_type' => $request->document_type,
            'purpose' => $request->purpose,
        ]);

        return back()->with('success','Request submitted!');
    }

    public function myRequests()
    {
        $requests = DocumentRequest::where('user_id',auth()->id())->get();
        return view('requests.my', compact('requests'));
    }
}
