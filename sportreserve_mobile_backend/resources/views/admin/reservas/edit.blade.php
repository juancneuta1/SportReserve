@extends('admin.layouts.app')

@section('title', 'Editar reserva - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Reservas</p>
            <h1 class="mb-1">Editar reserva #{{ $reserva->id }}</h1>
            <p class="mb-0">Actualiza la informaci�n principal de la reserva seleccionada.</p>
        </div>
        <a href="{{ route('admin.reservas.index') }}" class="btn btn-soft-gray mt-3 mt-md-0">
            <i class="bi bi-arrow-left-circle"></i> Volver a reservas
        </a>
    </div>

    @php
        $fechaValue = old('fecha', $reserva->fecha ? \Carbon\Carbon::parse($reserva->fecha)->format('Y-m-d') : '');
        $horaValue = old('hora', $reserva->hora ? \Carbon\Carbon::parse($reserva->hora)->format('H:i') : '');
        $cantidadHorasValue = old('cantidad_horas', $reserva->cantidad_horas);
        $precioValue = old('precio_por_cancha', $reserva->precio_por_cancha);
        $estadoValue = old('estado', $reserva->estado);
    @endphp

    <div class="admin-card">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.reservas.update', $reserva) }}" method="POST" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label fw-semibold">Cancha</label>
                <select name="cancha_id" class="form-select" required>
                    @foreach ($canchas as $cancha)
                        <option value="{{ $cancha->id }}" @selected(old('cancha_id', $reserva->cancha_id) == $cancha->id)>
                            {{ $cancha->nombre }} - {{ $cancha->ubicacion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ $fechaValue }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Hora inicio</label>
                <input type="time" name="hora" class="form-control" value="{{ $horaValue }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Horas</label>
                <input type="number" min="1" max="5" name="cantidad_horas" class="form-control"
                    value="{{ $cantidadHorasValue }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Precio por cancha</label>
                <input type="number" min="0" step="0.01" name="precio_por_cancha" class="form-control"
                    value="{{ $precioValue }}">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Estado</label>
                <select name="estado" class="form-select" required>
                    @foreach (['pendiente', 'pendiente_validacion', 'en_espera', 'confirmada', 'cancelada'] as $estado)
                        <option value="{{ $estado }}" @selected($estadoValue === $estado)>
                            {{ ucfirst(str_replace('_', ' ', $estado)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-emerald px-4">
                    <i class="bi bi-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
