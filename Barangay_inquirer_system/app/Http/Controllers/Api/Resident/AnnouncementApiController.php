<?php

namespace App\Http\Controllers\Api\Resident;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnouncementApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $announcements = Announcement::orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json($announcements, 200);
        } catch (\Throwable $exception) {
            Log::error('Resident announcements API index failed', [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function show(Announcement $announcement)
    {
        try {
            return response()->json($announcement, 200);
        } catch (\Throwable $exception) {
            Log::error('Resident announcements API show failed', [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
}
