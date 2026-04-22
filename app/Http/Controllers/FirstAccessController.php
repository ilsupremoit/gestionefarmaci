<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class FirstAccessController extends Controller
{
    public function show()
    {
        // Se la password è già stata cambiata, vai direttamente alla dashboard
        if (!auth()->user()->must_change_password) {
            return redirect()->route(auth()->user()->ruolo . '.dashboard');
        }

        return view('auth.first-access');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore(Auth::id())],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.required'         => 'L\'indirizzo email è obbligatorio.',
            'email.unique'           => 'Questa email è già in uso da un altro account.',
            'password.required'      => 'La password è obbligatoria.',
            'password.confirmed'     => 'Le due password non coincidono.',
            'password.min'           => 'La password deve avere almeno 8 caratteri.',
        ]);

        $user = Auth::user();

        $user->email                = $request->email;
        $user->password             = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        // Redirect diretto alla dashboard del ruolo — nessuna verifica email obbligatoria
        return redirect()
            ->route($user->ruolo . '.dashboard')
            ->with('success', '✅ Password impostata con successo! Benvenuto su PillMate.');
    }
}
