<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nuevo inicio de sesión</title>
</head>

<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color:#f4f6f8; color:#0f2b1a; margin:0; padding:0;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding:2rem 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation"
                    style="background-color:#ffffff;border-radius:16px;padding:2rem; box-shadow:0 18px 35px rgba(15,43,26,0.08);">
                    <tr>
                        <td>
                            <h2 style="margin:0 0 0.5rem 0;">Hola, {{ $user->name }}</h2>
                            <p style="margin:0 0 1.5rem 0; color:#5a5f5c;">Se detectó un nuevo inicio de sesión en el panel
                                administrativo de SportReserve.</p>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                style="border-collapse:collapse;margin-bottom:1.5rem;">
                                <tr>
                                    <td style="padding:0.75rem 1rem;border:1px solid #e3e7e4;border-radius:12px;">
                                        <strong style="display:block;color:#187c3b;">Dirección IP</strong>
                                        <span style="color:#363a38;">{{ $ipAddress ?: 'No disponible' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0.75rem 1rem;border:1px solid #e3e7e4;border-radius:12px;margin-top:0.75rem;">
                                        <strong style="display:block;color:#187c3b;">Dispositivo / Navegador</strong>
                                        <span style="color:#363a38;">{{ $userAgent ?: 'No disponible' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0.75rem 1rem;border:1px solid #e3e7e4;border-radius:12px;margin-top:0.75rem;">
                                        <strong style="display:block;color:#187c3b;">Fecha y hora</strong>
                                        <span style="color:#363a38;">{{ $loginTime->format('d/m/Y H:i:s') }} (hora del
                                            servidor)</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 1rem 0; color:#5a5f5c;">
                                Si no reconoces este inicio de sesión, te recomendamos cambiar la contraseña de inmediato
                                y notificar al equipo de soporte.
                            </p>

                            <p style="margin:0; color:#363a38;">— Equipo SportReserve</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
