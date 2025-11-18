<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cancha_id',
        'fecha',
        'hora',
        'hora_fin',
        'cantidad_horas',
        'precio_por_cancha',
        'precio',
        'estado',
        'deporte',
        'comprobante',
        'payment_link',
        'payment_reference',
        'payment_id',
        'payment_status',
        'payment_detail',
    ];

    protected $casts = [
        'payment_detail' => 'array',
    ];

    public function isPaid(): bool
    {
        return $this->payment_status === 'approved' || $this->estado === 'confirmada';
    }

    public function isPending(): bool
    {
        return in_array($this->payment_status, ['pending', 'in_process', 'in_mediation', 'pendiente_pago', null], true);
    }

    public function isRejected(): bool
    {
        return in_array($this->payment_status, ['rejected', 'cancelled'], true) || $this->estado === 'cancelada';
    }

    public function cancha()
    {
        return $this->belongsTo(Cancha::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
