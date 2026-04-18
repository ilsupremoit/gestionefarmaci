<?php

use App\Http\Controllers\MedicoPazienteDetailController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Familiare\DashboardController as FamiliareDashboard;
use App\Http\Controllers\FirstAccessController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\Medico\DashboardController as MedicoDashboard;
use App\Http\Controllers\MedicoPazienteController;
use App\Http\Controllers\Paziente\DashboardController as PazienteDashboard;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use PhpMqtt\Client\Facades\MQTT;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->ruolo . '.dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/primo-accesso', [FirstAccessController::class, 'show'])->name('first-access.show');
    Route::post('/primo-accesso', [FirstAccessController::class, 'store'])->name('first-access.store');

    Route::get('/email/verifica', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verifica/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route(auth()->user()->ruolo . '.dashboard')
            ->with('success', 'Email verificata con successo.');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verifica/notifica', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Nuova email di verifica inviata.');
    })->name('verification.send');
});

Route::get('/mqtt-test', function () {
    MQTT::publish('pillmate/disp_01/comandi', 'attiva_allarme');
    return 'Messaggio inviato';
});

Route::middleware(['auth', 'role:medico'])->prefix('medico')->name('medico.')->group(function () {
    Route::get('/dashboard', [MedicoDashboard::class, 'index'])->name('dashboard');
    Route::get('/pazienti', [MedicoPazienteController::class, 'index'])->name('pazienti.index');
    Route::get('/pazienti/crea', [MedicoPazienteController::class, 'create'])->name('pazienti.create');
    Route::post('/pazienti', [MedicoPazienteController::class, 'store'])->name('pazienti.store');

    // Dettaglio paziente
    Route::get('/pazienti/{paziente}', [MedicoPazienteDetailController::class, 'show'])->name('pazienti.show');

    // Terapie
    Route::post('/pazienti/{paziente}/terapie', [MedicoPazienteDetailController::class, 'storeTerapia'])->name('pazienti.terapie.store');

    // Comandi IoT
    Route::post('/pazienti/{paziente}/eroga', [MedicoPazienteDetailController::class, 'erogazioneForzata'])->name('pazienti.eroga');
    Route::post('/pazienti/{paziente}/allarme', [MedicoPazienteDetailController::class, 'toggleAllarme'])->name('pazienti.allarme');

    // Aggiorna stato assunzione (AJAX)
    Route::patch('/assunzioni/{assunzione}', [MedicoPazienteDetailController::class, 'aggiornaAssunzione'])->name('assunzioni.update');
});

Route::middleware(['auth', 'role:paziente'])->prefix('paziente')->name('paziente.')->group(function () {
    Route::get('/dashboard', [PazienteDashboard::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:familiare'])->prefix('familiare')->name('familiare.')->group(function () {
    Route::get('/dashboard', [FamiliareDashboard::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
});
