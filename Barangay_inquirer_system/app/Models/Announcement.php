<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 
        'content', 
        'is_published', 
        'created_by',
        'tag',
        'priority',
        'category',
        'announcement_date',
        'show_on_homepage',
        'display_order',
        'icon',
        'excerpt'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_on_homepage' => 'boolean',
        'announcement_date' => 'date',
        'display_order' => 'integer'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get announcements for homepage display
     */
    public static function getHomepageAnnouncements()
    {
        return static::where('is_published', true)
            ->where('show_on_homepage', true)
            ->orderBy('display_order', 'asc')
            ->orderBy('announcement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
    }

    /**
     * Get tag badge class for styling
     */
    public function getTagBadgeClass()
    {
        return match($this->tag) {
            'today' => 'bg-primary',
            'featured' => 'bg-warning text-dark',
            'success' => 'bg-success',
            'warning' => 'bg-warning',
            'danger' => 'bg-danger',
            'info' => 'bg-info',
            default => 'bg-secondary'
        };
    }

    /**
     * Get tag display text
     */
    public function getTagDisplayText()
    {
        return match($this->tag) {
            'today' => 'TODAY',
            'featured' => 'FEATURED',
            'success' => 'SUCCESS',
            'warning' => 'WARNING',
            'danger' => 'URGENT',
            'info' => 'INFO',
            default => strtoupper($this->tag)
        };
    }

    /**
     * Get display date (announcement_date if set, otherwise created_at)
     */
    public function getDisplayDate()
    {
        return $this->announcement_date ?? $this->created_at;
    }
}
