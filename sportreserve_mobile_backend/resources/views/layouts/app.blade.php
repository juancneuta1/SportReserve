<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel SportReserve</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
        }

        nav.navbar {
            background-color: #2e7d32;
        }

        .navbar-brand,
        .nav-link,
        .navbar-text {
            color: white !important;
        }

        footer {
            background: #2e7d32;
            color: white;
            text-align: center;
            padding: 12px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .container {
            margin-bottom: 60px;
        }
    </style>
</head>

<body>

    <!-- 🔹 Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/admin/canchas') }}">
                🏟️ SportReserve Panel
            </a>
        </div>
    </nav>

    <!-- 🔹 Contenido dinámico -->
    <main class="container mt-4">
        @yield('content')
    </main>

    <!-- 🔹 Footer -->
    <footer>
        SportReserve © {{ date('Y') }} — Panel Administrativo
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>


