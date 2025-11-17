<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel SportReserve')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image" href="{{ asset('images/logo-admin.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --emerald-900: #0f2b1a;
            --emerald-700: #187c3b;
            --emerald-500: #23a15a;
            --emerald-100: #e4f5ea;
            --sage-50: #f7fbf7;
            --gray-700: #363a38;
            --gray-600: #5a5f5c;
            --gray-200: #e3e7e4;
            --gray-100: #f2f4f2;

            --panel-bg: #f7fbf7;
            --panel-card: #ffffff;
            --panel-text: var(--emerald-900);
            --panel-muted: var(--gray-600);
            --panel-border: var(--gray-200);
        }

        body[data-theme='dark'] {
            --panel-bg: #0a101f;
            --panel-card: #131b2f;
            --panel-text: #f8fafc;
            --panel-muted: #cbd5f5;
            --panel-border: #202b44;
            --emerald-100: rgba(35, 161, 90, 0.3);
        }

        body {
            background: var(--panel-bg);
            color: var(--panel-text);
            font-family: 'Inter', 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background 0.25s ease, color 0.25s ease;
        }

        nav.navbar {
            background: var(--panel-card);
            border-bottom: 1px solid var(--panel-border);
            box-shadow: 0 8px 24px rgba(15, 43, 26, 0.05);
            padding: 0.65rem 0;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .navbar-brand {
            color: var(--emerald-700) !important;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            gap: 0.4rem;
            align-items: center;
            letter-spacing: 0.3px;
        }

        body[data-theme='dark'] .navbar-brand {
            color: #f8fafc !important;
        }

        body[data-theme='dark'] .page-header h1,
        body[data-theme='dark'] h5,
        body[data-theme='dark'] h4,
        body[data-theme='dark'] h3 {
            color: #f8fafc;
        }

        body[data-theme='dark'] .stat-card p,
        body[data-theme='dark'] .admin-card p,
        body[data-theme='dark'] .text-muted {
            color: #d1d5db !important;
        }

        .theme-toggle {
            border: 1px solid var(--panel-border);
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            background: transparent;
            color: var(--panel-text);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .theme-toggle:hover {
            background: var(--emerald-500);
            color: #fff;
            border-color: transparent;
        }

        .navbar .btn-logout {
            color: var(--emerald-700);
            border: 1px solid var(--emerald-500);
            border-radius: 999px;
            padding: 0.4rem 1.2rem;
            font-weight: 600;
            background-color: transparent;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        body[data-theme='dark'] .btn-logout {
            color: #f8fafc;
            border-color: rgba(255, 255, 255, 0.35);
        }

        .navbar .btn-logout:hover {
            background-color: var(--emerald-500);
            color: #fff;
            border-color: var(--emerald-500);
        }

        main.container {
            flex: 1;
            width: 100%;
            max-width: 1440px;
            padding-top: 100px;
            padding-bottom: 80px;
            animation: fadeIn 0.35s ease;
        }

        footer {
            background: var(--panel-card);
            color: var(--panel-muted);
            text-align: center;
            padding: 12px 0;
            font-size: 0.9rem;
            border-top: 1px solid var(--panel-border);
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--panel-text);
        }

        .page-header p {
            color: var(--panel-muted);
            margin-bottom: 0;
        }

        .admin-card {
            background: var(--panel-card);
            border-radius: 18px;
            border: 1px solid var(--panel-border);
            box-shadow: 0 12px 40px rgba(15, 43, 26, 0.05);
            padding: 1.75rem;
            transition: background 0.25s ease, color 0.25s ease;
        }

        .stat-card {
            border: 1px solid var(--panel-border);
            border-radius: 16px;
            padding: 1.5rem;
            background: var(--panel-card);
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(15, 43, 26, 0.08);
        }

        .btn-emerald {
            background: var(--emerald-500);
            color: #fff;
            border-radius: 999px;
            border: none;
            padding: 0.55rem 1.4rem;
            font-weight: 600;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .btn-emerald:hover {
            box-shadow: 0 10px 20px rgba(35, 161, 90, 0.35);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-outline-emerald {
            background: transparent;
            color: var(--emerald-700);
            border: 1px solid var(--emerald-500);
            border-radius: 999px;
            padding: 0.55rem 1.4rem;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease;
        }

        body[data-theme='dark'] .btn-outline-emerald {
            color: #f8fafc;
        }

        .btn-outline-emerald:hover {
            background: var(--emerald-500);
            color: #fff;
        }

        .btn-soft-gray {
            background: var(--gray-100);
            color: var(--emerald-900);
            border-radius: 999px;
            border: 1px solid var(--panel-border);
            padding: 0.55rem 1.4rem;
            font-weight: 600;
        }

        body[data-theme='dark'] .btn-soft-gray {
            background: #1f2937;
            color: var(--panel-text);
        }

        .table-modern {
            border-radius: 14px;
            overflow: hidden;
        }

        .table-modern thead {
            background: var(--emerald-100);
            color: var(--panel-text);
        }

        .table-modern tbody tr td {
            vertical-align: middle;
            color: var(--panel-text);
            background: var(--panel-card);
        }

        body[data-theme='dark'] table.table {
            color: var(--panel-text);
        }

        body[data-theme='dark'] .table-modern thead {
            background: rgba(35, 161, 90, 0.4);
        }

        .status-badge {
            border-radius: 999px;
            padding: 0.35rem 0.95rem;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-success {
            background: rgba(35, 161, 90, 0.12);
            color: var(--emerald-700);
        }

        body[data-theme='dark'] .status-success {
            color: #22c55e;
        }

        .status-warning {
            background: rgba(246, 193, 65, 0.18);
            color: #a86200;
        }

        .status-danger {
            background: rgba(229, 72, 77, 0.15);
            color: #b42318;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <nav class="navbar fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-hexagon-fill text-success"></i> SportReserve Panel
            </a>
            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle" id="themeToggle" type="button">
                    <i class="bi bi-moon-stars"></i>
                    <span>Modo oscuro</span>
                </button>
                <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-logout btn-sm">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container">
        @yield('content')
    </main>

    <footer>
        SportReserve © {{ date('Y') }} — Panel Administrativo
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const body = document.body;
            const button = document.getElementById('themeToggle');
            if (!button) return;

            const storedTheme = localStorage.getItem('admin-theme') || 'light';
            body.setAttribute('data-theme', storedTheme);
            updateToggleText(storedTheme);

            button.addEventListener('click', () => {
                const newTheme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                body.setAttribute('data-theme', newTheme);
                localStorage.setItem('admin-theme', newTheme);
                updateToggleText(newTheme);
            });

            function updateToggleText(theme) {
                const icon = button.querySelector('i');
                const label = button.querySelector('span');

                if (theme === 'dark') {
                    icon.classList.remove('bi-moon-stars');
                    icon.classList.add('bi-sun');
                    label.textContent = 'Modo claro';
                } else {
                    icon.classList.remove('bi-sun');
                    icon.classList.add('bi-moon-stars');
                    label.textContent = 'Modo oscuro';
                }
            }
        })();
    </script>
</body>

</html>
