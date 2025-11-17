@extends('admin.layouts.app')

@section('title', 'Usuarios del Sistema - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase small">Usuarios</p>
            <h1 class="mb-1">Usuarios del Sistema</h1>
            <p class="mb-0">Consulta el detalle de todos los registros y su actividad reciente.</p>
        </div>
        <div class="d-flex flex-column flex-md-row gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-soft-gray">
                <i class="bi bi-arrow-left-circle"></i> Volver
            </a>
            <button class="btn btn-emerald" type="button" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                <i class="bi bi-person-plus"></i> Crear usuario
            </button>
        </div>
    </div>

    @php
        $generatedCredentials = session('generated_user_credentials');
    @endphp

    @if (session('status'))
        <div class="alert alert-success admin-card py-3 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <strong class="d-block mb-1">{{ session('status') }}</strong>
                    @if ($generatedCredentials)
                        <span class="d-block text-muted small">Entrega estas credenciales al usuario para su primer acceso.</span>
                    @endif
                </div>
                @if ($generatedCredentials)
                    <div class="bg-light rounded-3 px-4 py-3 w-100 w-md-auto">
                        <div class="d-flex flex-column gap-1 small mb-2">
                            <span><strong>Correo:</strong> {{ $generatedCredentials['email'] }}</span>
                            <span><strong>Contraseña inicial:</strong> {{ $generatedCredentials['password'] }}</span>
                        </div>
                        <span class="badge bg-warning-subtle text-warning fw-semibold">Debe cambiarla al iniciar sesión</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="admin-card admin-card-wide">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-modern">
                <thead>
                    <tr>
                        <th class="text-uppercase small">ID</th>
                        <th class="text-uppercase small">Usuario</th>
                        <th class="text-uppercase small">Correo</th>
                        <th class="text-uppercase small">Rol</th>
                        <th class="text-uppercase small text-center">Reservas</th>
                        <th class="text-uppercase small text-center">Calificaciones</th>
                        <th class="text-uppercase small">Registrado</th>
                        <th class="text-uppercase small text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        @php
                            $sequentialId = $usuarios->count() - $loop->index;
                            $deleteModalId = 'modalEliminarUsuario' . $usuario->id;
                            $isCurrentUser = auth()->id() === $usuario->id;
                        @endphp
                        <tr>
                            <td class="text-muted">#{{ $sequentialId }}</td>
                            <td class="fw-semibold">{{ $usuario->name }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>
                                <span class="badge {{ $usuario->role === 'admin' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} fw-semibold px-3 py-2">
                                    {{ $usuario->role === 'admin' ? 'Administrador' : 'Usuario' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success fw-semibold">
                                    {{ $usuario->reservas_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info fw-semibold">
                                    {{ $usuario->calificaciones_count }}
                                </span>
                            </td>
                            <td>{{ $usuario->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.usuarios.show', $usuario) }}" class="btn btn-outline-emerald btn-sm">
                                        <i class="bi bi-search"></i> Ver detalle
                                    </a>
                                    <button class="btn btn-danger-soft btn-sm" type="button" data-bs-toggle="modal"
                                        data-bs-target="#{{ $deleteModalId }}" @disabled($isCurrentUser)>
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="{{ $deleteModalId }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold text-danger">
                                            <i class="bi bi-exclamation-triangle"></i> Eliminar usuario
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted">
                                            Esta acción eliminará definitivamente la cuenta de
                                            <strong>{{ $usuario->name }}</strong>. Los cambios no se pueden deshacer.
                                        </p>
                                        <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST"
                                            class="d-flex flex-column gap-3">
                                            @csrf
                                            @method('DELETE')
                                            <div>
                                                <label class="form-label fw-semibold">Contraseña del administrador</label>
                                                <input type="password" name="admin_password" class="form-control" required>
                                                @error('delete_' . $usuario->id)
                                                    <small class="text-danger d-block">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-soft-gray" data-bs-dismiss="modal">
                                                    Cancelar
                                                </button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bi bi-trash-fill"></i> Confirmar eliminación
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-muted">
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus"></i> Crear nuevo usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-4">
                        Se generará una contraseña temporal automáticamente. El usuario deberá cambiarla al iniciar sesión.
                    </p>
                    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="d-flex flex-column gap-3">
                        @csrf
                        <input type="hidden" name="form_context" value="create_user">

                        <div>
                            <label class="form-label fw-semibold">Nombre completo</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label fw-semibold">Correo</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label fw-semibold">Rol</label>
                            <select name="role" class="form-select" required>
                                <option value="user" @selected(old('role', 'user') === 'user')>Usuario</option>
                                <option value="admin" @selected(old('role') === 'admin')>Administrador</option>
                            </select>
                            @error('role')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-emerald px-4">
                                <i class="bi bi-save"></i> Crear usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (old('form_context') === 'create_user' && $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('crearUsuarioModal');
                if (!modalElement) return;
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            });
        </script>
    @endif

    @if (session('show_delete_modal'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const target = document.getElementById('modalEliminarUsuario{{ session('show_delete_modal') }}');
                if (!target) return;
                const modal = new bootstrap.Modal(target);
                modal.show();
            });
        </script>
    @endif

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

        .admin-card-wide {
            width: 100%;
        }
    </style>
@endsection
