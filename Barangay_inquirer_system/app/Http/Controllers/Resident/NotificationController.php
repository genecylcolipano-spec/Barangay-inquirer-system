<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read
     */
    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return back()->with('success', 'Notification marked as read.');
        }

        return back()->with('error', 'Notification not found.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();
        $notificationIds = $request->input('notification_ids', []);

        if (!empty($notificationIds)) {
            $user->notifications()
                ->whereIn('id', $notificationIds)
                ->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get all notifications for the user (page)
     */
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('resident.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Delete a single notification
     */
    public function destroy($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->delete();
            return back()->with('success', 'Notification deleted.');
        }

        return back()->with('error', 'Notification not found.');
    }

    /**
     * Delete all notifications for the user
     */
    public function deleteAll()
    {
        $user = auth()->user();
        $user->notifications()->delete();

        return back()->with('success', 'All notifications deleted.');
    }

    /**
     * Get recent notifications for AJAX dropdown
     */
    public function getRecent()
    {
        $user = auth()->user();
        $notifications = $user->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'time' => $notification->created_at->diffForHumans(),
                    'read' => !is_null($notification->read_at),
                    'url' => $this->getNotificationUrl($notification),
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Check unread notification count for AJAX
     */
    public function checkUnread()
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Get URL for notification based on its data
     */
    private function getNotificationUrl($notification)
    {
        $data = $notification->data;

        if (isset($data['request_id'])) {
            return route('resident.request.show', $data['request_id']);
        }

        if (isset($data['announcement_id'])) {
            return route('resident.announcement.show', $data['announcement_id']);
        }

        return route('resident.notifications');
    }
}
