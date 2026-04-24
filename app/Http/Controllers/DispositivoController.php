<?php

namespace App\Http\Controllers;

use App\Models\Dispositivo;
use App\Models\Farmaco;
use App\Models\ScompartoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

        app(MqttController::class)->configuraScomparti(new Request(), $idDispositivo);

        return redirect()
            ->route('dispositivi.scomparti', $idDispositivo)
            ->with('success', 'Configurazione salvata e inviata al dispositivo.');
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
