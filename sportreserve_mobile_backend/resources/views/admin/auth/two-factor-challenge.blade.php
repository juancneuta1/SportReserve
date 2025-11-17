@extends('admin.layouts.app')

@section('title', 'Verificación 2FA - SportReserve')

@section('content')
    <div class="d-flex justify-content-center">
        <div class="admin-card" style="max-width: 480px; width:100%;">
            <h2 class="fw-bold mb-3 text-center">Verificación de doble factor</h2>
            <p class="text-muted text-center mb-4">Introduce el código generado por tu aplicación de autenticación.</p>

            @if (session('status'))
                <div class="alert alert-info">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.2fa.verify') }}" class="d-flex flex-column gap-3">
                @csrf
                <div>
                    <label class="form-label fw-semibold">Código 2FA</label>
                    <input type="text" name="code" class="form-control" placeholder="123456" required autofocus>
                    @error('code')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="form-label fw-semibold">Código de recuperación (opcional)</label>
                    <input type="text" name="recovery_code" class="form-control" placeholder="ABCD-EFGH">
                    @error('recovery_code')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-emerald w-100">Verificar</button>
            </form>

            <form action="{{ route('admin.logout') }}" method="POST" class="mt-3 text-center">
                @csrf
                <button type="submit" class="btn btn-link text-muted">Salir</button>
            </form>
        </div>
    </div>
@endsection
