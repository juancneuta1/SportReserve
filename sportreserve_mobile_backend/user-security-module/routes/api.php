<?php

use App\Http\Controllers\Auth\LoginUserController;
use App\Http\Controllers\Auth\RegisterUserController;
use Illuminate\Support\Facades\Route;

Route::post('/user/register', [RegisterUserController::class, 'register']);
Route::post('/user/login', [LoginUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/security/logs', [LoginUserController::class, 'securityLogs']);
    Route::get('/user/security/sessions', [LoginUserController::class, 'activeSessions']);
    Route::post('/user/security/logout-all', [LoginUserController::class, 'logoutAllSessions']);
});
