<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Assunzione;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $medico = Auth::user();

        $pazientiIds = DB::table('medici_pazienti')
            ->where('id_medico', $medico->id)
            ->pluck('id_paziente');

        $numPazienti = $pazientiIds->count();

        $terapieAttive = DB::table('terapie')
            ->whereIn('id_paziente', $pazientiIds)
            ->where('attiva', true)
            ->count();

        // Assunzioni previste oggi per i pazienti del medico
        $assunzioniOggi = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($pazientiIds) {
                $q->whereIn('id_paziente', $pazientiIds);
            })
            ->whereDate('data_prevista', today())
            ->get();

        $assunzioniPreviste = $assunzioniOggi->count();
        $dosiSaltate = $assunzioniOggi->whereIn('stato', ['saltata', 'non_ritirata'])->count();

        // Ultimi 5 pazienti
        $ultimiPazienti = \App\Models\Paziente::whereIn('id', $pazientiIds)
            ->with('utente', 'dispositivi')
            ->latest('id')
            ->take(5)
            ->get();

        return view('medico.dashboard', compact(
            'medico', 'numPazienti', 'terapieAttive',
            'assunzioniPreviste', 'dosiSaltate', 'ultimiPazienti'
        ));
    }
}
