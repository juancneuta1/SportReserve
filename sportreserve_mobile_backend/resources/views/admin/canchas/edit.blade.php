@extends('admin.layouts.app')

@section('title', 'Editar Cancha - SportReserve')

@section('content')
    <div class="page-header text-center mb-4">
        <p class="text-muted mb-2 text-uppercase small">Canchas</p>
        <h1 class="mb-1">Editar cancha</h1>
        <p class="mb-0">Actualiza la información y disponibilidad del espacio seleccionado.</p>
    </div>

    <div class="admin-card mx-auto" style="max-width: 900px;">
        <form action="{{ route('admin.canchas.update', $cancha) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.canchas.form', ['cancha' => $cancha])

            <div class="text-center mt-4 d-flex flex-column flex-md-row justify-content-center gap-3">
                <button type="submit" class="btn btn-emerald px-4">
                    <i class="bi bi-save"></i> Actualizar
                </button>
                <a href="{{ route('admin.canchas.index') }}" class="btn btn-soft-gray px-4">
                    <i class="bi bi-arrow-left-circle"></i> Volver
                </a>
            </div>
        </form>
    </div>

    <style>
        .admin-card label {
            font-weight: 600;
            color: var(--emerald-900);
        }

        .admin-card .form-control {
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            padding: 0.65rem 0.95rem;
        }

        .admin-card .form-control:focus {
            border-color: var(--emerald-500);
            box-shadow: 0 0 0 0.15rem rgba(35, 161, 90, 0.15);
        }
    </style>
@endsection
