<?php

namespace App\Http\Controllers\Paziente;

use App\Http\Controllers\Controller;
use App\Models\Assunzione;
use App\Models\Dispositivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $utente   = Auth::user();
        $paziente = $utente->paziente;

        if (!$paziente) {
            return view('paziente.dashboard', [
                'utente'            => $utente,
                'paziente'          => null,
                'terapieAttive'     => collect(),
                'assunzioniOggi'    => collect(),
                'numDispositivi'    => 0,
                'notifiche'         => collect(),
                'prossimeAssunzioni'=> collect(),
                'statOggi'          => ['totali'=>0,'prese'=>0,'saltate'=>0,'attesa'=>0],
                'medici'            => collect(),
            ]);
        }

        $terapieAttive = $paziente->terapie()
            ->with('farmaco', 'somministrazioni')
            ->where('attiva', true)
            ->get();

        $assunzioniOggi = Assunzione::whereHas('somministrazione.terapia', fn($q) =>
                $q->where('id_paziente', $paziente->id))
            ->whereDate('data_prevista', today())
            ->with('somministrazione.terapia.farmaco')
            ->get();

        $prossimeAssunzioni = Assunzione::whereHas('somministrazione.terapia', fn($q) =>
                $q->where('id_paziente', $paziente->id))
            ->where('data_prevista', '>=', now())
            ->where('stato', 'in_attesa')
            ->with('somministrazione.terapia.farmaco')
            ->orderBy('data_prevista')
            ->take(5)
            ->get();

        $numDispositivi = $paziente->dispositivi()->where('stato', 'attivo')->count();

        $notifiche = DB::table('notifiche')
            ->where('id_utente', $utente->id)
            ->orderByDesc('data_invio')
            ->take(5)
            ->get();

        $medici = $paziente->medici()->select('users.id','nome','cognome','email','telefono')->get();

        $statOggi = [
            'totali'  => $assunzioniOggi->count(),
            'prese'   => $assunzioniOggi->whereIn('stato', ['assunta','erogata'])->count(),
            'saltate' => $assunzioniOggi->whereIn('stato', ['saltata','non_ritirata'])->count(),
            'attesa'  => $assunzioniOggi->where('stato', 'in_attesa')->count(),
        ];

        return view('paziente.dashboard', compact(
            'utente','paziente','terapieAttive','assunzioniOggi',
            'prossimeAssunzioni','numDispositivi','notifiche','statOggi','medici'
        ));
    }
}
