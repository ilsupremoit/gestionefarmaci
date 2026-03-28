<?php

namespace App\Http\Controllers\Paziente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $utente  = Auth::user();
        $paziente = $utente->paziente;
        return view('paziente.dashboard', compact('utente', 'paziente'));
    }
}
