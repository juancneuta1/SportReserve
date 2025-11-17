<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo inicio de sesión</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;color:#0f2b1a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;padding:32px;box-shadow:0 18px 35px rgba(15,43,26,0.08);">
                <tr>
                    <td>
                        <h2 style="margin:0 0 12px 0;">Hola, {{ $user->name }}</h2>
                        <p style="margin:0 0 16px 0;color:#5a5f5c;">Detectamos un nuevo inicio de sesión en tu cuenta de SportReserve.</p>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;">
                                    <strong style="color:#187c3b;display:block;">Dirección IP</strong>
                                    <span>{{ $ipAddress ?? 'No disponible' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;margin-top:12px;">
                                    <strong style="color:#187c3b;display:block;">Dispositivo / Navegador</strong>
                                    <span>{{ $userAgent ?? 'Sin datos' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;border:1px solid #e3e7e4;border-radius:12px;margin-top:12px;">
                                    <strong style="color:#187c3b;display:block;">Fecha</strong>
                                    <span>{{ optional($loginTime)->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</span>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:20px 0 0 0;color:#5a5f5c;">Si no reconoces este acceso te recomendamos cambiar tu contraseña y cerrar las demás sesiones desde la app.</p>
                        <p style="margin:12px 0 0 0;color:#0f2b1a;font-weight:600;">Equipo SportReserve</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

