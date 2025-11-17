<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Services\AdminActionNotifier;

class ComprobanteAdminController extends Controller
{
    // Mostrar reservas con comprobantes pendientes
    public function index()
    {
        $reservas = Reserva::with(['user', 'cancha'])
            ->whereIn('estado', ['pendiente', 'pendiente_validacion', 'pendiente_verificacion', 'en_espera'])
            ->whereNotNull('payment_reference')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.comprobantes.index', compact('reservas'));
    }


    // Aprobar o rechazar comprobante
    public function validar(Request $request, $id)
    {
        $request->validate(['accion' => 'required|in:aprobar,rechazar']);

        $reserva = Reserva::findOrFail($id);
        $reserva->estado = $request->accion === 'aprobar' ? 'confirmada' : 'cancelada';
        $reserva->save();

        $accionTexto = $request->accion === 'aprobar' ? 'aprobó' : 'rechazó';

        AdminNotification::create([
            'type' => 'comprobante',
            'title' => 'Validación de comprobante',
            'body' => "Se {$accionTexto} el comprobante de la reserva #{$reserva->id}.",
        ]);

        AdminActionNotifier::send(
            $request->user(),
            'Validación de comprobante',
            "Se {$accionTexto} el comprobante de la reserva #{$reserva->id}.",
            [
                'Usuario' => $reserva->user?->email ?? 'N/A',
                'Cancha' => $reserva->cancha?->nombre ?? 'N/A',
                'Referencia de pago' => $reserva->payment_reference ?? 'Sin referencia',
                'Estado final' => ucfirst($reserva->estado),
            ]
        );

        return redirect()->route('admin.comprobantes.index')->with('status', 'Reserva actualizada correctamente.');
    }
}
