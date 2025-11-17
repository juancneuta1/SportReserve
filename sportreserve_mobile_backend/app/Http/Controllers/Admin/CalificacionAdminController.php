<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cancha;
use Illuminate\View\View;

class CalificacionAdminController extends Controller
{
    public function index(): View
    {
        $canchas = Cancha::conPromedioEstrellas()
            ->orderByDesc('promedio_estrellas')
            ->orderByDesc('total_calificaciones')
            ->get();

        return view('admin.calificaciones.index', compact('canchas'));
    }
}
