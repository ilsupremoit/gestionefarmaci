<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispositivoController;
use App\Http\Controllers\Familiare\DashboardController as FamiliareDashboard;
use App\Http\Controllers\FirstAccessController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\Medico\DashboardController as MedicoDashboard;
use App\Http\Controllers\Medico\MedicoController;
use App\Http\Controllers\MedicoPazienteController;
use App\Http\Controllers\MedicoPazienteDetailController;
use App\Http\Controllers\MqttController;
use App\Http\Controllers\Paziente\DashboardController as PazienteDashboard;
use App\Http\Controllers\Paziente\PazienteController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ComuneController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;


Route::get('/comuni/cerca', [ComuneController::class, 'cerca']);
// ── Root ─────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->ruolo . '.dashboard');
    }
    return redirect()->route('login');
});

// ── Guest (non autenticati) ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/forgot-password',          [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password',         [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}',   [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',          [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// ── Logout ────────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Autenticati (tutti i ruoli) ───────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Primo accesso: impostazione password
    Route::get('/primo-accesso',  [FirstAccessController::class, 'show'])->name('first-access.show');
    Route::post('/primo-accesso', [FirstAccessController::class, 'store'])->name('first-access.store');

    // Verifica email (opzionale — non blocca l'accesso)
    Route::get('/email/verifica', fn() => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verifica/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route(auth()->user()->ruolo . '.dashboard')->with('success', 'Email verificata con successo.');
    })->middleware('signed')->name('verification.verify');
    Route::post('/email/verifica/notifica', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Nuova email di verifica inviata.');
    })->name('verification.send');
});

// ── MEDICO ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:medico'])->prefix('medico')->name('medico.')->group(function () {

    Route::get('/dashboard', [MedicoDashboard::class, 'index'])->name('dashboard');

    // Pazienti
    Route::get('/pazienti',      [MedicoPazienteController::class, 'index'])->name('pazienti.index');
    Route::get('/pazienti/crea', [MedicoPazienteController::class, 'create'])->name('pazienti.create');
    Route::post('/pazienti',     [MedicoPazienteController::class, 'store'])->name('pazienti.store');
    Route::get('/pazienti/{paziente}', [MedicoPazienteDetailController::class, 'show'])->name('pazienti.show');

    // Terapie
    Route::post('/pazienti/{paziente}/terapie', [MedicoPazienteDetailController::class, 'storeTerapia'])->name('pazienti.terapie.store');
    Route::delete('/pazienti/{paziente}/terapie/{terapia}', [MedicoPazienteDetailController::class, 'destroyTerapia'])->name('pazienti.terapie.destroy');
    Route::get('/pazienti/{paziente}/storico/{tipo?}', [MedicoPazienteDetailController::class, 'storico'])->name('pazienti.storico');

    // Comandi IoT rapidi (dalla pagina paziente)
    Route::post('/pazienti/{paziente}/eroga',   [MedicoPazienteDetailController::class, 'erogazioneForzata'])->name('pazienti.eroga');
    Route::post('/pazienti/{paziente}/allarme', [MedicoPazienteDetailController::class, 'toggleAllarme'])->name('pazienti.allarme');

    // Dispositivi
    Route::post('/pazienti/{paziente}/dispositivi',                              [MedicoController::class, 'dispositivoStore'])->name('pazienti.dispositivi.store');
    Route::get('/pazienti/{paziente}/dispositivi/{dispositivo}',                 [MedicoController::class, 'dispositivoShow'])->name('pazienti.dispositivi.show');
    Route::post('/pazienti/{paziente}/dispositivi/{dispositivo}/comando',        [MedicoController::class, 'dispositivoComando'])->name('pazienti.dispositivi.comando');
    Route::get('/pazienti/{paziente}/dispositivi/{dispositivo}/telemetria-live', [MedicoController::class, 'telemetriaLive'])->name('pazienti.dispositivi.telemetria');

    // Scomparti carosello (NUOVO)
    Route::post('/pazienti/{paziente}/dispositivi/{dispositivo}/scomparti',        [MedicoController::class, 'scompartiSalva'])->name('pazienti.dispositivi.scomparti');
    Route::post('/pazienti/{paziente}/dispositivi/{dispositivo}/eroga-forzata',    [MedicoController::class, 'erogazioneForzata'])->name('pazienti.dispositivi.eroga_forzata');
    Route::post('/pazienti/{paziente}/dispositivi/{dispositivo}/allarme/attiva',   [MedicoController::class, 'attivaAllarme'])->name('pazienti.dispositivi.allarme_attiva');
    Route::post('/pazienti/{paziente}/dispositivi/{dispositivo}/allarme/disattiva',[MedicoController::class, 'disattivaAllarme'])->name('pazienti.dispositivi.allarme_disattiva');

    // Scomparti dispositivo (gestione carosello)
    Route::get('/dispositivi/{idDispositivo}/scomparti',                          [DispositivoController::class, 'scomparti'])->name('dispositivi.scomparti');
    Route::post('/dispositivi/{idDispositivo}/scomparti',                         [DispositivoController::class, 'salvaScomparti'])->name('dispositivi.scomparti.salva');
    Route::patch('/dispositivi/{idDispositivo}/scomparti/{numeroScomparto}/stato',[DispositivoController::class, 'aggiornaStato'])->name('dispositivi.scomparti.stato');

    // Comandi MQTT diretti (AJAX)
    Route::post('/mqtt/{idDispositivo}/configura',  [MqttController::class, 'configuraScomparti'])->name('mqtt.configura');
    Route::post('/mqtt/{idDispositivo}/allarme',    [MqttController::class, 'attivaAllarme'])->name('mqtt.allarme');
    Route::post('/mqtt/{idDispositivo}/eroga',      [MqttController::class, 'erogaFarmaco'])->name('mqtt.eroga');
    Route::post('/mqtt/{idDispositivo}/sveglia',    [MqttController::class, 'setSveglia'])->name('mqtt.sveglia');
    Route::get('/mqtt/{idDispositivo}/mappa',       [MqttController::class, 'richiediMappa'])->name('mqtt.mappa');
    Route::post('/mqtt/{idDispositivo}/buzzer',     [MqttController::class, 'testBuzzer'])->name('mqtt.buzzer');

    // Assunzioni (AJAX update stato)
    Route::patch('/assunzioni/{assunzione}', [MedicoPazienteDetailController::class, 'aggiornaAssunzione'])->name('assunzioni.update');

    // Notifiche
    Route::get('/notifiche',  [MedicoController::class, 'notifiche'])->name('notifiche');
    Route::post('/notifiche', [MedicoController::class, 'inviaNotifica'])->name('notifiche.invia');
});

// ── PAZIENTE ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:paziente'])->prefix('paziente')->name('paziente.')->group(function () {
    Route::get('/dashboard',            [PazienteDashboard::class, 'index'])->name('dashboard');
    Route::get('/terapie',              [PazienteController::class, 'terapie'])->name('terapie');
    Route::get('/storico',              [PazienteController::class, 'storico'])->name('storico');
    Route::get('/storico/{assunzione}', [PazienteController::class, 'assunzioneShow'])->name('assunzione.show');
    Route::get('/dispositivi',          [PazienteController::class, 'dispositivi'])->name('dispositivi');
    Route::get('/notifiche',            [PazienteController::class, 'notifiche'])->name('notifiche');
    Route::post('/notifiche',           [PazienteController::class, 'inviaMessaggio'])->name('notifiche.invia');
});

// ── FAMILIARE ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:familiare'])->prefix('familiare')->name('familiare.')->group(function () {
    Route::get('/dashboard', [FamiliareDashboard::class, 'index'])->name('dashboard');
});

// ── ADMIN ─────────────────────────────────────────────────────────────────────
// ── ADMIN ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',   [AdminDashboard::class, 'index'])->name('dashboard');

    // Utenti
    Route::get('/utenti',                      [AdminController::class, 'utenti'])->name('utenti');
    Route::get('/utenti/crea',                 [AdminController::class, 'creaUtente'])->name('utenti.create');
    Route::post('/utenti',                     [AdminController::class, 'salvaUtente'])->name('utenti.store');
    Route::delete('/utenti/{user}',            [AdminController::class, 'eliminaUtente'])->name('utenti.elimina');
    Route::post('/utenti/{user}/reset-password',[AdminController::class, 'resetPassword'])->name('utenti.reset-password');

    // Pazienti
    Route::get('/pazienti',            [AdminController::class, 'pazienti'])->name('pazienti');
    Route::get('/pazienti/{paziente}', [AdminController::class, 'pazienteShow'])->name('pazienti.show');

    // Terapie (tutte)
    Route::get('/terapie', [AdminController::class, 'terapie'])->name('terapie');

    // Dispositivi
    Route::get('/dispositivi', [AdminController::class, 'dispositivi'])->name('dispositivi');

    // Farmaci
    Route::get('/farmaci',             [AdminController::class, 'farmaci'])->name('farmaci');
    Route::post('/farmaci',            [AdminController::class, 'salvaFarmaco'])->name('farmaci.store');
    Route::delete('/farmaci/{farmaco}',[AdminController::class, 'eliminaFarmaco'])->name('farmaci.elimina');

    // Notifiche ai medici
    Route::get('/notifiche',  [AdminController::class, 'notifiche'])->name('notifiche');
    Route::post('/notifiche', [AdminController::class, 'inviaNotifica'])->name('notifiche.invia');
});




