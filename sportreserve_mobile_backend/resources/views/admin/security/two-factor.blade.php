@extends('admin.layouts.app')

@section('title', 'Doble factor - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Seguridad</p>
            <h1 class="mb-1">Doble factor de autenticación</h1>
            <p class="mb-0">Protege tu cuenta de administrador activando Google Authenticator.</p>
        </div>
        <a href="{{ route('admin.security.index') }}" class="btn btn-soft-gray mt-3 mt-md-0">
            <i class="bi bi-arrow-left-circle"></i> Volver
        </a>
    </div>

    {{-- Mensajes de estado --}}
    @if (session('status'))
        <div class="alert alert-success admin-card py-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    {{-- Panel principal --}}
    <div class="admin-card">
        <div class="row g-4">
            {{-- Columna izquierda: estado actual --}}
            <div class="col-md-6">
                <h5 class="fw-bold mb-3">Estado actual</h5>
                <p class="mb-1"><strong>Correo:</strong> {{ $user->email }}</p>
                <p class="mb-1">
                    <strong>2FA:</strong>
                    @if ($user->two_factor_verified)
                        <span class="badge bg-success-subtle text-success">Activo</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                    @endif
                </p>
                <p class="text-muted small">
                    Recomendamos mantener el 2FA activo para tu cuenta administrativa.
                </p>

                <div class="d-flex gap-2 mt-3">
                    {{-- Botón generar secreto --}}
                    <form method="POST" action="{{ route('admin.two-factor.enable') }}">
                        @csrf
                        <button type="submit" class="btn btn-emerald" {{ $user->two_factor_secret ? 'disabled' : '' }}>
                            <i class="bi bi-shield-plus"></i> Generar secreto
                        </button>
                    </form>

                    {{-- Botón desactivar --}}
                    @if ($user->two_factor_secret)
                        <form method="POST" action="{{ route('admin.two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger-soft">
                                <i class="bi bi-shield-x"></i> Desactivar
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Columna derecha: configuración de app --}}
            <div class="col-md-6">
                <h5 class="fw-bold mb-3">Configura tu app</h5>

                @if ($qrCode)
                    <div class="text-center mb-3">
                        {{-- Render del código QR (SVG inline) --}}
                        <div class="d-flex justify-content-center mb-2">
                            {!! $qrCode !!}
                        </div>
                        <p class="text-muted small mb-0">Escanea el código con Google Authenticator.</p>
                    </div>

                    {{-- Formulario de confirmación --}}
                    <form method="POST" action="{{ route('admin.two-factor.confirm') }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="code" class="form-control text-center" maxlength="6"
                            placeholder="Código de 6 dígitos" required>
                        <button type="submit" class="btn btn-emerald">
                            <i class="bi bi-check-circle"></i> Confirmar
                        </button>
                    </form>

                    @error('code')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                @else
                    <p class="text-muted">
                        Haz clic en <strong>“Generar secreto”</strong> para iniciar la configuración.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Códigos de recuperación --}}
    @if (!empty($recoveryCodes))
        <div class="admin-card mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Códigos de recuperación</h5>
                    <p class="text-muted small mb-0">
                        Guárdalos en un lugar seguro. Permiten acceder si pierdes el móvil.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.two-factor.recovery') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-emerald btn-sm">
                        <i class="bi bi-arrow-repeat"></i> Regenerar
                    </button>
                </form>
            </div>

            <div class="row g-2">
                @foreach ($recoveryCodes as $code)
                    <div class="col-md-3">
                        <div class="border rounded px-3 py-2 text-center fw-semibold bg-light">
                            {{ $code }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
