<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use App\Models\Farmaco;
use App\Models\Paziente;
use App\Models\ScompartoDispositivo;
use App\Models\Terapia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;

class MedicoController extends Controller
{
    // ── NOTIFICHE / MESSAGGI ──────────────────────────────────────

    public function notifiche()
    {
        $medico = Auth::user();

        $pazienti  = $medico->pazientiSeguiti()->with('utente')->get();
        $medici    = User::where('ruolo', 'medico')->where('id', '!=', $medico->id)->orderBy('cognome')->get();
        $adminList = User::where('ruolo', 'admin')->orderBy('cognome')->get();

        $inviati = DB::table('notifiche')
            ->where('id_mittente', $medico->id)
            ->orderByDesc('data_invio')
            ->paginate(15, ['*'], 'inviati');

        $ricevuti = DB::table('notifiche')
            ->where('id_utente', $medico->id)
            ->orderByDesc('data_invio')
            ->paginate(15, ['*'], 'ricevuti');

        DB::table('notifiche')
            ->where('id_utente', $medico->id)
            ->where('letta', false)
            ->update(['letta' => true]);

        return view('medico.notifiche', compact('medico', 'inviati', 'ricevuti', 'pazienti', 'medici', 'adminList'));
    }

    public function inviaNotifica(Request $request)
    {
        $request->validate([
            'id_utente'  => ['required', 'exists:users,id'],
            'titolo'     => ['required', 'string', 'max:100'],
            'messaggio'  => ['required', 'string'],
            'tipo'       => ['required', 'in:info,promemoria,allarme,messaggio'],
        ]);

        $dest = User::findOrFail($request->id_utente);
        if ($dest->id === Auth::id()) {
            return back()->with('error', '❌ Non puoi inviare un messaggio a te stesso.');
        }

        DB::table('notifiche')->insert([
            'id_utente'   => $request->id_utente,
            'id_mittente' => Auth::id(),
            'titolo'      => $request->titolo,
            'messaggio'   => $request->messaggio,
            'tipo'        => $request->tipo,
            'letta'       => false,
            'data_invio'  => now(),
        ]);

        return back()->with('success', "✅ Messaggio inviato a {$dest->nome} {$dest->cognome}.");
    }

    // ── DISPOSITIVI ───────────────────────────────────────────────

    public function dispositivoShow(Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $eventi = DB::table('eventi_dispositivo')
            ->where('id_dispositivo', $dispositivo->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $ultimaTelemetria = DB::table('telemetrie_dispositivo')
            ->where('id_dispositivo', $dispositivo->id)
            ->orderByDesc('created_at')
            ->first();

        $storicoTelemetria = DB::table('telemetrie_dispositivo')
            ->where('id_dispositivo', $dispositivo->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        // Carica gli 8 scomparti con farmaco e terapia associata
        $scomparti = $this->getScomparti($dispositivo);

        // Farmaci disponibili per il dropdown
        $farmaci = Farmaco::orderBy('nome')->get();

        // Terapie attive del paziente per il dropdown
        $terapieAttive = Terapia::with('farmaco', 'somministrazioni')
            ->where('id_paziente', $paziente->id)
            ->where('attiva', true)
            ->get();

        return view('medico.dispositivo-show', compact(
            'paziente', 'dispositivo', 'eventi', 'ultimaTelemetria',
            'storicoTelemetria', 'scomparti', 'farmaci', 'terapieAttive'
        ));
    }

    public function dispositivoStore(Request $request, Paziente $paziente)
    {
        $this->autorizzaPaziente($paziente);

        $request->validate([
            'codice_seriale'   => ['required', 'string', 'max:100', 'unique:dispositivi,codice_seriale'],
            'nome_dispositivo' => ['nullable', 'string', 'max:50'],
        ], [
            'codice_seriale.required' => 'Il codice seriale è obbligatorio.',
            'codice_seriale.unique'   => 'Questo codice seriale è già registrato.',
        ]);

        $dispositivo = Dispositivo::create([
            'codice_seriale'   => $request->codice_seriale,
            'id_paziente'      => $paziente->id,
            'nome_dispositivo' => $request->nome_dispositivo ?? 'PillMate Dispenser',
            'stato'            => 'offline',
            'allarme_attivo'   => false,
        ]);

        // Crea i 8 scomparti vuoti con gli angoli precalcolati
        for ($i = 1; $i <= 8; $i++) {
            ScompartoDispositivo::create([
                'id_dispositivo'  => $dispositivo->id,
                'numero_scomparto'=> $i,
                'angolo'          => ScompartoDispositivo::ANGOLI[$i - 1],
                'id_farmaco'      => null,
                'pieno'           => false,
                'quantita'        => 0,
            ]);
        }

        return back()->with('success', '📡 Dispositivo aggiunto con 8 scomparti vuoti. Configurali qui sotto.');
    }

    // ── GESTIONE SCOMPARTI ────────────────────────────────────────

    /**
     * Salva la configurazione di tutti gli 8 scomparti e
     * pubblica via MQTT il comando "configura_scomparti" all'ESP32.
     */
    public function scompartiSalva(Request $request, Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $request->validate([
            'scomparti'                    => ['required', 'array', 'size:8'],
            'scomparti.*.numero_scomparto' => ['required', 'integer', 'between:1,8'],
            'scomparti.*.id_farmaco'       => ['nullable', 'exists:farmaci,id'],
            'scomparti.*.id_terapia'       => ['nullable', 'exists:terapie,id'],
            'scomparti.*.pieno'            => ['nullable', 'boolean'],
            'scomparti.*.quantita'         => ['nullable', 'integer', 'min:0'],
        ]);

        $mqttPayload = [];

        foreach ($request->scomparti as $s) {
            $num      = (int) $s['numero_scomparto'];
            $idFarm   = $s['id_farmaco'] ?: null;
            $idTer    = $s['id_terapia'] ?: null;
            $quantita = max(0, (int) ($s['quantita'] ?? 0));
            $pieno    = $quantita > 0;
            $angolo   = ScompartoDispositivo::ANGOLI[$num - 1];

            // Upsert: aggiorna o crea lo scomparto
            ScompartoDispositivo::updateOrCreate(
                ['id_dispositivo' => $dispositivo->id, 'numero_scomparto' => $num],
                ['angolo' => $angolo, 'id_farmaco' => $idFarm, 'id_terapia' => $idTer, 'pieno' => $pieno, 'quantita' => $quantita]
            );

            // Prepara payload MQTT solo per scomparti con farmaco
            if ($idFarm) {
                $farmaco = Farmaco::find($idFarm);
                $mqttPayload[] = [
                    'numero'       => $num,
                    'id_farmaco'   => (int) $idFarm,
                    'nome_farmaco' => $farmaco?->nome ?? 'Farmaco',
                    'quantita'     => $quantita,
                ];
            } else {
                // Scomparto vuoto — lo comunichiamo ugualmente
                $mqttPayload[] = [
                    'numero'       => $num,
                    'id_farmaco'   => 0,
                    'nome_farmaco' => '---',
                    'quantita'     => 0,
                ];
            }
        }

        // Pubblica configurazione scomparti via MQTT
        $payload = json_encode([
            'comando'    => 'configura_scomparti',
            'scomparti'  => $mqttPayload,
            'medico_id'  => Auth::id(),
            'timestamp'  => now()->toDateTimeString(),
        ]);

        try {
            config(['mqtt-client.connections.default.connection_settings.connect_timeout' => 2]);
            config(['mqtt-client.connections.default.connection_settings.socket_timeout' => 2]);
            MQTT::publish($dispositivo->topicComandi(), $payload);
            $mqttOk = true;
        } catch (\Throwable $e) {
            report($e);
            $mqttOk = false;
        }

        // Log evento
        DB::table('eventi_dispositivo')->insert([
            'id_dispositivo'     => $dispositivo->id,
            'id_paziente'        => $paziente->id,
            'topic'              => $dispositivo->topicComandi(),
            'azione'             => 'configura_scomparti',
            'metodo_attivazione' => 'medico_web',
            'severita'           => 'info',
            'messaggio'          => 'Configurazione scomparti aggiornata dal Dr. ' . Auth::user()->cognome,
            'payload_json'       => $payload,
            'created_at'         => now(),
        ]);

        $msg = $mqttOk
            ? '✅ Scomparti salvati e configurazione inviata al dispositivo.'
            : '⚠️ Scomparti salvati nel DB ma MQTT non disponibile (il dispositivo aggiornerà al prossimo collegamento).';

        return back()->with($mqttOk ? 'success' : 'warning', $msg);
    }

    /**
     * Erogazione forzata di uno scomparto specifico scelto dal medico.
     * Invia "eroga_farmaco" con id_farmaco + numero_scomparto.
     */
    public function erogazioneForzata(Request $request, Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $request->validate([
            'numero_scomparto' => ['required', 'integer', 'between:1,8'],
        ]);

        $num = (int) $request->numero_scomparto;

        // Recupera lo scomparto dal DB
        $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
            ->where('numero_scomparto', $num)
            ->first();

        if (!$scomparto || !$scomparto->id_farmaco) {
            return back()->with('error', "❌ Lo scomparto {$num} non ha un farmaco assegnato.");
        }

        $farmaco = Farmaco::find($scomparto->id_farmaco);

        $payload = json_encode([
            'comando'          => 'eroga_farmaco',
            'id_farmaco'       => (int) $scomparto->id_farmaco,
            'numero_scomparto' => $num,
            'nome_farmaco'     => $farmaco?->nome ?? '---',
            'medico_id'        => Auth::id(),
            'forzata'          => true,
            'timestamp'        => now()->toDateTimeString(),
        ]);

        try {
            MQTT::publish($dispositivo->topicComandi(), $payload);
        } catch (\Exception $e) {
            return back()->with('error', '❌ Errore MQTT: ' . $e->getMessage());
        }

        // Aggiorna quantità locale (il valore reale verrà comunque risincronizzato dagli eventi MQTT)
        $nuovaQuantita = max(0, (int) $scomparto->quantita - 1);
        $scomparto->update(['quantita' => $nuovaQuantita, 'pieno' => $nuovaQuantita > 0]);

        DB::table('eventi_dispositivo')->insert([
            'id_dispositivo'     => $dispositivo->id,
            'id_paziente'        => $paziente->id,
            'topic'              => $dispositivo->topicComandi(),
            'azione'             => 'apertura_forzata',
            'metodo_attivazione' => 'medico_web',
            'severita'           => 'warning',
            'messaggio'          => "Erogazione forzata scomparto {$num} ({$farmaco?->nome}) dal Dr. " . Auth::user()->cognome,
            'payload_json'       => $payload,
            'created_at'         => now(),
        ]);

        return back()->with('success', "💊 Erogazione forzata scomparto {$num} inviata al dispositivo.");
    }

    /**
     * Attiva allarme per lo scomparto/farmaco specificato.
     */
    public function attivaAllarme(Request $request, Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $request->validate([
            'numero_scomparto' => ['required', 'integer', 'between:1,8'],
        ]);

        $num = (int) $request->numero_scomparto;
        $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
            ->where('numero_scomparto', $num)->first();

        if (!$scomparto || !$scomparto->id_farmaco) {
            return response()->json(['success' => false, 'error' => "Scomparto {$num} senza farmaco."]);
        }

        $payload = json_encode([
            'comando'          => 'attiva_allarme',
            'id_farmaco'       => (int) $scomparto->id_farmaco,
            'numero_scomparto' => $num,
            'medico_id'        => Auth::id(),
            'timestamp'        => now()->toDateTimeString(),
        ]);

        try {
            MQTT::publish($dispositivo->topicComandi(), $payload);
            $dispositivo->update(['allarme_attivo' => true]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Disattiva allarme.
     */
    public function disattivaAllarme(Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $payload = json_encode([
            'comando'   => 'disattiva_allarme',
            'medico_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            MQTT::publish($dispositivo->topicComandi(), $payload);
            $dispositivo->update(['allarme_attivo' => false]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function dispositivoComando(Request $request, Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $request->validate([
            'azione'        => ['required', 'in:eroga_farmaco,attiva_allarme,disattiva_allarme,set_sveglia,buzzer_test,reset'],
            'payload_extra' => ['nullable', 'array'],
        ]);

        $azione = $request->input('azione');
        $topic  = $dispositivo->topicComandi();

        $payload = [
            'comando'   => $azione,
            'medico_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
        ];

        if ($azione === 'set_sveglia') {
            $oraRaw = $request->input('payload_extra.ora', '08:00');
            if (str_contains($oraRaw, ':')) {
                [$h, $m] = explode(':', $oraRaw);
                $payload['ora']    = (int) $h;
                $payload['minuto'] = (int) $m;
            } else {
                $payload['ora']    = (int) $oraRaw;
                $payload['minuto'] = (int) ($request->input('payload_extra.minuto', 0));
            }
        } else {
            $extra = $request->input('payload_extra', []);
            if (!empty($extra)) {
                $payload = array_merge($payload, $extra);
            }
        }

        try {
            MQTT::publish($topic, json_encode($payload));

            if ($azione === 'attiva_allarme')    $dispositivo->update(['allarme_attivo' => true]);
            if ($azione === 'disattiva_allarme') $dispositivo->update(['allarme_attivo' => false]);
            if ($azione === 'set_sveglia') {
                $sveglia = sprintf('%02d:%02d:00', $payload['ora'], $payload['minuto']);
                $dispositivo->update(['sveglia_impostata' => $sveglia]);
            }

            DB::table('eventi_dispositivo')->insert([
                'id_dispositivo'     => $dispositivo->id,
                'id_paziente'        => $paziente->id,
                'topic'              => $topic,
                'azione'             => $azione,
                'metodo_attivazione' => 'medico_web',
                'severita'           => in_array($azione, ['attiva_allarme', 'eroga_farmaco']) ? 'warning' : 'info',
                'messaggio'          => "Comando '{$azione}' inviato dal Dr. " . Auth::user()->cognome,
                'payload_json'       => json_encode($payload),
                'created_at'         => now(),
            ]);

            return response()->json(['success' => true, 'azione' => $azione]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function telemetriaLive(Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $dispositivo->refresh();
        $online = $dispositivo->stato === 'attivo'
            && $dispositivo->ultimo_payload_at
            && \Carbon\Carbon::parse($dispositivo->ultimo_payload_at)->diffInMinutes(now()) <= 5;

        // Scomparti aggiornati per polling live
        $scomparti = $this->getScomparti($dispositivo);

        return response()->json([
            'temperatura'       => $dispositivo->temperatura,
            'umidita'           => $dispositivo->umidita,
            'wifi_rssi'         => $dispositivo->wifi_rssi,
            'scomparto_attuale' => $dispositivo->scomparto_attuale,
            'sveglia_impostata' => $dispositivo->sveglia_impostata,
            'allarme_attivo'    => (bool) $dispositivo->allarme_attivo,
            'online'            => $online,
            'last_update'       => $dispositivo->ultimo_payload_at,
            'scomparti'         => $scomparti,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Restituisce array 8 scomparti con info farmaco/terapia.
     * Crea gli slot mancanti in automatico.
     */
    private function getScomparti(Dispositivo $dispositivo): array
    {
        $esistenti = ScompartoDispositivo::with('farmaco', 'terapia.farmaco')
            ->where('id_dispositivo', $dispositivo->id)
            ->get()
            ->keyBy('numero_scomparto');

        $risultato = [];
        for ($i = 1; $i <= 8; $i++) {
            if ($esistenti->has($i)) {
                $s = $esistenti[$i];
                $risultato[$i] = [
                    'numero'        => $i,
                    'angolo'        => ScompartoDispositivo::ANGOLI[$i - 1],
                    'id_farmaco'    => $s->id_farmaco,
                    'nome_farmaco'  => $s->farmaco?->nome,
                    'dose_farmaco'  => $s->farmaco?->dose,
                    'id_terapia'    => $s->id_terapia,
                    'terapia_info'  => $s->terapia ? $s->terapia->farmaco?->nome . ' — ' . $s->terapia->somministrazioni->map(fn($x) => substr($x->ora,0,5).' '.$x->giorno_settimana)->join(', ') : null,
                    'pieno'         => (bool) $s->pieno,
                    'quantita'      => (int) ($s->quantita ?? 0),
                ];
            } else {
                // Crea slot vuoto
                ScompartoDispositivo::create([
                    'id_dispositivo'   => $dispositivo->id,
                    'numero_scomparto' => $i,
                    'angolo'           => ScompartoDispositivo::ANGOLI[$i - 1],
                    'pieno'            => false,
                    'quantita'         => 0,
                ]);
                $risultato[$i] = [
                    'numero'       => $i,
                    'angolo'       => ScompartoDispositivo::ANGOLI[$i - 1],
                    'id_farmaco'   => null,
                    'nome_farmaco' => null,
                    'dose_farmaco' => null,
                    'id_terapia'   => null,
                    'terapia_info' => null,
                    'pieno'        => false,
                    'quantita'     => 0,
                ];
            }
        }
        return $risultato;
    }

    private function autorizzaPaziente(Paziente $paziente): void
    {
        $ok = DB::table('medici_pazienti')
            ->where('id_medico', Auth::id())
            ->where('id_paziente', $paziente->id)
            ->exists();
        if (!$ok) abort(403);
    }
}
