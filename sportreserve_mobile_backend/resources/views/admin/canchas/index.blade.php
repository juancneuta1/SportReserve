@extends('admin.layouts.app')

@section('title', 'Gestión de Canchas - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1">Administración</p>
            <h1 class="mb-1">Gestión de canchas</h1>
            <p class="mb-0">Administra los espacios deportivos disponibles.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-soft-gray">
                <i class="bi bi-arrow-left-circle"></i> Volver
            </a>
            <a href="{{ route('admin.canchas.create') }}" class="btn btn-emerald">
                <i class="bi bi-plus-circle"></i> Nueva cancha
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-modern">
                <thead>
                    <tr>
                        <th class="text-uppercase small">ID</th>
                        <th class="text-uppercase small">Nombre</th>
                        <th class="text-uppercase small">Ubicación</th>
                        <th class="text-uppercase small">Precio/Hora</th>
                        <th class="text-uppercase small text-center">Disponibilidad</th>
                        <th class="text-uppercase small text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($canchas as $cancha)
                        <tr>
                            <td class="text-muted">#{{ $cancha->id }}</td>
                            <td class="fw-semibold">{{ $cancha->nombre }}</td>
                            <td>{{ $cancha->ubicacion }}</td>
                            <td>${{ number_format($cancha->precio_por_hora, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if ($cancha->disponibilidad)
                                    <span class="status-badge status-success">Disponible</span>
                                @else
                                    <span class="status-badge status-danger">Ocupada</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.canchas.edit', $cancha) }}" class="btn btn-outline-emerald btn-sm me-1">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>
                                <form action="{{ route('admin.canchas.destroy', $cancha) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger-soft">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No hay canchas registradas todavía.
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
    </style>
@endsection
