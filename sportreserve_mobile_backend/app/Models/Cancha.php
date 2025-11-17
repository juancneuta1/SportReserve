<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cancha extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'ubicacion',
        'latitud',
        'longitud',
        'precio_por_hora',
        'disponibilidad',
        'imagen',
        'descripcion',
        'servicios',
        'capacidad',
    ];


    protected $casts = [
        'disponibilidad' => 'boolean',
        'precio_por_hora' => 'float',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    public function scopeConPromedioEstrellas($query)
    {
        return $query->withAvg('calificaciones as promedio_estrellas', 'estrellas')
            ->withCount('calificaciones as total_calificaciones');
    }

    public function promedioEstrellas(): float
    {
        if (!array_key_exists('promedio_estrellas', $this->attributes)) {
            $this->loadAggregate('calificaciones as promedio_estrellas', 'avg', 'estrellas');
        }

        return round((float) ($this->promedio_estrellas ?? 0), 2);
    }
}
