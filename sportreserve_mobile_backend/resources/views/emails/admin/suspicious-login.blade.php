<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión detectado</title>
</head>

<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color:#f4f6f8; color:#0f2b1a; margin:0;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding:2rem 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation"
                    style="background-color:#ffffff;border-radius:16px;padding:2rem; box-shadow:0 18px 35px rgba(15,43,26,0.08);">
                    <tr>
                        <td>
                            <h2 style="margin:0 0 0.5rem 0;">Hola, {{ $user->name }}</h2>
                            <p style="margin:0 0 1.5rem 0;">Se registró un inicio de sesión en tu cuenta administrativa de
                                SportReserve.</p>
                            <p style="margin:0 0 0.5rem 0;"><strong>IP:</strong> {{ $ip ?? 'No disponible' }}</p>
                            <p style="margin:0 0 0.5rem 0;"><strong>Ubicación:</strong> {{ $location ?? 'Sin datos' }}</p>
                            <p style="margin:0 0 0.5rem 0;"><strong>Dispositivo:</strong> {{ $device ?? 'Sin datos' }}</p>
                            <p style="margin:1rem 0 0 0;color:#5a5f5c;">Si no reconoces este acceso, cambia tu contraseña y
                                activa el doble factor de autenticación de inmediato.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
