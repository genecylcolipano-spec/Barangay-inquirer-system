<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DocumentRequest extends Model
{
    use HasFactory;
    
    protected $table = 'document_requests';
    
    protected $fillable = [
        'user_id',
        'full_name',
        'address',
        'document_type',
        'details',
        'attachment',
        'notes',
        'status',
        'resident_name',
        'rejection_reason'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to enforce row-level security based on user role
     */
    public function scopeUserOwned($query)
    {
        $user = Auth::user();
        
        if (!$user) {
            return $query->whereNull('id'); // Return empty result if not authenticated
        }

        // Residents can only see their own requests
        if ($user->role === 'resident') {
            return $query->where('user_id', $user->id);
        }

        // Admins and super_admins can see all requests
        return $query;
    }

    /**
     * Scope to get only requests visible to the current user
     */
    public function scopeAccessibleToUser($query, $user = null)
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return $query->whereNull('id'); // Return empty result if not authenticated
        }

        if ($user->role === 'resident') {
            return $query->where('user_id', $user->id);
        }

        return $query;
    }
}
