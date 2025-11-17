@extends('admin.layouts.app')

@section('title', '🔒 Panel de Seguridad - SportReserve')

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <p class="text-muted mb-1 text-uppercase tracking-[0.3em] text-xs">Seguridad</p>
            <h1 class="mb-2 fw-bold">🔒 Panel de Seguridad</h1>
            <p class="mb-0 text-muted">Monitorea el acceso de administradores y la actividad reciente del sistema.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-soft-gray">
                <i class="bi bi-arrow-left-circle"></i> Volver al dashboard
            </a>
        </div>
    </div>

    {{-- Alertas (conectar a session() en backend) --}}
    @if (session('status'))
        <div class="alert alert-success mb-4 rounded-[18px] shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- 1. Estado General --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- 2FA --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 flex items-center gap-4">
            <div class="p-3 rounded-full bg-emerald-50 text-emerald-600">
                <i class="bi bi-shield-lock"></i>
            </div>
            <div>
                {{-- placeholder: usar $isTwoFactorEnabled --}}
                <p class="text-sm text-gray-500 mb-1">Autenticación 2FA</p>
                <p class="text-lg fw-semibold">Activa</p>
                <span class="inline-flex items-center text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-700">
                    Protegido
                </span>
            </div>
        </div>
        {{-- Último acceso --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 flex items-center gap-4">
            <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                {{-- placeholder: usar $lastLogin --}}
                <p class="text-sm text-gray-500 mb-1">Último acceso exitoso</p>
                <p class="text-lg fw-semibold">09 nov 2025 · 10:15</p>
                <span class="text-xs text-gray-500">IP: 181.55.24.10</span>
            </div>
        </div>
        {{-- Intentos fallidos --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 flex items-center gap-4">
            <div class="p-3 rounded-full bg-amber-50 text-amber-600">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                {{-- placeholder: usar $failedAttempts --}}
                <p class="text-sm text-gray-500 mb-1">Intentos fallidos recientes</p>
                <p class="text-lg fw-semibold">2 en la última hora</p>
                <span class="text-xs text-gray-500">Revisado a las 09:50</span>
            </div>
        </div>
    </div>

    {{-- 2. Gráfico de actividad --}}
    <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Actividad semanal</h2>
                <p class="text-sm text-gray-500 mb-0">Número de inicios de sesión por día (últimos 7 días).</p>
            </div>
            <span class="text-sm text-emerald-600 fw-semibold">+12% vs semana pasada</span>
        </div>
        <canvas id="loginActivityChart" height="80"></canvas>
    </div>

    {{-- 3. Sesiones activas --}}
    <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mb-6">
        <div class="flex flex-col flex-md-row justify-between gap-3 mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Sesiones activas</h2>
                <p class="text-sm text-gray-500 mb-0">Dispositivos con sesión abierta en este momento.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn btn-danger-soft">
                    🔁 Cerrar todas las demás sesiones
                </button>
                <button type="button" class="btn btn-outline-emerald">
                    📜 Ver historial completo
                </button>
                <button type="button" class="btn btn-emerald">
                    🧩 Configurar autenticación 2FA
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-emerald-50">
                    <tr>
                        <th>IP</th>
                        <th>Navegador</th>
                        <th>Sistema</th>
                        <th>Fecha/Hora</th>
                        <th class="text-end">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Ejemplos estáticos: reemplazar por @foreach ($sessions as $session) --}}
                    <tr>
                        <td>181.55.24.10</td>
                        <td>Chrome 119</td>
                        <td>Windows 11</td>
                        <td>09/11/2025 10:15</td>
                        <td class="text-end">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700">
                                Sesión actual
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>170.252.12.80</td>
                        <td>Safari 17</td>
                        <td>macOS Sonoma</td>
                        <td>08/11/2025 21:40</td>
                        <td class="text-end">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                                Activa
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>45.230.89.200</td>
                        <td>Edge 118</td>
                        <td>Windows 10</td>
                        <td>08/11/2025 16:22</td>
                        <td class="text-end">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                                Activa
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Scripts del gráfico --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('loginActivityChart');

                if (!ctx) {
                    return;
                }

                // TODO: reemplazar con datos reales (p.ej. vía @json($loginStats))
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                        datasets: [{
                            label: 'Inicios de sesión',
                            data: [5, 7, 3, 8, 6, 10, 9],
                            borderColor: '#16A34A',
                            backgroundColor: 'rgba(22,163,74,0.15)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#16A34A',
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f0f4f8' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
