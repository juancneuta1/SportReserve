<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFailedLogin extends Model
{
    use HasFactory;

    protected $table = 'user_failed_logins';

    protected $fillable = [
        'user_id',
        'email',
        'ip',
        'user_agent',
        'attempted_at',
        'locked',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'locked' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
