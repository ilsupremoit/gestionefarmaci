<?php

namespace App\Http\Controllers;

use App\Models\Paziente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Mostra il form di registrazione.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('login');
        }
        return view('auth.register');
    }

    /**
     * Gestisce la registrazione di un nuovo utente.
     * Se il ruolo è 'paziente', crea anche il record nella tabella `pazienti`.
     */
    public function register(Request $request)
    {
        $request->validate([
            'nome'          => ['required', 'string', 'max:50'],
            'cognome'       => ['required', 'string', 'max:50'],
            'email'         => ['required', 'email', 'max:100', 'unique:utenti,email'],
            'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'ruolo'         => ['required', 'in:paziente,medico,familiare'],
            'telefono'      => ['nullable', 'string', 'max:20'],
            // Campi extra solo per paziente
            'data_nascita'  => ['nullable', 'required_if:ruolo,paziente', 'date'],
            'indirizzo'     => ['nullable', 'string', 'max:150'],
        ], [
            'nome.required'         => 'Il nome è obbligatorio.',
            'cognome.required'      => 'Il cognome è obbligatorio.',
            'email.required'        => 'L\'email è obbligatoria.',
            'email.email'           => 'Inserisci un\'email valida.',
            'email.unique'          => 'Questa email è già registrata.',
            'password.required'     => 'La password è obbligatoria.',
            'password.confirmed'    => 'Le password non coincidono.',
            'password.min'          => 'La password deve avere almeno 8 caratteri.',
            'ruolo.required'        => 'Seleziona un ruolo.',
            'ruolo.in'              => 'Ruolo non valido.',
            'data_nascita.required_if' => 'La data di nascita è obbligatoria per i pazienti.',
            'data_nascita.date'     => 'Data di nascita non valida.',
        ]);

        // Transazione: crea utente + eventuale paziente in modo atomico
        DB::transaction(function () use ($request) {
            $user = User::create([
                'nome'     => $request->nome,
                'cognome'  => $request->cognome,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'ruolo'    => $request->ruolo,
                'telefono' => $request->telefono,
            ]);

            // Se il ruolo è paziente, crea il record aggiuntivo
            if ($request->ruolo === 'paziente') {
                Paziente::create([
                    'id_utente'    => $user->id,
                    'data_nascita' => $request->data_nascita,
                    'indirizzo'    => $request->indirizzo,
                ]);
            }
        });

        return redirect()->route('login')
            ->with('success', 'Registrazione completata! Accedi con le tue credenziali.');
    }
}
