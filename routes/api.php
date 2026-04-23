<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PazienteApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::get('/dashboard', [PazienteApiController::class, 'dashboard']);
    Route::get('/paziente/terapie', [PazienteApiController::class, 'terapie']);
    Route::get('/paziente/storico', [PazienteApiController::class, 'storico']);
    Route::get('/paziente/dispositivi', [PazienteApiController::class, 'dispositivi']);
    Route::get('/paziente/notifiche', [PazienteApiController::class, 'notifiche']);
});
