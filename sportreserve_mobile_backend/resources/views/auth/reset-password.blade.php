<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - SportReserve</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f9f6, #e8f5ed);
            color: #0f2b1a;
        }
        .auth-card {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(17, 94, 50, 0.12);
            padding: 32px;
            border: 1px solid #e2e8f0;
        }
        .brand {
            text-align: center;
            margin-bottom: 24px;
        }
        .brand h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f8a4b;
        }
        .form-control:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.15);
        }
        .btn-emerald {
            background-color: #16a34a;
            border-color: #16a34a;
            color: #fff;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-emerald:hover {
            background-color: #0f8a3c;
            border-color: #0f8a3c;
        }
        .text-muted {
            color: #6b7280 !important;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand">
            <div class="mb-2">
                <i class="bi bi-shield-lock-fill text-success" style="font-size: 2rem;"></i>
            </div>
            <h1>SportReserve</h1>
            <p class="text-muted mb-0">Restablece tu contraseña</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Nueva contraseña</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="mb-4">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn btn-emerald">
                <i class="bi bi-arrow-repeat me-1"></i> Restablecer contraseña
            </button>
        </form>

        <div class="mt-4 text-center">
            <small class="text-muted">Si no solicitaste este cambio, puedes ignorar este mensaje.</small>
        </div>
    </div>
</body>
</html>
