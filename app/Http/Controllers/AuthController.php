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
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'login.required'    => 'Inserisci email o nome utente.',
            'password.required' => 'La password è obbligatoria.',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // capisce se è email o username
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$field => $login, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'login' => 'Credenziali non valide.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // 🔴 primo accesso
        if ($user->must_change_password) {
            return redirect()->route('first-access.show');
        }

        return $this->redirectByRole($user->ruolo);
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
