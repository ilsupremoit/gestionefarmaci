<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use App\Models\Paziente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;

class MedicoController extends Controller
{
    // ── NOTIFICHE / MESSAGGI ────────────────────────────────────────

    public function notifiche()
    {
        $medico = Auth::user();

        // Messaggi inviati dal medico
        $inviati = DB::table('notifiche')
            ->where('id_mittente', $medico->id)
            ->orderByDesc('data_invio')
            ->paginate(20, ['*'], 'inviati');

        // Messaggi ricevuti dal medico
        $ricevuti = DB::table('notifiche')
            ->where('id_utente', $medico->id)
            ->orderByDesc('data_invio')
            ->paginate(20, ['*'], 'ricevuti');

        // Pazienti del medico per il form invio
        $pazienti = $medico->pazientiSeguiti()->with('utente')->get();

        return view('medico.notifiche', compact('medico', 'inviati', 'ricevuti', 'pazienti'));
    }

    public function inviaNotifica(Request $request)
    {
        $request->validate([
            'id_utente'  => ['required', 'exists:users,id'],
            'titolo'     => ['required', 'string', 'max:100'],
            'messaggio'  => ['required', 'string'],
            'tipo'       => ['required', 'in:info,promemoria,allarme,messaggio'],
        ]);

        DB::table('notifiche')->insert([
            'id_utente'   => $request->id_utente,
            'id_mittente' => Auth::id(),
            'titolo'      => $request->titolo,
            'messaggio'   => $request->messaggio,
            'tipo'        => $request->tipo,
            'letta'       => false,
            'data_invio'  => now(),
        ]);

        return back()->with('success', '✅ Messaggio inviato con successo.');
    }

    // ── DISPOSITIVI ─────────────────────────────────────────────────

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

    public function dispositivoComando(Request $request, Paziente $paziente, Dispositivo $dispositivo)
    {
        $this->autorizzaPaziente($paziente);
        abort_if($dispositivo->id_paziente !== $paziente->id, 403);

        $request->validate([
            'azione' => ['required', 'in:eroga_ora,attiva_allarme,disattiva_allarme,imposta_sveglia,reset'],
            'payload_extra' => ['nullable', 'array'],
        ]);

        $azione = $request->input('azione');
        $topic  = "pillmate/{$dispositivo->codice_seriale}/comandi";

        $payload = array_merge([
            'azione'    => $azione,
            'medico_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
        ], $request->input('payload_extra', []));

        try {
            MQTT::publish($topic, json_encode($payload));

            // Aggiorna DB locale
            if ($azione === 'attiva_allarme')    $dispositivo->update(['allarme_attivo' => true]);
            if ($azione === 'disattiva_allarme') $dispositivo->update(['allarme_attivo' => false]);
            if ($azione === 'imposta_sveglia' && isset($payload['ora'])) {
                $dispositivo->update(['sveglia_impostata' => $payload['ora']]);
            }

            DB::table('eventi_dispositivo')->insert([
                'id_dispositivo'     => $dispositivo->id,
                'id_paziente'        => $paziente->id,
                'topic'              => $topic,
                'azione'             => $azione,
                'metodo_attivazione' => 'medico_web',
                'severita'           => in_array($azione, ['attiva_allarme','eroga_ora']) ? 'warning' : 'info',
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
            'temperatura'      => $dispositivo->temperatura,
            'umidita'          => $dispositivo->umidita,
            'wifi_rssi'        => $dispositivo->wifi_rssi,
            'scomparto_attuale'=> $dispositivo->scomparto_attuale,
            'sveglia_impostata'=> $dispositivo->sveglia_impostata,
            'allarme_attivo'   => (bool) $dispositivo->allarme_attivo,
            'online'           => $online,
            'last_update'      => $dispositivo->ultimo_payload_at,
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function autorizzaPaziente(Paziente $paziente): void
    {
        $ok = DB::table('medici_pazienti')
            ->where('id_medico', Auth::id())
            ->where('id_paziente', $paziente->id)
            ->exists();
        if (!$ok) abort(403);
    }
}
