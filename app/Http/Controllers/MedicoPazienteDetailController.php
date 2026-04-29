<?php

namespace App\Http\Controllers;

use App\Models\Assunzione;
use App\Models\Paziente;
use App\Models\Somministrazione;
use App\Models\Terapia;
use App\Models\Farmaco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;

class MedicoPazienteDetailController extends Controller
{
    /**
     * Pagina dettaglio paziente
     */
    public function show(Paziente $paziente)
    {
        $this->autorizza($paziente);

        $paziente->load([
            'utente',
            'dispositivi',
            'terapie' => fn($q) => $q->with('farmaco', 'somministrazioni')
                ->orderByDesc('attiva')->orderByDesc('data_inizio'),
        ]);

        $stats = $this->calcolaTotaliRapidi($paziente->id);

        return view('medico.pazienti.show', compact('paziente', 'stats'));
    }

    /**
     * Pagina storico assunzioni per tipo.
     * $tipo: prese | saltate | forzate | oggi
     */
    public function storico(Request $request, Paziente $paziente, string $tipo)
    {
        $this->autorizza($paziente);

        $tipiValidi = ['prese', 'saltate', 'forzate', 'oggi'];
        abort_if(!in_array($tipo, $tipiValidi), 404);

        $q = Assunzione::whereHas('somministrazione.terapia', fn($q) => $q->where('id_paziente', $paziente->id))
            ->with('somministrazione.terapia.farmaco', 'dispositivo', 'medicoForzante');

        match ($tipo) {
            'prese'   => $q->whereIn('stato', ['assunta', 'erogata']),
            'saltate' => $q->whereIn('stato', ['saltata', 'non_ritirata']),
            'forzate' => $q->where(function ($sub) {
                $sub->where('forzata_medico', true)
                    ->orWhere('apertura_forzata', true)
                    ->orWhere('stato', 'apertura_forzata');
            }),
            'oggi'    => $q->whereDate('data_prevista', today()),
        };

        $assunzioni = $q->orderByDesc('data_prevista')->paginate(50);

        $tipoMeta = [
            'prese'   => ['label' => 'Pillole prese',     'color' => 'green'],
            'saltate' => ['label' => 'Pillole saltate',   'color' => 'red'],
            'forzate' => ['label' => 'Erogazioni forzate','color' => 'orange'],
            'oggi'    => ['label' => 'Previste oggi',     'color' => 'blue'],
        ];

        $totali = $this->calcolaTotaliRapidi($paziente->id);

        return view('medico.pazienti.storico', compact(
            'paziente', 'assunzioni', 'tipo', 'tipoMeta', 'totali'
        ));
    }

    // ── Terapie CRUD ─────────────────────────────────────────────

    public function storeTerapia(Request $request, Paziente $paziente)
    {
        $this->autorizza($paziente);

        $data = $request->validate([
            'id_farmaco'  => ['required', 'exists:farmaci,id'],
            'data_inizio' => ['required', 'date'],
            'data_fine'   => ['nullable', 'date', 'after:data_inizio'],
            'frequenza'   => ['nullable', 'string', 'max:50'],
            'quantita'    => ['required', 'integer', 'min:1'],
            'istruzioni'  => ['nullable', 'string'],
            'ora'         => ['required', 'date_format:H:i'],
            'giorni'      => ['required', 'array'],
        ]);

        DB::transaction(function () use ($data, $paziente) {
            $terapia = Terapia::create([
                'id_paziente' => $paziente->id,
                'id_medico'   => Auth::id(),
                'id_farmaco'  => $data['id_farmaco'],
                'data_inizio' => $data['data_inizio'],
                'data_fine'   => $data['data_fine'] ?? null,
                'frequenza'   => $data['frequenza'] ?? null,
                'quantita'    => $data['quantita'],
                'istruzioni'  => $data['istruzioni'] ?? null,
                'attiva'      => true,
            ]);

            foreach ($data['giorni'] as $giorno) {
                Somministrazione::create([
                    'id_terapia'       => $terapia->id,
                    'ora'              => $data['ora'],
                    'giorno_settimana' => $giorno,
                ]);
            }
        });

        return back()->with('success', '✅ Terapia aggiunta con successo.');
    }

    /**
     * Modifica una terapia esistente (form inline nella show).
     */
    public function updateTerapia(Request $request, Paziente $paziente, Terapia $terapia)
    {
        $this->autorizza($paziente);
        abort_if($terapia->id_paziente !== $paziente->id, 403);

        $data = $request->validate([
            'id_farmaco'  => ['required', 'exists:farmaci,id'],
            'data_inizio' => ['required', 'date'],
            'data_fine'   => ['nullable', 'date', 'after:data_inizio'],
            'frequenza'   => ['nullable', 'string', 'max:50'],
            'quantita'    => ['required', 'integer', 'min:1'],
            'istruzioni'  => ['nullable', 'string'],
            'ora'         => ['required', 'date_format:H:i'],
            'giorni'      => ['required', 'array'],
            'attiva'      => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $terapia) {
            $terapia->update([
                'id_farmaco'  => $data['id_farmaco'],
                'data_inizio' => $data['data_inizio'],
                'data_fine'   => $data['data_fine'] ?? null,
                'frequenza'   => $data['frequenza'] ?? null,
                'quantita'    => $data['quantita'],
                'istruzioni'  => $data['istruzioni'] ?? null,
                'attiva'      => $data['attiva'] ?? true,
            ]);

            // Aggiorna le somministrazioni: cancella e ricrea
            $terapia->somministrazioni()->delete();
            foreach ($data['giorni'] as $giorno) {
                Somministrazione::create([
                    'id_terapia'       => $terapia->id,
                    'ora'              => $data['ora'],
                    'giorno_settimana' => $giorno,
                ]);
            }
        });

        return back()->with('success', '✅ Terapia aggiornata con successo.');
    }

    /**
     * Elimina una terapia (soft: la disattiva, oppure delete reale).
     */
    public function destroyTerapia(Paziente $paziente, Terapia $terapia)
    {
        $this->autorizza($paziente);
        abort_if($terapia->id_paziente !== $paziente->id, 403);

        // Disattiva invece di eliminare: conserva lo storico assunzioni
        $terapia->update(['attiva' => false]);

        return back()->with('success', '🗑 Terapia disattivata. Lo storico delle assunzioni è conservato.');
    }

    // ── IoT ───────────────────────────────────────────────────────

    public function erogazioneForzata(Paziente $paziente)
    {
        $this->autorizza($paziente);
        $dispositivo = $paziente->dispositivi()->where('stato', 'attivo')->first();
        if (!$dispositivo) return back()->with('error', 'Nessun dispositivo attivo.');

        $topic   = "pillmate/{$dispositivo->codice_seriale}/comandi";
        $payload = json_encode(['comando' => 'eroga_farmaco', 'forzata' => true, 'medico_id' => Auth::id(), 'timestamp' => now()->toDateTimeString()]);

        try {
            MQTT::publish($topic, $payload);
            DB::table('eventi_dispositivo')->insert([
                'id_dispositivo'     => $dispositivo->id,
                'id_paziente'        => $paziente->id,
                'azione'             => 'apertura_forzata',
                'metodo_attivazione' => 'medico_web',
                'severita'           => 'warning',
                'messaggio'          => 'Erogazione forzata dal medico ' . Auth::user()->cognome,
                'created_at'         => now(),
            ]);
            return back()->with('success', '✅ Comando erogazione forzata inviato.');
        } catch (\Exception $e) {
            return back()->with('error', 'Errore MQTT: ' . $e->getMessage());
        }
    }

    public function toggleAllarme(Paziente $paziente, Request $request)
    {
        $this->autorizza($paziente);
        $dispositivo = $paziente->dispositivi()->where('stato', 'attivo')->first();
        if (!$dispositivo) return back()->with('error', 'Nessun dispositivo attivo.');

        $attiva = $request->boolean('attiva');
        $azione = $attiva ? 'attiva_allarme' : 'disattiva_allarme';
        $topic  = "pillmate/{$dispositivo->codice_seriale}/comandi";
        $payload = json_encode(['comando' => $azione, 'medico_id' => Auth::id(), 'timestamp' => now()->toDateTimeString()]);

        try {
            MQTT::publish($topic, $payload);
            $dispositivo->update(['allarme_attivo' => $attiva]);
            DB::table('eventi_dispositivo')->insert([
                'id_dispositivo'     => $dispositivo->id,
                'id_paziente'        => $paziente->id,
                'azione'             => $azione,
                'metodo_attivazione' => 'medico_web',
                'severita'           => $attiva ? 'critico' : 'info',
                'messaggio'          => ($attiva ? 'Allarme attivato' : 'Allarme disattivato') . ' dal medico ' . Auth::user()->cognome,
                'created_at'         => now(),
            ]);
            return back()->with('success', $attiva ? '🔔 Allarme attivato.' : '🔕 Allarme disattivato.');
        } catch (\Exception $e) {
            return back()->with('error', 'Errore MQTT: ' . $e->getMessage());
        }
    }

    public function aggiornaAssunzione(Request $request, Assunzione $assunzione)
    {
        $pazienteId = $assunzione->somministrazione->terapia->id_paziente ?? null;
        if (!$pazienteId) abort(404);
        $paziente = Paziente::findOrFail($pazienteId);
        $this->autorizza($paziente);

        $nuovoStato = $request->input('stato');
        if (!in_array($nuovoStato, ['assunta', 'saltata', 'in_attesa', 'non_ritirata'])) {
            return response()->json(['error' => 'Stato non valido'], 422);
        }

        $assunzione->update([
            'stato'         => $nuovoStato,
            'confermata_da' => 'familiare',
            'data_conferma' => $nuovoStato === 'assunta' ? now() : null,
        ]);

        return response()->json(['success' => true, 'stato' => $nuovoStato]);
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function calcolaTotaliRapidi(int $idPaziente): array
    {
        $base = fn() => Assunzione::whereHas('somministrazione.terapia', fn($q) => $q->where('id_paziente', $idPaziente));

        return [
            'prese'   => $base()->whereIn('stato', ['assunta', 'erogata'])->count(),
            'saltate' => $base()->whereIn('stato', ['saltata', 'non_ritirata'])->count(),
            'forzate' => $base()->where(fn($q) => $q->where('apertura_forzata', true))->count(),
            'oggi'    => $base()->whereDate('data_prevista', today())->count(),
        ];
    }

    private function autorizza(Paziente $paziente): void
    {
        $ok = DB::table('medici_pazienti')
            ->where('id_medico', Auth::id())
            ->where('id_paziente', $paziente->id)
            ->exists();
        if (!$ok) abort(403);
    }
}
