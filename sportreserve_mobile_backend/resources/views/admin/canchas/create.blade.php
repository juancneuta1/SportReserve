@extends('admin.layouts.app')

@section('title', 'Registrar nueva cancha - SportReserve')

@section('content')
    <div class="page-header text-center mb-4">
        <p class="text-muted mb-2 text-uppercase small">Canchas</p>
        <h1 class="mb-1">Registrar nueva cancha</h1>
        <p class="mb-0">Ingresa la información clave para publicar el espacio deportivo.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-card mx-auto" style="max-width: 900px;">
        <form action="{{ route('admin.canchas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('admin.canchas.form', ['cancha' => new \App\Models\Cancha])

            <div class="text-center mt-4 d-flex flex-column flex-md-row justify-content-center gap-3">
                <button type="submit" class="btn btn-emerald px-4">
                    <i class="bi bi-save"></i> Guardar
                </button>
                <a href="{{ route('admin.canchas.index') }}" class="btn btn-soft-gray px-4">
                    <i class="bi bi-arrow-left-circle"></i> Cancelar
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

        .alert ul {
            padding-left: 1.2rem;
        }
    </style>
@endsection
