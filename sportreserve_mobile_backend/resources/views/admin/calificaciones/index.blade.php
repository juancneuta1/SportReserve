@extends('admin.layouts.app')

@section('title', 'Calificaciones de Canchas - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Reportes</p>
            <h1 class="mb-1">📊 Calificaciones de Canchas</h1>
            <p class="mb-0">Monitorea el rendimiento y prestigio de cada cancha según los usuarios.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-soft-gray mt-3 mt-md-0">
            <i class="bi bi-arrow-left-circle"></i> Volver al panel
        </a>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-modern">
                <thead>
                    <tr>
                        <th class="text-uppercase small">ID</th>
                        <th class="text-uppercase small">Nombre</th>
                        <th class="text-uppercase small">Promedio</th>
                        <th class="text-uppercase small text-center">Total votos</th>
                        <th class="text-uppercase small text-center">Estado</th>
                        <th class="text-uppercase small text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($canchas as $cancha)
                        @php
                            $promedio = round($cancha->promedio_estrellas ?? 0, 2);
                            $total = $cancha->total_calificaciones ?? 0;
                            $estado = 'Regular';
                            $estadoBadge = 'status-warning';

                            if ($promedio >= 4.5 && $total >= 20) {
                                $estado = 'Cancha Top';
                                $estadoBadge = 'status-success';
                            } elseif ($promedio >= 3.8) {
                                $estado = 'Popular';
                                $estadoBadge = 'status-success';
                            } elseif ($total < 5) {
                                $estado = 'Novedad';
                                $estadoBadge = 'status-warning';
                            }
                        @endphp
                        <tr>
                            <td class="text-muted">#{{ $cancha->id }}</td>
                            <td class="fw-semibold">{{ $cancha->nombre }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold">{{ number_format($promedio, 2) }}</span>
                                    <div>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= round($promedio) ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $total }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ $estadoBadge }}">
                                    {{ $estado }} @if ($estado === 'Cancha Top') ⭐ @endif
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-outline-emerald btn-sm">
                                    <i class="bi bi-gift"></i> Premiar / Agregar Novedad
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-muted">
                                No hay calificaciones registradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
