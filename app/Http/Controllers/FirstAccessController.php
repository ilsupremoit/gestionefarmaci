<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class FirstAccessController extends Controller
{
    public function show()
    {
        if (!auth()->user()->must_change_password) {
            return redirect()->route(auth()->user()->ruolo . '.dashboard');
        }

        return view('auth.first-access');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        $user->email = $request->email;
        $user->password = $request->password;
        $user->must_change_password = false;
        $user->email_verified_at = null;
        $user->save();

        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('verification.notice')
            ->with('success', 'Abbiamo inviato la mail di verifica.');
    }
}
