<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'message', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper for quick logging
     */
    public static function log(string $message, string $type = null, $userId = null)
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'message' => $message,
            'type' => $type,
        ]);
    }
}
