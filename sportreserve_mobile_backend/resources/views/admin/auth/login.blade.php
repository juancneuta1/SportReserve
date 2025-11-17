<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingreso administrativo | SportReserve</title>
    <link rel="icon" type="image" href="{{ asset('images/logo-admin.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- 🔒 Importante para validar CSRF --}}

    <style>
        :root {
            --emerald-900: #0f2b1a;
            --emerald-700: #187c3b;
            --emerald-500: #23a15a;
            --sage-100: #ebf6ef;
            --gray-500: #6b6f6d;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: radial-gradient(circle at top, rgba(35, 161, 90, 0.28), transparent 45%),
                linear-gradient(180deg, #f5fbf7 0%, #e5f4eb 100%);
            font-family: 'Inter', 'Poppins', sans-serif;
            color: var(--emerald-900);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-shell {
            width: 100%;
            max-width: 480px;
        }

        .brand-card {
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(14px);
            box-shadow: 0 25px 60px rgba(7, 35, 20, 0.15);
            border: 1px solid rgba(15, 43, 26, 0.08);
            padding: 3rem 2.5rem;
        }

        .brand-badge {
            width: 150px;
            height: 150px;
            border-radius: 26px;
            background-color: #fff;
            border: 2px solid rgba(35, 161, 90, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            padding: 0.5rem;
            box-shadow: 0 14px 34px rgba(15, 43, 26, 0.24);
        }

        .brand-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 22px;
            border: 2px solid rgba(35, 161, 90, 0.35);
            box-shadow: inset 0 0 12px rgba(0, 0, 0, 0.08);
        }

        .brand-card h1 {
            font-size: 1.9rem;
            font-weight: 700;
        }

        .form-floating label {
            color: var(--gray-500);
        }

        .btn-emerald {
            background: var(--emerald-500);
            border: none;
            border-radius: 999px;
            font-weight: 600;
            padding: 0.85rem 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-emerald:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 20px rgba(35, 161, 90, 0.35);
        }

        .alert {
            border-radius: 14px;
        }
    </style>
</head>

<body>
    <div class="login-shell">
        <div class="brand-card">
            <div class="brand-badge">
                <img src="{{ asset('images/logo-admin.png') }}" alt="SportReserve icono">
            </div>
            <div class="text-center">
                <p class="text-uppercase fw-semibold text-muted mb-1">SportReserve</p>
                <h1 class="mb-1">Panel administrativo</h1>
                <p class="text-muted mb-4">Accede con tus credenciales para gestionar las operaciones.</p>
            </div>

            {{-- ⚠️ Mostrar errores de validación --}}
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- ✅ Formulario con token CSRF --}}
            <form method="POST" action="{{ route('admin.login.post') }}" class="mt-4">
                @csrf
                <div class="form-floating mb-3">
                    <input type="email" name="email" id="email" class="form-control" placeholder="correo@example.com"
                        value="{{ old('email') }}" required autofocus>
                    <label for="email">Correo corporativo</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Contraseña" required>
                    <label for="password">Contraseña</label>
                </div>

                <button type="submit" class="btn btn-emerald w-100">Ingresar</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</body>

</html>
