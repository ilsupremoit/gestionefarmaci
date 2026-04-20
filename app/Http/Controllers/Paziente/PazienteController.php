<?php

namespace App\Http\Controllers\Paziente;

use App\Http\Controllers\Controller;
use App\Models\Assunzione;
use App\Models\Dispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PazienteController extends Controller
{
    private function getPaziente()
    {
        $paziente = Auth::user()->paziente;
        if (!$paziente) abort(404, 'Profilo paziente non trovato.');
        return $paziente;
    }

    // ── LE MIE TERAPIE ─────────────────────────────────────────────

    public function terapie()
    {
        $utente   = Auth::user();
        $paziente = $this->getPaziente();

        $terapieAttive = $paziente->terapie()
            ->with(['farmaco', 'somministrazioni', 'medico'])
            ->where('attiva', true)
            ->orderByDesc('data_inizio')
            ->get();

        $terapieConcluse = $paziente->terapie()
            ->with(['farmaco', 'somministrazioni', 'medico'])
            ->where('attiva', false)
            ->orderByDesc('data_inizio')
            ->take(10)
            ->get();

        return view('paziente.terapie', compact('utente','paziente','terapieAttive','terapieConcluse'));
    }

    // ── STORICO ASSUNZIONI ─────────────────────────────────────────

    public function storico(Request $request)
    {
        $utente   = Auth::user();
        $paziente = $this->getPaziente();

        $query = Assunzione::whereHas('somministrazione.terapia', fn($q) =>
                $q->where('id_paziente', $paziente->id))
            ->with(['somministrazione.terapia.farmaco', 'dispositivo']);

        // Filtro per stato
        if ($stato = $request->get('stato')) {
            $query->where('stato', $stato);
        }

        // Filtro per data
        if ($dal = $request->get('dal')) {
            $query->whereDate('data_prevista', '>=', $dal);
        }
        if ($al = $request->get('al')) {
            $query->whereDate('data_prevista', '<=', $al);
        }

        // Default: ultimi 30 giorni
        if (!$request->get('dal') && !$request->get('al')) {
            $query->where('data_prevista', '>=', now()->subDays(30));
        }

        $assunzioni = $query->orderByDesc('data_prevista')->paginate(20)->withQueryString();

        // Statistiche generali
        $totaleAssunzioni = Assunzione::whereHas('somministrazione.terapia', fn($q) =>
                $q->where('id_paziente', $paziente->id))->count();
        $totalePrese = Assunzione::whereHas('somministrazione.terapia', fn($q) =>
                $q->where('id_paziente', $paziente->id))
            ->whereIn('stato', ['assunta','erogata'])->count();
        $totaleForzate = Assunzione::whereHas('somministrazione.terapia', fn($q) =>
                $q->where('id_paziente', $paziente->id))
            ->where('apertura_forzata', true)->count();

        $stats = [
            'totale'    => $totaleAssunzioni,
            'prese'     => $totalePrese,
            'forzate'   => $totaleForzate,
            'aderenza'  => $totaleAssunzioni > 0 ? round($totalePrese / $totaleAssunzioni * 100) : 0,
        ];

        return view('paziente.storico', compact('utente','paziente','assunzioni','stats'));
    }

    // ── DETTAGLIO SINGOLA ASSUNZIONE ───────────────────────────────

    public function assunzioneShow(Assunzione $assunzione)
    {
        $utente   = Auth::user();
        $paziente = $this->getPaziente();

        // Verifica appartenenza
        $idPaz = $assunzione->somministrazione->terapia->id_paziente ?? null;
        if ($idPaz !== $paziente->id) abort(403);

        $assunzione->load([
            'somministrazione.terapia.farmaco',
            'somministrazione.terapia.medico',
            'dispositivo',
        ]);

        return view('paziente.assunzione-detail', compact('utente','paziente','assunzione'));
    }

    // ── DISPOSITIVI ────────────────────────────────────────────────

    public function dispositivi()
    {
        $utente   = Auth::user();
        $paziente = $this->getPaziente();

        $dispositivi = $paziente->dispositivi()
            ->orderByRaw("FIELD(stato,'attivo','offline','errore','manutenzione')")
            ->get();

        return view('paziente.dispositivi', compact('utente','paziente','dispositivi'));
    }

    // ── INVIA MESSAGGIO AL MEDICO ──────────────────────────────────

    public function inviaMessaggio(Request $request)
    {
        $utente   = Auth::user();
        $paziente = $this->getPaziente();

        $request->validate([
            'titolo'   => ['required', 'string', 'max:100'],
            'messaggio'=> ['required', 'string'],
        ]);

        // Invia a tutti i medici del paziente
        $medici = $paziente->medici()->pluck('users.id');
        foreach ($medici as $idMedico) {
            DB::table('notifiche')->insert([
                'id_utente'   => $idMedico,
                'id_mittente' => $utente->id,
                'id_paziente' => $paziente->id,
                'titolo'      => $request->titolo,
                'messaggio'   => $request->messaggio,
                'tipo'        => 'messaggio',
                'letta'       => false,
                'data_invio'  => now(),
            ]);
        }

        return back()->with('success', '✅ Messaggio inviato al medico.');
    }

    public function notifiche()
    {
        $utente   = Auth::user();
        $paziente = $this->getPaziente();

        $notifiche = DB::table('notifiche')
            ->where('id_utente', $utente->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        // Segna come lette
        DB::table('notifiche')
            ->where('id_utente', $utente->id)
            ->where('letta', false)
            ->update(['letta' => true]);

        $nonLette = 0; // già azzerate

        return view('paziente.notifiche', compact('utente','paziente','notifiche','nonLette'));
    }
}
