<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad administrativa</title>
</head>

<body style="font-family:'Segoe UI',Arial,sans-serif;background:#f4f6f8;color:#0f2b1a;margin:0;padding:0;">
    <table width="100%" role="presentation" cellpadding="0" cellspacing="0" style="padding:2rem 0;">
        <tr>
            <td align="center">
                <table width="600" role="presentation" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:18px;padding:2rem;box-shadow:0 18px 35px rgba(15,43,26,0.08);">
                    <tr>
                        <td>
                            <p style="margin:0 0 0.5rem 0;font-size:0.9rem;text-transform:uppercase;color:#5a5f5c;">
                                Notificación automática · SportReserve
                            </p>
                            <h2 style="margin:0 0 0.5rem 0;">{{ $action }}</h2>
                            <p style="margin:0 0 1.5rem 0;color:#363a38;">{{ $body }}</p>

                            @if (!empty($details))
                                <table width="100%" role="presentation" cellpadding="0" cellspacing="0"
                                    style="border-collapse:collapse;margin-bottom:1.5rem;">
                                    @foreach ($details as $label => $value)
                                        <tr>
                                            <td style="padding:0.6rem 0.8rem;border:1px solid #e3e7e4;border-radius:10px;">
                                                <strong style="display:block;color:#187c3b;">{{ $label }}</strong>
                                                <span style="color:#0f2b1a;">{{ $value }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:0.6rem;"></td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <p style="margin:0 0 0.5rem 0;color:#5a5f5c;">
                                Operador: {{ $actor?->name ?? 'Sistema' }} ({{ $actor?->email ?? 'N/A' }})
                            </p>
                            <p style="margin:0;color:#5a5f5c;">
                                Fecha del registro: {{ $timestamp->format('d/m/Y H:i:s') }} (hora del servidor)
                            </p>

                            <p style="margin:1.5rem 0 0 0;color:#9aa19d;font-size:0.85rem;">
                                Si no reconoces esta actividad, cambia la contraseña y contacta con el soporte de inmediato.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
