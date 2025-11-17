@extends('admin.layouts.app')

@section('title', 'Detalle de usuario - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Usuarios</p>
            <h1 class="mb-1">Detalle de {{ $usuario->name }}</h1>
            <p class="mb-0">Consulta la actividad y edita los datos del perfil seleccionado.</p>
        </div>
        <a href="{{ route('admin.usuarios') }}" class="btn btn-soft-gray mt-3 mt-md-0">
            <i class="bi bi-arrow-left-circle"></i> Volver al listado
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="admin-card h-100">
                <h5 class="fw-bold mb-3">Actividad reciente</h5>
                <p class="mb-1"><strong>Reservas registradas:</strong> {{ $usuario->reservas_count }}</p>
                <p class="mb-1"><strong>Calificaciones enviadas:</strong> {{ $usuario->calificaciones_count }}</p>
                <p class="mb-1">
                    <strong>Último inicio de sesión:</strong>
                    {{ $usuario->last_login_at ? $usuario->last_login_at->format('d/m/Y H:i') : 'Sin datos' }}
                </p>
                <div class="mt-3">
                    <strong>Última cancha reservada:</strong>
                    @if ($ultimaReserva)
                        <div class="mt-2">
                            <p class="mb-1">{{ $ultimaReserva->cancha->nombre ?? 'Sin nombre' }}</p>
                            <small class="text-muted">
                                Fecha: {{ $ultimaReserva->fecha }} · Hora: {{ $ultimaReserva->hora }} – {{ $ultimaReserva->hora_fin }}
                            </small>
                        </div>
                    @else
                        <p class="text-muted mb-0">Sin reservas registradas.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="admin-card h-100">
                <h5 class="fw-bold mb-3">Editar datos del usuario</h5>

                @if (session('status'))
                    <div class="alert alert-success mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nombre completo</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $usuario->name) }}"
                            required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Correo</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $usuario->email) }}"
                            required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Rol</label>
                        <select name="role" class="form-select" required>
                            <option value="user" @selected(old('role', $usuario->role) === 'user')>Usuario</option>
                            <option value="admin" @selected(old('role', $usuario->role) === 'admin')>Administrador</option>
                        </select>
                        @error('role')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Contraseña del administrador</label>
                        <input type="password" name="admin_password" class="form-control" required>
                        <small class="text-muted">Requerido para confirmar cambios.</small>
                        @error('admin_password')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-emerald px-4">
                            <i class="bi bi-save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
