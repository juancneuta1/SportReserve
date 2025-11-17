<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Cancha;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservaAdminController extends Controller
{
    public function index(): View
    {
        $reservas = Reserva::with(['user', 'cancha'])
            ->orderByDesc('fecha')
            ->orderBy('hora')
            ->get();

        return view('admin.reservas.index', compact('reservas'));
    }

    public function edit(Reserva $reserva): View
    {
        $canchas = Cancha::orderBy('nombre')->get();

        return view('admin.reservas.edit', compact('reserva', 'canchas'));
    }

    public function update(Request $request, Reserva $reserva): RedirectResponse
    {
        $data = $request->validate([
            'cancha_id' => ['required', 'exists:canchas,id'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'cantidad_horas' => ['required', 'integer', 'min:1', 'max:5'],
            'precio_por_cancha' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', Rule::in(['pendiente', 'pendiente_validacion', 'en_espera', 'confirmada', 'cancelada'])],
        ]);

        $horaInicio = Carbon::createFromFormat('H:i', $data['hora']);
        $data['hora'] = $horaInicio->format('H:i');
        $data['hora_fin'] = $horaInicio->copy()->addHours((int) $data['cantidad_horas'])->format('H:i');

        if (! isset($data['precio_por_cancha'])) {
            $data['precio_por_cancha'] = $reserva->precio_por_cancha;
        }

        $reserva->update($data);

        return redirect()
            ->route('admin.reservas.index')
            ->with('status', 'Reserva actualizada correctamente.');
    }

    public function hold(Reserva $reserva): RedirectResponse
    {
        $reserva->update(['estado' => 'en_espera']);

        return back()->with('status', 'Reserva marcada en espera.');
    }

    public function cancel(Reserva $reserva): RedirectResponse
    {
        $reserva->update(['estado' => 'cancelada']);

        return back()->with('status', 'Reserva cancelada.');
    }
}
