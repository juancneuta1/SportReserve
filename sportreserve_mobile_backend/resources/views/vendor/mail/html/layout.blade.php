<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        :root {
            --emerald: #16a34a;
            --emerald-dark: #0f8a3c;
            --bg: #f2f8f4;
            --text: #0f2b1a;
            --muted: #6b7280;
            --card: #ffffff;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at 20% 20%, #e9f7ef 0%, #f5f9f6 45%),
                        radial-gradient(circle at 80% 0%, #e4f3ed 0%, #f5f9f6 40%),
                        var(--bg);
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: var(--text);
        }
        .wrapper {
            width: 100%;
            padding: 36px 12px;
        }
        .content {
            max-width: 620px;
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
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #d1fadf;
            color: var(--emerald-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 21px;
            font-weight: 800;
            color: var(--emerald-dark);
            letter-spacing: 0.3px;
        }
        .body {
            padding: 24px 28px 30px;
            color: var(--text);
            font-size: 16px;
            line-height: 1.6;
        }
        .body p { margin: 0 0 14px 0; }
        .body h1, .body h2, .body h3 { color: var(--text); margin: 0 0 12px 0; }
        .panel {
            background: #f8fbf9;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            margin: 16px 0;
            color: var(--muted);
        }
        a { color: var(--emerald-dark); }
        .footer {
            padding: 16px 28px 24px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            border-top: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="content">
            <div class="header">
                <div class="logo-circle">
                    {{ \Illuminate\Support\Str::of(config('app.name', 'SR'))->substr(0, 2)->upper() }}
                </div>
                <h1>{{ config('app.name', 'SportReserve') }}</h1>
            </div>

            <div class="body">
                {{ $slot }}
            </div>

            <div class="footer">
                {{ $subcopy ?? '' }}
                <p style="margin: 8px 0 0 0;">© {{ date('Y') }} {{ config('app.name', 'SportReserve') }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
