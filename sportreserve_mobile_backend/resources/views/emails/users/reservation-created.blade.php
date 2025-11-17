<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva creada</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;color:#0f2b1a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;padding:32px;box-shadow:0 18px 35px rgba(15,43,26,0.08);">
                <tr>
                    <td>
                        <p style="font-size:14px;letter-spacing:3px;color:#23a15a;text-transform:uppercase;margin:0 0 12px 0;">SportReserve</p>
                        <h2 style="margin:0 0 16px 0;">¡Hola, {{ $user->name }}!</h2>
                        <p style="margin:0 0 16px 0;color:#5a5f5c;">Tu reserva se registró correctamente. Aquí tienes los detalles:</p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;">
                                    <strong style="color:#187c3b;display:block;">Cancha</strong>
                                    <span>{{ $cancha->nombre ?? 'No especificada' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;margin-top:12px;">
                                    <strong style="color:#187c3b;display:block;">Fecha</strong>
                                    <span>{{ \Illuminate\Support\Carbon::parse($reserva->fecha)->translatedFormat('d \\d\\e F Y') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;margin-top:12px;">
                                    <strong style="color:#187c3b;display:block;">Horario</strong>
                                    <span>{{ $reserva->hora }} - {{ $reserva->hora_fin }} ({{ $reserva->cantidad_horas }} h)</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;margin-top:12px;">
                                    <strong style="color:#187c3b;display:block;">Estado</strong>
                                    <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $reserva->estado ?? 'pendiente') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;margin-top:12px;">
                                    <strong style="color:#187c3b;display:block;">Total</strong>
                                    <span>S/ {{ number_format($reserva->precio_por_cancha * max(1, $reserva->cantidad_horas), 2) }}</span>
                                </td>
                            </tr>
                        </table>

                        @if ($reserva->payment_link)
                            <p style="margin:20px 0 0 0;">
                                <a href="{{ $reserva->payment_link }}" style="display:inline-block;background:#23a15a;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:600;">Completar pago</a>
                            </p>
                            <p style="margin:12px 0 0 0;color:#5a5f5c;">Si ya realizaste el pago, ignora este mensaje. Verificaremos tu comprobante y te notificaremos.</p>
                        @else
                            <p style="margin:20px 0 0 0;color:#5a5f5c;">No se requiere pago adicional para esta reserva.</p>
                        @endif

                        <p style="margin:20px 0 0 0;color:#0f2b1a;font-weight:600;">Gracias por confiar en SportReserve 💚</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

