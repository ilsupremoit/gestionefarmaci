<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $ruolo): mixed
    {
        if (!Auth::check() || Auth::user()->ruolo !== $ruolo) {
            abort(403, 'Accesso non autorizzato.');
        }
        return $next($request);
    }
}
