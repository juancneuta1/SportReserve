<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de seguridad</title>
</head>

<body style="margin:0;padding:0;background:#fff6f5;font-family:'Segoe UI',Arial,sans-serif;color:#3f0d12;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;padding:32px;box-shadow:0 18px 35px rgba(101,16,16,0.08);">
                <tr>
                    <td>
                        <h2 style="margin:0 0 12px 0;">Atención, {{ $user->name }}</h2>
                        <p style="margin:0 0 16px 0;color:#5f1d20;">Registramos un acceso desde una IP distinta a la más reciente.</p>
                        <ul style="list-style:none;padding:0;margin:0 0 16px 0;">
                            <li style="margin-bottom:8px;"><strong>IP:</strong> {{ $ip ?? 'No disponible' }}</li>
                            <li style="margin-bottom:8px;"><strong>Ubicación:</strong> {{ $location ?? 'Sin datos' }}</li>
                            <li style="margin-bottom:8px;"><strong>Dispositivo:</strong> {{ $device ?? 'Desconocido' }}</li>
                        </ul>
                        <p style="margin:0 0 12px 0;color:#5f1d20;">Si no fuiste tú, cambia tu contraseña y cierra todas las sesiones desde la app para mantener tu cuenta protegida.</p>
                        <p style="margin:12px 0 0 0;color:#3f0d12;font-weight:600;">Equipo SportReserve</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>
