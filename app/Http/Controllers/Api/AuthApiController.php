<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $data['login'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$field => $login, 'password' => $data['password']])) {
            return response()->json([
                'message' => 'Credenziali non valide.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->must_change_password) {
            return response()->json([
                'message' => 'Completa il primo accesso dal sito prima di usare l\'app.',
            ], 403);
        }

        if ($user->ruolo !== 'paziente') {
            return response()->json([
                'message' => 'Questa app e disponibile solo per il profilo paziente.',
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'cognome' => $user->cognome,
                'email' => $user->email,
                'ruolo' => $user->ruolo,
            ],
        ]);
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'token' => '',
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'cognome' => $user->cognome,
                'email' => $user->email,
                'ruolo' => $user->ruolo,
            ],
        ]);
    }
}
