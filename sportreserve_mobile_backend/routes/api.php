<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CanchaController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn() => response()->json(['message' => 'API funcionando correctamente']));

// Rutas pÃºblicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/canchas', [CanchaController::class, 'index']);
Route::get('/canchas/{id}', [CanchaController::class, 'show']);
Route::get('/calificaciones/{cancha_id}/promedio', [CalificacionController::class, 'promedio']);
Route::get('/calificaciones/{cancha_id}', [CalificacionController::class, 'listar']);
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'webhook']);

// Rutas protegidas (requieren token)
Route::middleware(['auth:sanctum'])->group(function () {
    // AutenticaciÃ³n
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/update-photo', [AuthController::class, 'updatePhoto']);

    // Canchas (solo admin o staff)
    Route::post('/canchas', [CanchaController::class, 'store']);
    Route::put('/canchas/{id}', [CanchaController::class, 'update']);
    Route::delete('/canchas/{id}', [CanchaController::class, 'destroy']);

    Route::post('/calificaciones', [CalificacionController::class, 'store']);

    Route::get('/reservas/pendientes', [ReservaController::class, 'pendientes']);
    Route::put('/reservas/{id}/validar', [ReservaController::class, 'validarPago']);

    // Reservas (usuarios autenticados)
    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::post('/reservas', [ReservaController::class, 'store']);
    Route::put('/reservas/{id}', [ReservaController::class, 'update']);
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);
    Route::get('/mis-reservas', [ReservaController::class, 'misReservas']);
    Route::put('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);
    Route::post('/reservas/{id}/comprobante', [ReservaController::class, 'subirComprobante']);
    Route::get('/canchas/{id}/disponibilidad', [ReservaController::class, 'disponibilidad']);
    Route::get('/canchas/{id}/horarios', [ReservaController::class, 'disponibilidad']);

    //Para confirmar el estado de pago de una reserva

    Route::get('/reservas/{id}/estado-pago', [ReservaController::class, 'estadoPago']);

});

