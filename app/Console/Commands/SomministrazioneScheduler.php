<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\ScompartoDispositivo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Controlla ogni minuto le somministrazioni in scadenza
 * e pubblica attiva_allarme + set_sveglia verso l ESP32.
 *
 * Schedulato in Kernel.php con ->everyMinute()
 */
class SomministrazioneScheduler extends Command
{
    protected $signature   = 'pillmate:scheduler';
    protected $description = 'Controlla le somministrazioni previste e attiva gli allarmi';

    public function handle(): void
    {
        $adesso = Carbon::now();

        $assunzioni = Assunzione::with([
                'somministrazione.terapia.farmaco',
                'somministrazione.terapia.paziente',
            ])
            ->where('stato', 'in_attesa')
            ->whereBetween('data_prevista', [
                $adesso->copy()->subSeconds(30),
                $adesso->copy()->addSeconds(30),
            ])
            ->get();

        foreach ($assunzioni as $assunzione) {
            $terapia  = $assunzione->somministrazione->terapia;
            $farmaco  = $terapia->farmaco;
            $paziente = $terapia->paziente;
            if (! $paziente) continue;

            $dispositivo = Dispositivo::where('id_paziente', $paziente->id)
                ->where('stato', 'attivo')
                ->first();

            if (! $dispositivo) {
                $this->warn("[SCHEDULER] Nessun dispositivo attivo per paziente {$paziente->id}");
                continue;
            }

            $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                ->where('id_farmaco', $farmaco->id)
                ->where('pieno', true)
                ->first();

            if (! $scomparto) {
                $this->warn("[SCHEDULER] Scomparto vuoto per {$farmaco->nome}");
                Notifica::create([
                    'id_utente' => $paziente->id_utente,
                    'titolo'    => 'Scomparto vuoto',
                    'messaggio' => "Lo scomparto per \"{$farmaco->nome}\" e vuoto. Ricaricare il dispositivo.",
                    'tipo'      => 'allarme',
                ]);
                continue;
            }

            // Attiva allarme sul dispositivo
            MQTT::publish($dispositivo->topicComandi(), json_encode([
                'comando'    => 'attiva_allarme',
                'id_farmaco' => $farmaco->id,
            ]));

            // Aggiorna anche la sveglia nella flash come backup
            MQTT::publish($dispositivo->topicComandi(), json_encode([
                'comando' => 'set_sveglia',
                'ora'     => (int) $adesso->format('H'),
                'minuto'  => (int) $adesso->format('i'),
            ]));

            $this->info("[SCHEDULER] Allarme -> {$dispositivo->codice_seriale} | {$farmaco->nome}");
        }
    }
}
