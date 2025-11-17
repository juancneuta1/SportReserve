@extends('admin.layouts.app')

@section('title', 'Reservas - SportReserve')

@section('content')
    @php
        $hasEditRoute = \Illuminate\Support\Facades\Route::has('admin.reservas.edit');
        $hasCancelRoute = \Illuminate\Support\Facades\Route::has('admin.reservas.cancel');
        $hasHoldRoute = \Illuminate\Support\Facades\Route::has('admin.reservas.hold');
    @endphp

    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Agenda</p>
            <h1 class="mb-1">Listado de reservas</h1>
            <p class="mb-0">Controla las reservas activas y toma acciones inmediatas.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-soft-gray mt-3 mt-md-0">
            <i class="bi bi-arrow-left-circle"></i> Volver
        </a>
    </div>

    <div class="admin-card">
        @if (session('status'))
            <div class="alert alert-success mb-4">
                {{ session('status') }}
            </div>
        @endif

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h5 class="mb-1 fw-semibold">Resumen rapido</h5>
                <p class="text-muted mb-0">
                    Total: {{ $reservas->count() }} reservas
                    &middot; Confirmadas: {{ $reservas->where('estado', 'confirmada')->count() }}
                    &middot; Pendientes: {{ $reservas->whereIn('estado', ['pendiente', 'pendiente_validacion', 'en_espera'])->count() }}
                </p>
            </div>
            @if (! $hasEditRoute || ! $hasCancelRoute || ! $hasHoldRoute)
                <div class="alert alert-warning py-2 px-3 mb-0">
                    Configura las rutas <code>admin.reservas.*</code> para habilitar todas las acciones.
                </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-modern">
                <thead>
                    <tr>
                        <th class="text-uppercase small">#</th>
                        <th class="text-uppercase small">Usuario</th>
                        <th class="text-uppercase small">Cancha</th>
                        <th class="text-uppercase small">Horario</th>
                        <th class="text-uppercase small text-center">Estado</th>
                        <th class="text-uppercase small text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservas as $reserva)
                        @php
                            $estado = strtolower($reserva->estado ?? 'pendiente');
                            $badgeClass = match ($estado) {
                                'confirmada' => 'status-success',
                                'cancelada' => 'status-danger',
                                'pendiente', 'pendiente_validacion', 'en_espera' => 'status-warning',
                                default => 'status-warning',
                            };
                            $estadoLabel = ucfirst(str_replace('_', ' ', $estado));
                            $fechaFormateada = $reserva->fecha ? \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') : 'Sin fecha';
                            $horaInicio = $reserva->hora ?? '--:--';
                            $horaFin = $reserva->hora_fin ?? '--:--';
                        @endphp
                        <tr>
                            <td class="text-muted">#{{ $reserva->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $reserva->user->name ?? 'Sin usuario' }}</div>
                                <small class="text-muted">{{ $reserva->user->email ?? 'Sin correo' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $reserva->cancha->nombre ?? 'Sin cancha' }}</div>
                                <small class="text-muted">{{ $reserva->cancha->ubicacion ?? '' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $fechaFormateada }}
                                </div>
                                <small class="text-muted">
                                    {{ $horaInicio }} - {{ $horaFin }} ({{ $reserva->cantidad_horas }} h)
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="status-badge {{ $badgeClass }}">{{ $estadoLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end flex-wrap gap-2">
                                    @if ($hasEditRoute)
                                        <a href="{{ route('admin.reservas.edit', $reserva) }}" class="btn btn-sm btn-outline-emerald">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>
                                    @endif

                                    @if ($hasHoldRoute)
                                        <form action="{{ route('admin.reservas.hold', $reserva) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-warning-soft">
                                                <i class="bi bi-pause-circle"></i> En espera
                                            </button>
                                        </form>
                                    @endif

                                    @if ($hasCancelRoute)
                                        <form action="{{ route('admin.reservas.cancel', $reserva) }}" method="POST"
                                            onsubmit="return confirm('Deseas cancelar esta reserva?');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-danger-soft">
                                                <i class="bi bi-x-circle"></i> Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-muted">
                                Aun no hay reservas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .btn-danger-soft {
            background: rgba(229, 72, 77, 0.12);
            color: #b42318;
            border: 1px solid rgba(229, 72, 77, 0.25);
            border-radius: 999px;
        }

        .btn-danger-soft:hover {
            background: rgba(229, 72, 77, 0.18);
            color: #a01f15;
        }

        .btn-warning-soft {
            background: rgba(246, 193, 65, 0.18);
            color: #a86200;
            border: 1px solid rgba(246, 193, 65, 0.35);
            border-radius: 999px;
        }

        .btn-warning-soft:hover {
            background: rgba(246, 193, 65, 0.24);
            color: #905200;
        }
    </style>
@endsection
