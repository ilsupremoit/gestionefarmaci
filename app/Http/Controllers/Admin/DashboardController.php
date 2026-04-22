<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use App\Models\Farmaco;
use App\Models\Paziente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::user();

        $stats = [
            'utenti'      => User::count(),
            'medici'      => User::where('ruolo', 'medico')->count(),
            'pazienti'    => User::where('ruolo', 'paziente')->count(),
            'dispositivi' => Dispositivo::count(),
            'farmaci'     => Farmaco::count(),
            'non_lette'   => DB::table('notifiche')->where('letta', false)->count(),
        ];

        $ultimiUtenti       = User::orderByDesc('created_at')->take(8)->get();
        $dispositiviRecenti = Dispositivo::with('paziente.utente')->orderByDesc('id')->take(6)->get();

        return view('admin.dashboard', compact('admin', 'stats', 'ultimiUtenti', 'dispositiviRecenti'));
    }
}
