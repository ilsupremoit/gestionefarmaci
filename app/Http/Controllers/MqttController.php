<?php

namespace App\Http\Controllers;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\ScompartoDispositivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;
use Throwable;

class MqttController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Comandi → ESP32
    // ─────────────────────────────────────────────────────────────

    public function configuraScomparti(Request $request, int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);
        $payload = [
            'comando'    => 'configura_scomparti',
            'scomparti'  => ScompartoDispositivo::buildPayloadPerDispositivo($idDispositivo),
        ];

        try {
            $this->pubblicaMqtt($dispositivo, $payload);
            return response()->json(['ok' => true, 'messaggio' => 'Configurazione inviata.', 'payload' => $payload]);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'messaggio' => 'Broker MQTT non raggiungibile.', 'payload' => $payload], 202);
        }
    }

    public function attivaAllarme(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate(['id_farmaco' => 'required|integer|exists:farmaci,id']);
        $dispositivo = Dispositivo::findOrFail($idDispositivo);
        $payload = ['comando' => 'attiva_allarme', 'id_farmaco' => (int) $request->id_farmaco];

        try {
            $this->pubblicaMqtt($dispositivo, $payload);
            return response()->json(['ok' => true, 'messaggio' => 'Allarme attivato.']);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'messaggio' => 'Broker MQTT non raggiungibile.'], 202);
        }
    }

    public function erogaFarmaco(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate(['id_farmaco' => 'required|integer|exists:farmaci,id']);
        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        $scomparto = ScompartoDispositivo::where('id_dispositivo', $idDispositivo)
            ->where('id_farmaco', $request->id_farmaco)
            ->where('quantita', '>', 0)
            ->first();

        if (!$scomparto) {
            return response()->json(['ok' => false, 'messaggio' => 'Farmaco non trovato o scomparto vuoto.'], 422);
        }

        $payload = ['comando' => 'eroga_farmaco', 'id_farmaco' => (int) $request->id_farmaco];

        try {
            $this->pubblicaMqtt($dispositivo, $payload);
            return response()->json(['ok' => true, 'messaggio' => 'Comando erogazione inviato.']);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'messaggio' => 'Broker MQTT non raggiungibile.'], 202);
        }
    }

    public function setSveglia(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate([
            'ora'    => 'required|integer|between:0,23',
            'minuto' => 'required|integer|between:0,59',
        ]);
        $dispositivo = Dispositivo::findOrFail($idDispositivo);
        $payload = ['comando' => 'set_sveglia', 'ora' => (int) $request->ora, 'minuto' => (int) $request->minuto];

        try {
            $this->pubblicaMqtt($dispositivo, $payload);
            return response()->json(['ok' => true, 'messaggio' => "Sveglia impostata."]);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'messaggio' => 'Broker MQTT non raggiungibile.'], 202);
        }
    }

    public function richiediMappa(int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);
        try {
            $this->pubblicaMqtt($dispositivo, ['comando' => 'get_mappa_scomparti']);
            return response()->json(['ok' => true, 'messaggio' => 'Richiesta mappa inviata.']);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'messaggio' => 'Broker non raggiungibile.'], 202);
        }
    }

    public function testBuzzer(int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);
        try {
            $this->pubblicaMqtt($dispositivo, ['comando' => 'buzzer_test']);
            return response()->json(['ok' => true, 'messaggio' => 'Buzzer test inviato.']);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'messaggio' => 'Broker non raggiungibile.'], 202);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Evento in arrivo dall'ESP32 via webhook interno
    // Chiamato da MqttListen quando arriva un messaggio su /eventi
    // ─────────────────────────────────────────────────────────────

    /**
     * Processa pillola_erogata ricevuto dall'ESP32.
     *
     * JSON atteso:
     * {
     *   "azione":         "pillola_erogata",
     *   "dispositivo":    "pillmate_disp_01",
     *   "scomparto_usato": 3,
     *   "id_farmaco":     101,
     *   "quantita":       12,           ← nuova quantità DOPO l'erogazione
     *   "metodo_attivazione": "BOTTONE" | "PIR" | "MQTT_DIRETTO"
     * }
     */
    public static function handlePillolaErogata(array $data, Dispositivo $dispositivo): void
    {
        $scompartoNum  = (int) ($data['scomparto_usato'] ?? 0);
        $idFarmaco     = (int) ($data['id_farmaco'] ?? 0);
        $nuovaQuantita = (int) ($data['quantita'] ?? 0);  // quantità rimasta dopo erogazione
        $metodo        = $data['metodo_attivazione'] ?? 'sconosciuto';

        // 1. Aggiorna la quantità dello scomparto nel DB
        //    Prende la più piccola tra quella attuale e quella arrivata (per sicurezza)
        $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
            ->where('numero_scomparto', $scompartoNum)
            ->first();

        if ($scomparto) {
            $qtaAggiornata = min((int) ($scomparto->quantita ?? 0), $nuovaQuantita);
            // Se il broker manda una quantità più piccola la usiamo sempre — è la fonte di verità
            // Se il broker manda una quantità più GRANDE (errore HW) prendiamo la locale
            $qtaFinale = ($nuovaQuantita < (int) ($scomparto->quantita ?? 0))
                ? $nuovaQuantita
                : max(0, (int) ($scomparto->quantita ?? 0) - 1);

            $scomparto->update([
                'quantita' => $qtaFinale,
                'pieno'    => $qtaFinale > 0,
            ]);
        }

        // 2. Cerca l'assunzione "allarme_attivo" più recente per quel farmaco/dispositivo
        $assunzione = Assunzione::where('id_dispositivo', $dispositivo->id)
            ->where('stato', 'allarme_attivo')
            ->whereHas('somministrazione.terapia', fn($q) => $q->where('id_farmaco', $idFarmaco))
            ->orderByDesc('data_prevista')
            ->first();

        // Se non trova allarme_attivo, prova in_attesa (erogazione manuale senza allarme)
        if (!$assunzione) {
            $assunzione = Assunzione::where('id_dispositivo', $dispositivo->id)
                ->where('stato', 'in_attesa')
                ->whereHas('somministrazione.terapia', fn($q) => $q->where('id_farmaco', $idFarmaco))
                ->orderByDesc('data_prevista')
                ->first();
        }

        if ($assunzione) {
            $isForzata = in_array($metodo, ['MQTT_DIRETTO']);
            $assunzione->update([
                'stato'          => $isForzata ? 'apertura_forzata' : 'erogata',
                'data_erogazione'=> now(),
                'data_conferma'  => now(),
                'confermata_da'  => in_array($metodo, ['BOTTONE', 'PIR']) ? 'paziente' : 'sistema',
                'quantita_erogata'   => $nuovaQuantita,
                'scomparto_numero'   => $scompartoNum,
                'forzata_medico'     => $isForzata,
                'note_evento'        => "Erogata via {$metodo}. Quantità rimasta: {$qtaFinale}",
            ]);
        }

        // 3. Log evento (se non già fatto da MqttListen)
        $esiste = DB::table('eventi_dispositivo')
            ->where('id_dispositivo', $dispositivo->id)
            ->where('azione', 'pillola_erogata')
            ->where('created_at', '>=', now()->subMinutes(1))
            ->exists();

        if (!$esiste) {
            DB::table('eventi_dispositivo')->insert([
                'id_dispositivo'     => $dispositivo->id,
                'id_paziente'        => $dispositivo->id_paziente,
                'azione'             => 'pillola_erogata',
                'metodo_attivazione' => $metodo,
                'severita'           => 'info',
                'messaggio'          => "Pillola erogata da scomparto {$scompartoNum}. Quantità rimasta: {$qtaFinale}",
                'payload_json'       => json_encode($data),
                'created_at'         => now(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helper privato
    // ─────────────────────────────────────────────────────────────

    private function pubblicaMqtt(Dispositivo $dispositivo, array $payload): void
    {
        config([
            'mqtt-client.connections.default.client_id' => env('MQTT_CLIENT_ID', 'laravel-pillmate') . '-' . uniqid(),
            'mqtt-client.connections.default.connection_settings.connect_timeout' => 2,
            'mqtt-client.connections.default.connection_settings.socket_timeout'  => 2,
        ]);

        MQTT::publish(
            $dispositivo->topicComandi(),
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
    }
}
