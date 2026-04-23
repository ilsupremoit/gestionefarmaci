<?php

namespace App\Http\Controllers;

use App\Models\Dispositivo;
use App\Models\ScompartoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Gestisce i comandi che Laravel pubblica verso l ESP32.
 *
 * Comandi supportati dall ESP32 (vedere main.cpp -> callback()):
 *   configura_scomparti, attiva_allarme, eroga_farmaco,
 *   set_sveglia, get_mappa_scomparti, buzzer_test
 */
class MqttController extends Controller
{
    // â”€â”€ Invia la mappa completa scomparti->farmaci all ESP32 â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function configuraScomparti(Request $request, int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        $payload = json_encode([
            'comando'   => 'configura_scomparti',
            'scomparti' => ScompartoDispositivo::buildPayloadPerDispositivo($idDispositivo),
        ]);

        MQTT::publish($dispositivo->topicComandi(), $payload);

        return response()->json([
            'ok'        => true,
            'messaggio' => 'Configurazione inviata al dispositivo.',
            'payload'   => json_decode($payload),
        ]);
    }

    // â”€â”€ Attiva allarme (buzzer + OLED), il paziente conferma con PIR/tasto â”€â”€

    public function attivaAllarme(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate(['id_farmaco' => 'required|integer|exists:farmaci,id']);

        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish($dispositivo->topicComandi(), json_encode([
            'comando'    => 'attiva_allarme',
            'id_farmaco' => (int) $request->id_farmaco,
        ]));

        return response()->json(['ok' => true, 'messaggio' => 'Allarme attivato.']);
    }

    // â”€â”€ Eroga subito (remoto, senza conferma paziente) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function erogaFarmaco(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate(['id_farmaco' => 'required|integer|exists:farmaci,id']);

        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        $scomparto = ScompartoDispositivo::where('id_dispositivo', $idDispositivo)
            ->where('id_farmaco', $request->id_farmaco)
            ->where('quantita', '>', 0)
            ->first();

        if (! $scomparto) {
            return response()->json([
                'ok'        => false,
                'messaggio' => 'Farmaco non trovato in nessuno scomparto pieno.',
            ], 422);
        }

        MQTT::publish($dispositivo->topicComandi(), json_encode([
            'comando'    => 'eroga_farmaco',
            'id_farmaco' => (int) $request->id_farmaco,
        ]));

        return response()->json(['ok' => true, 'messaggio' => 'Comando erogazione inviato.']);
    }

    // â”€â”€ Aggiorna sveglia nella flash dell ESP32 â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function setSveglia(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate([
            'ora'    => 'required|integer|between:0,23',
            'minuto' => 'required|integer|between:0,59',
        ]);

        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish($dispositivo->topicComandi(), json_encode([
            'comando' => 'set_sveglia',
            'ora'     => (int) $request->ora,
            'minuto'  => (int) $request->minuto,
        ]));

        return response()->json([
            'ok'        => true,
            'messaggio' => "Sveglia impostata alle {$request->ora}:{$request->minuto}.",
        ]);
    }

    // â”€â”€ Richiede all ESP32 di pubblicare la sua mappa attuale â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function richiediMappa(int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish(
            $dispositivo->topicComandi(),
            json_encode(['comando' => 'get_mappa_scomparti'])
        );

        return response()->json(['ok' => true, 'messaggio' => 'Richiesta mappa inviata.']);
    }

    // â”€â”€ Test buzzer â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function testBuzzer(int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish(
            $dispositivo->topicComandi(),
            json_encode(['comando' => 'buzzer_test'])
        );

        return response()->json(['ok' => true, 'messaggio' => 'Buzzer test inviato.']);
    }
}
