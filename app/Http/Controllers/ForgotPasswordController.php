<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Mostra il form "Password dimenticata".
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Invia il link di reset via email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => "L'email è obbligatoria.",
            'email.email'    => "Inserisci un'email valida.",
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * Mostra il form di reset password.
     */
    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Aggiorna la password con il token ricevuto via email.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', 'min:8'],
        ], [
            'email.required'            => "L'email è obbligatoria.",
            'email.email'               => "Inserisci un'email valida.",
            'password.required'         => 'La nuova password è obbligatoria.',
            'password.confirmed'        => 'Le password non coincidono.',
            'password.min'              => 'La password deve avere almeno 8 caratteri.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Password aggiornata con successo! Accedi con le nuove credenziali.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
