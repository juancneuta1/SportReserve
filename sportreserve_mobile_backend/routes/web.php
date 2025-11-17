<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Services\AdminActionNotifier;
use App\Http\Controllers\Admin\AuthAdminController;
use App\Http\Controllers\Admin\CalificacionAdminController;
use App\Http\Controllers\Admin\CanchaAdminController;
use App\Http\Controllers\Admin\ComprobanteAdminController;
use App\Http\Controllers\Admin\ReservaAdminController;
use App\Http\Controllers\Admin\ComunicacionesController;
use App\Http\Controllers\Admin\MensajeController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\UsuarioAdminController;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'SportReserve Backend',
        'version' => '1.0'
    ]);
});


// 🔐 Ruta principal de login administrativo
Route::get('/admin/login', [AuthAdminController::class, 'showLoginForm'])->name('admin.login');

// ⚙️ Alias para que Laravel encuentre la ruta 'login' por defecto cuando usa el middleware 'auth'
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// 🔑 Acciones de autenticación del administrador
Route::post('/admin/login', [AuthAdminController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthAdminController::class, 'logout'])->name('admin.logout');

// 🧭 Rutas protegidas del panel administrativo
Route::middleware(['auth', '2fa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Gestión de canchas
    Route::resource('canchas', CanchaAdminController::class);

    // Gestión de reservas
    Route::get('/reservas', [ReservaAdminController::class, 'index'])->name('reservas.index');
    Route::get('/reservas/{reserva}/edit', [ReservaAdminController::class, 'edit'])->name('reservas.edit');
    Route::put('/reservas/{reserva}', [ReservaAdminController::class, 'update'])->name('reservas.update');
    Route::put('/reservas/{reserva}/hold', [ReservaAdminController::class, 'hold'])->name('reservas.hold');
    Route::put('/reservas/{reserva}/cancel', [ReservaAdminController::class, 'cancel'])->name('reservas.cancel');

    // Calificaciones
    Route::get('/calificaciones', [CalificacionAdminController::class, 'index'])->name('calificaciones');

    // Gestión de usuarios
    Route::get('/usuarios', [UsuarioAdminController::class, 'index'])->name('usuarios');
    Route::post('/usuarios', [UsuarioAdminController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}', [UsuarioAdminController::class, 'show'])->name('usuarios.show');
    Route::put('/usuarios/{usuario}', [UsuarioAdminController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuarioAdminController::class, 'destroy'])->name('usuarios.destroy');

    // Comprobantes
    Route::get('/comprobantes', [ComprobanteAdminController::class, 'index'])->name('comprobantes.index');
    Route::put('/comprobantes/{id}/validar', [ComprobanteAdminController::class, 'validar'])->name('comprobantes.validar');

    // Seguridad
    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::post('/security/sessions/flush', [SecurityController::class, 'destroyOtherSessions'])->name('security.sessions.flush');

    // Doble factor
    Route::get('/security/two-factor', [TwoFactorController::class, 'index'])->name('two-factor.index');
    Route::post('/security/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/security/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/security/two-factor/recovery', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery');
    Route::delete('/security/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // Comunicaciones internas
    Route::get('/comunicaciones', [ComunicacionesController::class, 'dashboard'])->name('comunicaciones.dashboard');
    Route::get('/comunicaciones/notificaciones', [ComunicacionesController::class, 'notifications'])->name('comunicaciones.notifications');
    Route::put('/comunicaciones/notificaciones/mark-all', [ComunicacionesController::class, 'markAllRead'])->name('comunicaciones.notifications.readall');

    Route::get('/mensajes/enviados', [MensajeController::class, 'enviados'])->name('mensajes.enviados');
    Route::put('/mensajes/{mensaje}/leido', [MensajeController::class, 'marcarLeido'])->name('mensajes.leido');
    Route::resource('mensajes', MensajeController::class)->names('mensajes')->except(['create', 'edit', 'update', 'show']);
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('admin.2fa.challenge');
    Route::post('/admin/two-factor/verify', [TwoFactorController::class, 'verifyChallenge'])->name('admin.2fa.verify');
});

// 🧪 RUTA DE PRUEBA PARA ENVÍO DE CORREO (admin logueado)
Route::get('/test-mail', function () {
    try {
        $adminEmail = auth()->user()->email;

        Mail::raw('Hola 👋 este es un correo de prueba de SportReserve Mobile.', function ($message) use ($adminEmail) {
            $message->to($adminEmail)
                ->subject('📬 Prueba de correo Laravel');
        });

        return '✅ Envío correcto al administrador autenticado (' . $adminEmail . ')';
    } catch (\Exception $e) {
        return '❌ Error: ' . $e->getMessage();
    }
})->middleware('auth'); // 👈 solo funciona con admin logueado


// 🧾 RUTA DE PRUEBA PARA CORREO DE AUDITORÍA
Route::get('/test-audit', function () {
    try {
        $actor = auth()->user() ?? User::first(); // si no hay sesión, toma el primer usuario registrado

        AdminActionNotifier::send(
            $actor,
            'Prueba de Auditoría',
            'Este es un mensaje de prueba para confirmar el envío al correo ADMIN_AUDIT_EMAIL.'
        );

        return '✅ Correo de auditoría enviado correctamente. Revisa tu bandeja o spam.';
    } catch (\Exception $e) {
        return '❌ Error en correo de auditoría: ' . $e->getMessage();
    }
});
