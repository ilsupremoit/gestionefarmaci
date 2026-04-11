<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Mostra la pagina di login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->ruolo);
        }
        return view('auth.login');
    }

    /**
     * Gestisce il tentativo di login.
     * Usa la tabella `utenti` con i campi email e password.
     */
    public function login(Request $request)
    {
        // Validazione input
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required'    => 'L\'email è obbligatoria.',
            'email.email'       => 'Inserisci un\'email valida.',
            'password.required' => 'La password è obbligatoria.',
            'password.min'      => 'La password deve avere almeno 6 caratteri.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        // Tentativo di autenticazione sulla tabella `utenti`
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Credenziali non valide. Controlla email e password.',
            ]);
        }

        // Rigenera la sessione per sicurezza (CSRF)
        $request->session()->regenerate();

        // Reindirizza in base al ruolo dell'utente
        return $this->redirectByRole(Auth::user()->ruolo);
    }

    /**
     * Gestisce il logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout effettuato correttamente.');
    }

    /**
     * Reindirizza alla dashboard corretta in base al ruolo.
     * I ruoli sono definiti nell'ENUM della tabella `utenti`:
     * 'paziente' | 'medico' | 'familiare' | 'admin'
     */
    protected function redirectByRole(string $ruolo)
    {
        return match ($ruolo) {
            'medico'    => redirect()->route('medico.dashboard'),
            'paziente'  => redirect()->route('paziente.dashboard'),
            'familiare' => redirect()->route('familiare.dashboard'),
            'admin'     => redirect()->route('admin.dashboard'),
            default     => redirect('/'),
        };
    }
}
