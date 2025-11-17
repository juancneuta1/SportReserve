@extends('admin.layouts.app')

@section('title', 'Comprobantes - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Pagos</p>
            <h1 class="mb-1">Comprobantes de pago</h1>
            <p class="mb-0">Valida las referencias enviadas por los usuarios.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-soft-gray mt-3 mt-md-0">
            <i class="bi bi-arrow-left-circle"></i> Volver
        </a>
    </div>

    <div class="admin-card">
        @if (session('status'))
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('status') }}
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover align-middle table-modern text-center">
                <thead>
                    <tr>
                        <th class="text-uppercase small">ID</th>
                        <th class="text-uppercase small">Usuario</th>
                        <th class="text-uppercase small">Reserva</th>
                        <th class="text-uppercase small">Referencia</th>
                        <th class="text-uppercase small">Estado</th>
                        <th class="text-uppercase small">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservas as $reserva)
                        <tr>
                            <td class="text-muted">#{{ $reserva->id }}</td>
                            <td>{{ $reserva->user->name ?? '—' }}</td>
                            <td>{{ $reserva->cancha->nombre ?? '—' }}</td>
                            <td class="fw-semibold">{{ $reserva->payment_reference ?? 'N/A' }}</td>
                            <td>
                                @if ($reserva->estado === 'confirmada')
                                    <span class="status-badge status-success">Confirmada</span>
                                @elseif ($reserva->estado === 'cancelada')
                                    <span class="status-badge status-danger">Rechazada</span>
                                @else
                                    <span class="status-badge status-warning">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <form action="{{ route('admin.comprobantes.validar', $reserva->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="accion" value="aprobar">
                                        <button type="submit" class="btn btn-sm btn-emerald px-3">
                                            <i class="bi bi-check-circle"></i> Aprobar
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.comprobantes.validar', $reserva->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="accion" value="rechazar">
                                        <button type="submit" class="btn btn-sm btn-danger-soft px-3">
                                            <i class="bi bi-x-circle"></i> Rechazar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-muted">
                                No hay comprobantes pendientes por validar.
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
