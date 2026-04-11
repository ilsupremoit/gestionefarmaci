<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Medico\DashboardController as MedicoDashboard;
use App\Http\Controllers\Paziente\DashboardController as PazienteDashboard;
use App\Http\Controllers\Familiare\DashboardController as FamiliareDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use Illuminate\Support\Facades\Route;
use PhpMqtt\Client\Facades\MQTT;


// ── Redirect radice ───────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->ruolo . '.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/mqtt-test', function () {
    MQTT::publish('pillmate/disp_01/comandi', 'attiva_allarme');
    return 'Messaggio inviato';
});
// ── Auth (solo per guest) ─────────────────────────────
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login',    [AuthController::class,  'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class,  'login']);

    // Registrazione
    Route::get('/register',  [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Medico ────────────────────────────────────────────
Route::middleware(['auth', 'role:medico'])->prefix('medico')->name('medico.')->group(function () {
    Route::get('/dashboard', [MedicoDashboard::class, 'index'])->name('dashboard');
});

// ── Paziente ──────────────────────────────────────────
Route::middleware(['auth', 'role:paziente'])->prefix('paziente')->name('paziente.')->group(function () {
    Route::get('/dashboard', [PazienteDashboard::class, 'index'])->name('dashboard');
});

// ── Familiare ─────────────────────────────────────────
Route::middleware(['auth', 'role:familiare'])->prefix('familiare')->name('familiare.')->group(function () {
    Route::get('/dashboard', [FamiliareDashboard::class, 'index'])->name('dashboard');
});

// ── Admin ─────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
});
