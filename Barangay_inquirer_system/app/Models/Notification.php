<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class Notification extends DatabaseNotification
{
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Scope to enforce row-level security - only show user's own notifications
     */
    public function scopeUserOwned($query)
    {
        $user = Auth::user();

        if (!$user) {
            return $query->whereNull('id'); // Return empty result if not authenticated
        }

        // All authenticated users can only see their own notifications
        return $query->where('notifiable_id', $user->id)
                     ->where('notifiable_type', User::class);
    }

    /**
     * Scope to get notifications accessible to the current user
     */
    public function scopeAccessibleToUser($query, $user = null)
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return $query->whereNull('id'); // Return empty result if not authenticated
        }

        return $query->where('notifiable_id', $user->id)
                     ->where('notifiable_type', User::class);
    }
}
