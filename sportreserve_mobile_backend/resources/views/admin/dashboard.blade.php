@extends('admin.layouts.app')

@section('title', 'Panel de Administración - SportReserve')

@section('content')
    <div class="page-header text-center text-md-start">
        <p class="text-muted mb-2">Bienvenido, {{ Auth::user()->name }} 👋</p>
        <h1>Panel de administración</h1>
        <p class="mt-1">Gestiona las canchas, revisa los comprobantes y mantén el panel al día.</p>
    </div>

    @php
        $twoFactorActive = Auth::user()->two_factor_verified ? 'Activo' : 'Desactivado';
    @endphp

    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h5 class="mb-2">Gestión de Canchas</h5>
                    <p class="text-muted mb-0">Crea, edita o desactiva las canchas disponibles.</p>
                </div>
                <a href="{{ route('admin.canchas.index') }}" class="btn btn-emerald w-100">Ir a canchas</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3 text-success">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <h5 class="mb-2">Comprobantes</h5>
                    <p class="text-muted mb-0">Valida las referencias de pago recibidas.</p>
                </div>
                <a href="{{ route('admin.comprobantes.index') }}" class="btn btn-emerald w-100">Ver comprobantes</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3 text-info">
                        <i class="bi bi-calendar2-check"></i>
                    </div>
                    <h5 class="mb-2">Reservas</h5>
                    <p class="text-muted mb-0">Revisa, edita o cancela las reservas activas.</p>
                </div>
                <a href="{{ route('admin.reservas.index') }}" class="btn btn-emerald w-100">Ver reservas</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3 text-warning">
                        <i class="bi bi-star-half"></i>
                    </div>
                    <h5 class="mb-2">Calificaciones</h5>
                    <p class="text-muted mb-0">Consulta el ranking e identifica canchas destacadas.</p>
                </div>
                <a href="{{ route('admin.calificaciones') }}" class="btn btn-emerald w-100">Ver calificaciones</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3 text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5 class="mb-2">Usuarios del Sistema</h5>
                    <p class="text-muted mb-0">Consulta la actividad y registros de la comunidad.</p>
                </div>
                <a href="{{ route('admin.usuarios') }}" class="btn btn-emerald w-100">Ver usuarios</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3 text-success" style="background: rgba(22, 163, 74, 0.12);">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h5 class="mb-1">Seguridad y 2FA</h5>
                    <p class="text-muted mb-2">Supervisa sesiones, logs y configura la autenticación avanzada.</p>
                    <span class="badge {{ Auth::user()->two_factor_verified ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} px-3 py-1 rounded-pill">
                        2FA {{ $twoFactorActive }}
                    </span>
                </div>
                <a href="{{ route('admin.security.index') }}" class="btn btn-emerald w-100 mt-3">Abrir panel</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3 text-info" style="background: rgba(59, 130, 246, 0.12);">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h5 class="mb-2">Comunicaciones internas</h5>
                    <p class="text-muted mb-0">Mensajes entre administradores y alertas del sistema.</p>
                </div>
                <a href="{{ route('admin.comunicaciones.dashboard') }}" class="btn btn-emerald w-100">Abrir módulo</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card h-100">
                <div>
                    <div class="option-icon mb-3 text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <h5 class="mb-2">Cerrar sesión</h5>
                    <p class="text-muted mb-0">Finaliza tu sesión de administrador.</p>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-soft-gray w-100">Salir</button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .option-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: var(--emerald-100);
            color: var(--emerald-700);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .stat-card h5 {
            font-weight: 700;
            color: var(--emerald-900);
        }
    </style>
@endsection
