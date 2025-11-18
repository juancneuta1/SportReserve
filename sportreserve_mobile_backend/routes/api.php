<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CanchaController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

// ======================================================
//                   TEST DE VIDA
// ======================================================
Route::get('/ping', fn() => response()->json(['message' => 'API funcionando correctamente']));


// ======================================================
//                  AUTENTICACIÓN PÚBLICA
// ======================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);


// ======================================================
//                       CANCHAS
// ======================================================

// Listar todas las canchas (público)
Route::get('/canchas', [CanchaController::class, 'index']);

// Ver detalle de una cancha
Route::get('/canchas/{id}', [CanchaController::class, 'show']);


// ======================================================
//                 CALIFICACIONES (PÚBLICO)
// ======================================================

// Promedio de calificación de una cancha
Route::get('/calificaciones/{cancha_id}/promedio', [CalificacionController::class, 'promedio']);

// Listado de calificaciones de una cancha
Route::get('/calificaciones/{cancha_id}', [CalificacionController::class, 'listar']);

// Resumen (promedio + total + últimas reseñas)
Route::get(
    '/canchas/{cancha_id}/calificaciones/resumen',
    [CalificacionController::class, 'resumen']
);

// ======================================================
//            MERCADO PAGO WEBHOOK (PÚBLICO)
// ======================================================
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'webhook']);



// ======================================================
//              RUTAS PROTEGIDAS (SANCTUM)
// ======================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // ------------------------------------------
    //        PERFIL DE USUARIO
    // ------------------------------------------
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/update-photo', [AuthController::class, 'updatePhoto']);

    // ------------------------------------------
    //        CALIFICACIONES (LOGUEADO)
    // ------------------------------------------
    // Crear reseña (solo usuarios autenticados)
    Route::post('/canchas/{cancha_id}/calificaciones', [CalificacionController::class, 'store']);

    // ------------------------------------------
    //        CANCHAS (ADMIN / STAFF)
    // ------------------------------------------
    // Crear cancha
    Route::post('/canchas', [CanchaController::class, 'store']);

    // Editar cancha
    Route::put('/canchas/{id}', [CanchaController::class, 'update']);

    // Eliminar cancha
    Route::delete('/canchas/{id}', [CanchaController::class, 'destroy']);

    // ------------------------------------------
    //               RESERVAS
    // ------------------------------------------

    // Reservas pendientes (panel admin)
    Route::get('/reservas/pendientes', [ReservaController::class, 'pendientes']);

    // Validar pago admin
    Route::put('/reservas/{id}/validar', [ReservaController::class, 'validarPago']);

    // Listar reservas del usuario autenticado
    Route::get('/reservas', [ReservaController::class, 'index']);

    // Crear reserva
    Route::post('/reservas', [ReservaController::class, 'store']);

    // Actualizar reserva
    Route::put('/reservas/{id}', [ReservaController::class, 'update']);

    // Eliminar reserva
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);

    // Mis reservas
    Route::get('/mis-reservas', [ReservaController::class, 'misReservas']);

    // Cancelar
    Route::put('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);

    // Subir comprobante
    Route::post('/reservas/{id}/comprobante', [ReservaController::class, 'subirComprobante']);

    // Disponibilidad horaria
    Route::get('/canchas/{id}/disponibilidad', [ReservaController::class, 'disponibilidad']);

    // Horarios diarios
    Route::get('/canchas/{id}/horarios', [ReservaController::class, 'disponibilidad']);

    // Estado del pago
    Route::get('/reservas/{id}/estado-pago', [ReservaController::class, 'estadoPago']);
});
