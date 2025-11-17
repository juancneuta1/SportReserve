<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMobileSession extends Model
{
    use HasFactory;

    protected $table = 'user_mobile_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'ip',
        'device',
        'user_agent',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
