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
     * Pagina dettaglio paziente: terapie, assunzioni, bottoni IoT
     */
    public function show(Paziente $paziente)
    {
        $this->autorizza($paziente);

        $paziente->load([
            'utente',
            'dispositivi',
            'terapie' => fn($q) => $q->with('farmaco', 'somministrazioni')->orderByDesc('attiva')->orderByDesc('data_inizio'),
        ]);

        // Assunzioni degli ultimi 7 giorni
        $assunzioni = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($paziente) {
                $q->where('id_paziente', $paziente->id);
            })
            ->with('somministrazione.terapia.farmaco', 'dispositivo')
            ->where('data_prevista', '>=', now()->subDays(7))
            ->orderByDesc('data_prevista')
            ->get();

        // Statistiche assunzioni oggi
        $oggiAssunzioni = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($paziente) {
                $q->where('id_paziente', $paziente->id);
            })
            ->whereDate('data_prevista', today())
            ->get();

        $stats = [
            'oggi_totali'  => $oggiAssunzioni->count(),
            'oggi_prese'   => $oggiAssunzioni->whereIn('stato', ['assunta', 'erogata'])->count(),
            'oggi_saltate' => $oggiAssunzioni->whereIn('stato', ['saltata', 'non_ritirata'])->count(),
            'oggi_attesa'  => $oggiAssunzioni->whereIn('stato', ['in_attesa', 'allarme_attivo'])->count(),
        ];

        $assunzioniOggi = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($paziente) {
                $q->where('id_paziente', $paziente->id);
            })
            ->with('somministrazione.terapia.farmaco', 'dispositivo')
            ->whereDate('data_prevista', today())
            ->orderBy('data_prevista')
            ->get();

        $storicoForzate = DB::table('eventi_dispositivo')
            ->where('id_paziente', $paziente->id)
            ->whereIn('azione', ['apertura_forzata', 'pillola_erogata'])
            ->where(function ($q) {
                $q->where('metodo_attivazione', 'medico_web')
                  ->orWhere('payload_json', 'like', '%MQTT_DIRETTO%');
            })
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('medico.pazienti.show', compact('paziente', 'assunzioni', 'stats', 'assunzioniOggi', 'storicoForzate'));
    }

    /**
     * Erogazione forzata PIN via MQTT
     */
    public function erogazioneForzata(Paziente $paziente)
    {
        $this->autorizza($paziente);

        $dispositivo = $paziente->dispositivi()->where('stato', 'attivo')->first();

        if (!$dispositivo) {
            return back()->with('error', 'Nessun dispositivo attivo per questo paziente.');
        }

        $topic = "pillmate/{$dispositivo->codice_seriale}/comandi";
        $payload = json_encode([
            'comando'   => 'eroga_farmaco',   // Firmware legge "comando", non "azione"
            'forzata'   => true,
            'medico_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            MQTT::publish($topic, $payload);

            // Registra evento nel DB
            DB::table('eventi_dispositivo')->insert([
                'id_dispositivo'      => $dispositivo->id,
                'id_paziente'         => $paziente->id,
                'azione'              => 'apertura_forzata',
                'metodo_attivazione'  => 'medico_web',
                'severita'            => 'warning',
                'messaggio'           => 'Erogazione forzata ordinata dal medico ' . Auth::user()->cognome,
                'created_at'          => now(),
            ]);

            return back()->with('success', '✅ Comando erogazione forzata inviato al dispositivo.');
        } catch (\Exception $e) {
            return back()->with('error', 'Errore MQTT: ' . $e->getMessage());
        }
    }

    /**
     * Attiva/Disattiva allarme via MQTT
     */
    public function toggleAllarme(Paziente $paziente, Request $request)
    {
        $this->autorizza($paziente);

        $dispositivo = $paziente->dispositivi()->where('stato', 'attivo')->first();

        if (!$dispositivo) {
            return back()->with('error', 'Nessun dispositivo attivo per questo paziente.');
        }

        $attiva = $request->boolean('attiva');
        $azione = $attiva ? 'attiva_allarme' : 'disattiva_allarme';

        $topic = "pillmate/{$dispositivo->codice_seriale}/comandi";
        $payload = json_encode([
            'comando'   => $azione,           // Firmware legge "comando", non "azione"
            'medico_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            MQTT::publish($topic, $payload);

            // Aggiorna stato nel DB
            $dispositivo->update(['allarme_attivo' => $attiva]);

            DB::table('eventi_dispositivo')->insert([
                'id_dispositivo'      => $dispositivo->id,
                'id_paziente'         => $paziente->id,
                'azione'              => $azione,
                'metodo_attivazione'  => 'medico_web',
                'severita'            => $attiva ? 'critico' : 'info',
                'messaggio'           => ($attiva ? 'Allarme attivato' : 'Allarme disattivato') . ' dal medico ' . Auth::user()->cognome,
                'created_at'          => now(),
            ]);

            $msg = $attiva ? '🔔 Allarme attivato sul dispositivo.' : '🔕 Allarme disattivato.';
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Errore MQTT: ' . $e->getMessage());
        }
    }

    /**
     * Aggiorna stato assunzione (presa / saltata) via AJAX
     */
    public function aggiornaAssunzione(Request $request, Assunzione $assunzione)
    {
        // Verifica che l'assunzione appartenga a un paziente del medico
        $pazienteId = $assunzione->somministrazione->terapia->id_paziente ?? null;
        if (!$pazienteId) abort(404);

        $paziente = Paziente::findOrFail($pazienteId);
        $this->autorizza($paziente);

        $nuovoStato = $request->input('stato');
        $statiValidi = ['assunta', 'saltata', 'in_attesa', 'non_ritirata'];

        if (!in_array($nuovoStato, $statiValidi)) {
            return response()->json(['error' => 'Stato non valido'], 422);
        }

        $assunzione->update([
            'stato'         => $nuovoStato,
            'confermata_da' => 'familiare', // medico agisce come supervisore
            'data_conferma' => $nuovoStato === 'assunta' ? now() : null,
        ]);

        return response()->json(['success' => true, 'stato' => $nuovoStato]);
    }

    /**
     * Aggiunge una nuova terapia al paziente
     */
    public function storeTerapia(Request $request, Paziente $paziente)
    {
        $this->autorizza($paziente);

        $data = $request->validate([
            'id_farmaco'   => ['required', 'exists:farmaci,id'],
            'data_inizio'  => ['required', 'date'],
            'data_fine'    => ['nullable', 'date', 'after:data_inizio'],
            'frequenza'    => ['nullable', 'string', 'max:50'],
            'quantita'     => ['required', 'integer', 'min:1'],
            'istruzioni'   => ['nullable', 'string'],
            'ora'          => ['required', 'date_format:H:i'],
            'giorni'       => ['required', 'array'],
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
                    'id_terapia'      => $terapia->id,
                    'ora'             => $data['ora'],
                    'giorno_settimana'=> $giorno,
                ]);
            }
        });

        return back()->with('success', 'Terapia aggiunta con successo.');
    }


    public function storico(Paziente $paziente, string $tipo = 'tutte')
    {
        $this->autorizza($paziente);

        $tipiAmmessi = ['tutte', 'oggi', 'prese', 'saltate', 'forzate'];
        if (!in_array($tipo, $tipiAmmessi, true)) {
            $tipo = 'tutte';
        }

        $query = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($paziente) {
                $q->where('id_paziente', $paziente->id);
            })
            ->with('somministrazione.terapia.farmaco', 'dispositivo');

        if ($tipo === 'oggi') {
            $query->whereDate('data_prevista', today());
        }
        if ($tipo === 'prese') {
            $query->whereIn('stato', ['assunta', 'erogata']);
        }
        if ($tipo === 'saltate') {
            $query->whereIn('stato', ['saltata', 'non_ritirata']);
        }
        if ($tipo === 'forzate') {
            $query->where('apertura_forzata', true);
        }

        $assunzioni = $query->orderByDesc('data_prevista')->paginate(20)->withQueryString();

        $tipoMeta = [
            'tutte'   => ['icon' => 'list', 'label' => 'Storico completo'],
            'oggi'    => ['icon' => 'calendar-clock', 'label' => 'Previste oggi'],
            'prese'   => ['icon' => 'check-circle-2', 'label' => 'Assunzioni prese'],
            'saltate' => ['icon' => 'x-circle', 'label' => 'Assunzioni saltate'],
            'forzate' => ['icon' => 'shield-alert', 'label' => 'Erogazioni forzate'],
        ];

        return view('medico.pazienti.storico', compact('paziente', 'assunzioni', 'tipo', 'tipoMeta'));
    }

    public function destroyTerapia(Paziente $paziente, Terapia $terapia)
    {
        $this->autorizza($paziente);

        abort_if($terapia->id_paziente !== $paziente->id, 403);

        $nomeFarmaco = $terapia->farmaco?->nome ?? 'Terapia';
        $terapia->delete();

        return back()->with('success', "Terapia '{$nomeFarmaco}' eliminata con successo.");
    }

    // ── Helpers ───────────────────────────────────────

    private function autorizza(Paziente $paziente): void
    {
        $medico = Auth::user();
        $associato = DB::table('medici_pazienti')
            ->where('id_medico', $medico->id)
            ->where('id_paziente', $paziente->id)
            ->exists();

        if (!$associato) {
            abort(403, 'Non autorizzato a visualizzare questo paziente.');
        }
    }
}
