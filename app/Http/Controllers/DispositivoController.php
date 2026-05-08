<?php

namespace App\Http\Controllers;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Farmaco;
use App\Models\ScompartoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class DispositivoController extends Controller
{
    /**
     * Pagina configurazione scomparti.
     */
    public function scomparti(int $idDispositivo): View
    {
        $dispositivo = Dispositivo::with(['scomparti.farmaco', 'paziente.utente'])
                                  ->findOrFail($idDispositivo);
        $farmaci     = Farmaco::orderBy('nome')->get();

        if ($dispositivo->scomparti->count() < ScompartoDispositivo::NUM_SCOMPARTI) {
            ScompartoDispositivo::inizializzaPerDispositivo($idDispositivo);
            $dispositivo->load('scomparti.farmaco');
        }

        return view('dispositivi.scomparti', compact('dispositivo', 'farmaci'));
    }

    /**
     * Salva la configurazione e la invia all ESP32 via MQTT.
     */
    public function salvaScomparti(Request $request, int $idDispositivo): RedirectResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);
        $datiForm    = $request->input('scomparti', []);

        foreach ($datiForm as $numero => $valori) {
            $numero = (int) $numero;
            if ($numero < 1 || $numero > ScompartoDispositivo::NUM_SCOMPARTI) continue;

            $idFarmaco = isset($valori['id_farmaco']) && (int)$valori['id_farmaco'] > 0
                ? (int) $valori['id_farmaco']
                : null;

            ScompartoDispositivo::updateOrCreate(
                ['id_dispositivo' => $idDispositivo, 'numero_scomparto' => $numero],
                [
                    'angolo'     => ScompartoDispositivo::calcolaAngolo($numero),
                    'id_farmaco' => $idFarmaco,
                    'quantita'   => max(0, (int) ($valori['quantita'] ?? 0)),
                    'pieno'      => ((int) ($valori['quantita'] ?? 0)) > 0,
                ]
            );
        }

        // ── Resetta allarmi pendenti prima di riconfigurare ──────────────
        // Se esiste un'assunzione in allarme_attivo per questo dispositivo,
        // invia disattiva_allarme all'ESP32 e segna le assunzioni come saltate
        // (non hanno avuto conferma), così lo scheduler non le ri-allarma.
        $this->resetlaAllarmiPendenti($dispositivo);

        // Invia la nuova configurazione scomparti
        app(MqttController::class)->configuraScomparti(new Request(), $idDispositivo);

        return redirect()
            ->route('dispositivi.scomparti', $idDispositivo)
            ->with('success', 'Configurazione salvata e inviata al dispositivo.');
    }

    /**
     * Disattiva eventuali allarmi attivi sull'ESP32 e resetta le assunzioni
     * allarme_attivo/in_attesa stantie nel DB per evitare re-allarmi indesiderati
     * dopo una riconfigurazione degli scomparti.
     */
    private function resetlaAllarmiPendenti(Dispositivo $dispositivo): void
    {
        $haAllarmiAttivi = Assunzione::where('id_dispositivo', $dispositivo->id)
            ->whereIn('stato', ['allarme_attivo'])
            ->exists();

        if ($haAllarmiAttivi) {
            // 1. Invia disattiva_allarme all'ESP32 con client dedicato
            try {
                $clientId = 'pillmate-reset-' . uniqid();
                $settings = (new ConnectionSettings())
                    ->setUsername(env('MQTT_AUTH_USERNAME'))
                    ->setPassword(env('MQTT_AUTH_PASSWORD'))
                    ->setUseTls(true)
                    ->setTlsSelfSignedAllowed(true)
                    ->setTlsVerifyPeer(false)
                    ->setTlsVerifyPeerName(false)
                    ->setConnectTimeout(10)
                    ->setSocketTimeout(10);

                $client = new MqttClient(
                    env('MQTT_HOST'),
                    (int) env('MQTT_PORT', 8883),
                    $clientId
                );

                $client->connect($settings, true);
                $client->publish(
                    $dispositivo->topicComandi(),
                    json_encode(['comando' => 'disattiva_allarme']),
                    1
                );
                $client->disconnect();
            } catch (\Throwable $e) {
                // Non blocchiamo il salvataggio se il broker non è raggiungibile
                \Log::warning('DispositivoController: disattiva_allarme fallito durante reset', [
                    'dispositivo' => $dispositivo->codice_seriale,
                    'errore'      => $e->getMessage(),
                ]);
            }

            // 2. Segna le assunzioni allarme_attivo come saltate nel DB
            // così lo scheduler non le trova più e non invia reminder
            Assunzione::where('id_dispositivo', $dispositivo->id)
                ->where('stato', 'allarme_attivo')
                ->update([
                    'stato'       => 'saltata',
                    'note_evento' => 'Allarme annullato — scomparti riconfigurati dal medico',
                ]);
        }
    }

    /**
     * Aggiorna solo pieno/vuoto di un singolo scomparto (AJAX).
     */
    public function aggiornaStato(Request $request, int $idDispositivo, int $numeroScomparto)
    {
        $request->validate(['quantita' => 'required|integer|min:0']);

        $scomparto = ScompartoDispositivo::where('id_dispositivo', $idDispositivo)
            ->where('numero_scomparto', $numeroScomparto)
            ->firstOrFail();

        $quantita = max(0, (int) $request->integer('quantita'));
        $scomparto->update(['quantita' => $quantita, 'pieno' => $quantita > 0]);

        app(MqttController::class)->configuraScomparti(new Request(), $idDispositivo);

        return response()->json(['ok' => true, 'pieno' => $scomparto->pieno, 'quantita' => $scomparto->quantita]);
    }
}
