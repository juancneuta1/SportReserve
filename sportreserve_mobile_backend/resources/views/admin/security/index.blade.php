@extends('admin.layouts.app')

@section('title', 'Seguridad - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Seguridad</p>
            <h1 class="mb-1">Panel de seguridad</h1>
            <p class="mb-0">Administra tus sesiones activas y revisa el historial de accesos.</p>
        </div>
        <div class="d-flex flex-column flex-md-row gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.two-factor.index') }}" class="btn btn-outline-emerald">
                <i class="bi bi-shield-lock"></i> Doble factor
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-soft-gray">
                <i class="bi bi-arrow-left-circle"></i> Volver
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success admin-card py-3 mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="admin-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1">Sesiones activas</h5>
                <p class="text-muted mb-0 small">Gestiona desde dónde tienes sesión abierta.</p>
            </div>
            <form method="POST" action="{{ route('admin.security.sessions.flush') }}">
                @csrf
                <button type="submit" class="btn btn-danger-soft">
                    <i class="bi bi-power"></i> Cerrar otras sesiones
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Dispositivo</th>
                        <th>IP</th>
                        <th>Última actividad</th>
                        <th>Actual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->device ?? 'Sin datos' }}</td>
                            <td>{{ $session->ip ?? 'N/A' }}</td>
                            <td>{{ $session->last_activity_at?->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if ($session->session_id === session()->getId())
                                    <span class="badge bg-success-subtle text-success">Esta sesión</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Otra sesión</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Sin sesiones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <h5 class="fw-bold mb-3">Historial de accesos</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>IP</th>
                        <th>Ubicación</th>
                        <th>Dispositivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->logged_in_at?->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->ip ?? 'N/A' }}</td>
                            <td>{{ $log->location ?? 'Sin datos' }}</td>
                            <td>{{ $log->device ?? 'Desconocido' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Todavía no hay accesos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
