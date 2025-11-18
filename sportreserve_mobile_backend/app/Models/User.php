<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atributos que pueden asignarse en masa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo_url',
        'role',
        'last_login_at',
        'must_change_password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_enabled_at',
        'two_factor_confirmed_at',
        'two_factor_verified',
        'failed_login_count',
        'locked_until',
    ];

    /**
     * Atributos ocultos en las respuestas JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'must_change_password' => 'boolean',
        'two_factor_enabled_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_verified' => 'boolean',
        'locked_until' => 'datetime',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Envía una notificación de restablecimiento de contraseña personalizada.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
