<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use App\Models\Paziente;
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

        // Tutti i destinatari possibili
        $pazienti  = $medico->pazientiSeguiti()->with('utente')->get();
        $medici    = User::where('ruolo', 'medico')->where('id', '!=', $medico->id)->orderBy('cognome')->get();
        $adminList = User::where('ruolo', 'admin')->orderBy('cognome')->get();

        // Messaggi inviati
        $inviati = DB::table('notifiche')
            ->where('id_mittente', $medico->id)
            ->orderByDesc('data_invio')
            ->paginate(15, ['*'], 'inviati');

        // Messaggi ricevuti
        $ricevuti = DB::table('notifiche')
            ->where('id_utente', $medico->id)
            ->orderByDesc('data_invio')
            ->paginate(15, ['*'], 'ricevuti');

        // Segna come letti
        DB::table('notifiche')
            ->where('id_utente', $medico->id)
            ->where('letta', false)
            ->update(['letta' => true, 'letto_at' => now()]);

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

        // Ultimi 20 eventi
        $eventi = DB::table('eventi_dispositivo')
            ->where('id_dispositivo', $dispositivo->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Ultima telemetria
        $ultimaTelemetria = DB::table('telemetrie_dispositivo')
            ->where('id_dispositivo', $dispositivo->id)
            ->orderByDesc('created_at')
            ->first();

        // Storico telemetria (ultimi 50 punti per grafico)
        $storicoTelemetria = DB::table('telemetrie_dispositivo')
            ->where('id_dispositivo', $dispositivo->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return view('medico.dispositivo-show', compact(
            'paziente', 'dispositivo', 'eventi', 'ultimaTelemetria', 'storicoTelemetria'
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

        Dispositivo::create([
            'codice_seriale'   => $request->codice_seriale,
            'id_paziente'      => $paziente->id,
            'nome_dispositivo' => $request->nome_dispositivo ?? 'PillMate Dispenser',
            'stato'            => 'offline',
            'allarme_attivo'   => false,
        ]);

        return back()->with('success', '📡 Dispositivo aggiunto. Appena si connette al broker MQTT apparirà online.');
    }

    /**
     * Invia un comando MQTT all'ESP32.
     *
     * IMPORTANTE: il firmware C++ legge il campo "comando" (non "azione").
     * Comandi supportati: eroga_farmaco, attiva_allarme, disattiva_allarme,
     *                     set_sveglia, get_mappa_scomparti, buzzer_test
     */
    public function dispositivoComando(Request $request, Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $request->validate([
            'azione'        => ['required', 'in:eroga_farmaco,attiva_allarme,disattiva_allarme,set_sveglia,get_mappa_scomparti,buzzer_test,reset'],
            'payload_extra' => ['nullable', 'array'],
        ]);

        $azione = $request->input('azione');
        $topic  = $dispositivo->topicComandi();

        // Il firmware C++ usa la chiave "comando" — non "azione"
        $payload = [
            'comando'   => $azione,
            'medico_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
        ];

        // Gestione speciale sveglia: il firmware si aspetta "ora" (int) e "minuto" (int)
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
            // Merge degli altri campi extra (es. id_farmaco per eroga_farmaco/attiva_allarme)
            $extra = $request->input('payload_extra', []);
            if (!empty($extra)) {
                $payload = array_merge($payload, $extra);
            }
        }

        try {
            MQTT::publish($topic, json_encode($payload));

            // Aggiorna DB locale in base all'azione
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

        return response()->json([
            'temperatura'       => $dispositivo->temperatura,
            'umidita'           => $dispositivo->umidita,
            'wifi_rssi'         => $dispositivo->wifi_rssi,
            'scomparto_attuale' => $dispositivo->scomparto_attuale,
            'sveglia_impostata' => $dispositivo->sveglia_impostata,
            'allarme_attivo'    => (bool) $dispositivo->allarme_attivo,
            'online'            => $online,
            'last_update'       => $dispositivo->ultimo_payload_at,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function autorizzaPaziente(Paziente $paziente): void
    {
        $ok = DB::table('medici_pazienti')
            ->where('id_medico', Auth::id())
            ->where('id_paziente', $paziente->id)
            ->exists();
        if (!$ok) abort(403);
    }
}
