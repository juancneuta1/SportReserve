<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña</title>
    <style>
        :root {
            --emerald: #16a34a;
            --emerald-dark: #0f8a3c;
            --bg: #f2f6f4;
            --text: #0f2b1a;
            --muted: #6b7280;
            --card: #ffffff;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 12px;
            background: radial-gradient(circle at 20% 20%, #e9f7ef 0%, #f5f9f6 45%),
                        radial-gradient(circle at 80% 0%, #e4f3ed 0%, #f5f9f6 40%),
                        var(--bg);
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: var(--text);
        }
        .card {
            max-width: 640px;
            margin: 0 auto;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(17, 94, 50, 0.12);
            overflow: hidden;
        }
        .header {
            padding: 28px;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }
        .logo-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(145deg, #d1fadf, #b8f3cc);
            color: var(--emerald-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            margin-bottom: 12px;
            box-shadow: 0 12px 32px rgba(22, 163, 74, 0.18);
        }
        h1,h2,h3,h4 { margin: 0; color: var(--text); }
        .title { margin-top: 0; font-size: 20px; font-weight: 800; }
        .body {
            padding: 24px 28px 30px;
            font-size: 16px;
            line-height: 1.6;
        }
        .body p { margin: 0 0 14px 0; color: var(--text); }
        .panel {
            background: #f8fbf9;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            margin: 16px 0;
            color: var(--muted);
        }
        .btn {
            display: inline-block;
            padding: 12px 26px;
            background: linear-gradient(145deg, #16a34a, #128a3e);
            color: #fff !important;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            border: 1px solid #128a3e;
            box-shadow: 0 10px 24px rgba(22, 163, 74, 0.25);
            letter-spacing: 0.1px;
        }
        .footer {
            padding: 16px 28px 24px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            border-top: 1px solid var(--border);
        }
        a { color: var(--emerald-dark); }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="logo-circle">
                {{ \Illuminate\Support\Str::of(config('app.name', 'SR'))->substr(0, 2)->upper() }}
            </div>
            <div class="title">{{ config('app.name', 'SportReserve') }}</div>
        </div>
        <div class="body">
            <h2 style="margin-bottom: 12px;">Hola{{ $user?->name ? ', '.$user->name : '' }}:</h2>
            <p>Recibimos una solicitud para restablecer tu contraseña. Si fuiste tú, usa el botón de abajo.</p>

            <p style="margin: 18px 0;">
                <a href="{{ $resetUrl }}" class="btn" target="_blank" rel="noopener">Restablecer contraseña</a>
            </p>

            <div class="panel">
                <p style="margin: 0 0 6px 0;"><strong>Válido por:</strong> {{ $expires }} minutos</p>
                <p style="margin: 0;">Si no solicitaste este cambio, puedes ignorar este correo.</p>
            </div>

            <p style="margin-top: 18px; color: var(--muted); font-size: 14px;">
                También puedes copiar y pegar este enlace en tu navegador:<br>
                <a href="{{ $resetUrl }}" target="_blank" rel="noopener">{{ $resetUrl }}</a>
            </p>
        </div>
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name', 'SportReserve') }}. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
