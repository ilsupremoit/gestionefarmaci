<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\ScompartoDispositivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Ascolta i topic MQTT dei dispositivi PillMate e aggiorna il DB';

    public function handle(): void
    {
        $this->info('[MQTT] Listener avviato. In ascolto su pillmate/+/...');

        $mqtt = MQTT::connection();

        $mqtt->subscribe('pillmate/+/eventi', function (string $topic, string $raw) {
            $serial = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (!is_array($payload) || !isset($payload['azione'])) {
                return;
            }

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (!$dispositivo) {
                $this->warn("[MQTT] Dispositivo sconosciuto: {$serial}");
                return;
            }

            $this->salvaEventoRaw($dispositivo, $payload, $topic);

            match ($payload['azione']) {
                'pillola_erogata' => $this->gestisciErogazione($dispositivo, $payload),
                'mappa_scomparti' => $this->sincronizzaMappa($dispositivo, $payload),
                'richiesta_ricarica' => $this->gestisciRichiestaRicarica($dispositivo, $payload),
                'errore_erogazione' => $this->gestisciErroreFarmaco($dispositivo, $payload),
                default => $this->line("[MQTT] Azione non gestita: {$payload['azione']}"),
            };
        }, 1);

        $mqtt->subscribe('pillmate/+/telemetria', function (string $topic, string $raw) {
            $serial = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (!is_array($payload)) {
                return;
            }

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (!$dispositivo) {
                return;
            }

            $update = [
                'ultima_connessione' => now(),
                'ultimo_payload_at' => now(),
            ];

            foreach (['temperatura', 'umidita', 'wifi_rssi'] as $k) {
                if (array_key_exists($k, $payload)) {
                    $update[$k] = $payload[$k];
                }
            }
            if (isset($payload['scomparto_corrente'])) {
                $update['scomparto_attuale'] = (int) $payload['scomparto_corrente'];
            }
            if (isset($payload['allarme_attivo'])) {
                $update['allarme_attivo'] = (bool) $payload['allarme_attivo'];
            }

            $dispositivo->update($update);

            $this->line("[TELEM] {$serial} | T:" . ($payload['temperatura'] ?? '-') . ' H:' . ($payload['umidita'] ?? '-'));
        }, 0);

        $mqtt->subscribe('pillmate/+/stato', function (string $topic, string $raw) {
            $serial = $this->estraiSeriale($topic);
            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (!$dispositivo) {
                return;
            }

            $status = trim($raw, "\" \t\n\r");
            $nuovoStato = match ($status) {
                'online' => 'attivo',
                'offline' => 'offline',
                default => null,
            };

            if ($nuovoStato) {
                $dispositivo->update([
                    'stato' => $nuovoStato,
                    'ultima_connessione' => now(),
                    'ultimo_payload_at' => now(),
                ]);
                $this->info("[STATO] {$serial} -> {$nuovoStato}");
            }

            if ($nuovoStato === 'attivo') {
                $this->inviaConfigurazione($dispositivo);
            }
        }, 1);

        $mqtt->loop(true, true);
    }

    private function gestisciErogazione(Dispositivo $dispositivo, array $payload): void
    {
        $idFarmaco = (int) ($payload['id_farmaco'] ?? 0);
        $numScomparto = (int) ($payload['scomparto_usato'] ?? 0);
        $metodo = (string) ($payload['metodo_attivazione'] ?? 'sconosciuto');
        $quantitaRimanente = max(0, (int) ($payload['quantita_rimanente'] ?? 0));
        $timestamp = now();

        if ($numScomparto > 0) {
            ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                ->where('numero_scomparto', $numScomparto)
                ->update([
                    'quantita' => $quantitaRimanente,
                    'pieno' => $quantitaRimanente > 0,
                ]);
        }

        if ($idFarmaco > 0 && $dispositivo->id_paziente) {
            $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($dispositivo, $idFarmaco) {
                $q->where('id_paziente', $dispositivo->id_paziente)
                    ->where('id_farmaco', $idFarmaco)
                    ->where('attiva', true);
            })
                ->whereIn('stato', ['in_attesa', 'allarme_attivo'])
                ->orderBy('data_prevista')
                ->first();

            if ($assunzione) {
                $assunzione->update([
                    'stato' => 'assunta',
                    'data_erogazione' => $timestamp,
                    'data_conferma' => $timestamp,
                    'confermata_da' => $this->mappaMetodo($metodo),
                    'id_dispositivo' => $dispositivo->id,
                    'scomparto_numero' => $numScomparto ?: null,
                    'allarme_inviato' => true,
                    'data_allarme' => $assunzione->data_allarme ?? $timestamp,
                    'apertura_forzata' => strtoupper($metodo) === 'MQTT_DIRETTO',
                    'data_apertura_forzata' => strtoupper($metodo) === 'MQTT_DIRETTO' ? $timestamp : null,
                ]);
            }
        }

        $nome = $payload['nome_farmaco'] ?? "Farmaco #{$idFarmaco}";
        $this->creaNotifica((int) $dispositivo->id_paziente, 'Pillola erogata', "La pillola \"{$nome}\" è stata erogata (metodo: {$metodo}).", 'info');
    }

    private function sincronizzaMappa(Dispositivo $dispositivo, array $payload): void
    {
        foreach (($payload['scomparti'] ?? []) as $s) {
            if (!isset($s['numero'])) {
                continue;
            }

            $numero = (int) $s['numero'];
            $idFarmaco = isset($s['id_farmaco']) && (int)$s['id_farmaco'] > 0 ? (int)$s['id_farmaco'] : null;
            $quantita = max(0, (int) ($s['quantita'] ?? 0));

            ScompartoDispositivo::updateOrCreate(
                ['id_dispositivo' => $dispositivo->id, 'numero_scomparto' => $numero],
                [
                    'angolo' => $s['angolo'] ?? ScompartoDispositivo::calcolaAngolo($numero),
                    'id_farmaco' => $idFarmaco,
                    'quantita' => $quantita,
                    'pieno' => $quantita > 0,
                ]
            );
        }

        $this->line("[MAPPA] Sincronizzata per {$dispositivo->codice_seriale}.");
    }

    private function gestisciRichiestaRicarica(Dispositivo $dispositivo, array $payload): void
    {
        $num = (int) ($payload['scomparto'] ?? 0);
        $nome = $payload['nome_farmaco'] ?? 'Farmaco';

        $this->creaNotifica((int) $dispositivo->id_paziente, 'Ricarica scomparto', "Scomparto {$num} ({$nome}) da ricaricare.", 'allarme');
    }

    private function gestisciErroreFarmaco(Dispositivo $dispositivo, array $payload): void
    {
        $nome = $payload['nome_farmaco'] ?? ('Farmaco ID ' . ($payload['id_farmaco'] ?? '?'));
        $this->creaNotifica((int) $dispositivo->id_paziente, 'Farmaco non disponibile', "Il farmaco \"{$nome}\" non è disponibile nello scomparto configurato.", 'allarme');
    }

    private function inviaConfigurazione(Dispositivo $dispositivo): void
    {
        $payload = json_encode([
            'comando' => 'configura_scomparti',
            'scomparti' => ScompartoDispositivo::buildPayloadPerDispositivo($dispositivo->id),
        ]);

        MQTT::publish($dispositivo->topicComandi(), $payload);
        $this->info("[AUTO-CONFIG] Configurazione inviata a {$dispositivo->codice_seriale}.");
    }

    private function salvaEventoRaw(Dispositivo $dispositivo, array $payload, string $topic): void
    {
        DB::table('eventi_dispositivo')->insert([
            'id_dispositivo' => $dispositivo->id,
            'id_paziente' => $dispositivo->id_paziente,
            'topic' => $topic,
            'azione' => $payload['azione'] ?? 'evento',
            'metodo_attivazione' => $payload['metodo_attivazione'] ?? null,
            'severita' => 'info',
            'messaggio' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }

    private function estraiSeriale(string $topic): string
    {
        return explode('/', $topic)[1] ?? '';
    }

    private function mappaMetodo(string $metodo): string
    {
        return match (strtoupper($metodo)) {
            'PIR' => 'sensore',
            'BOTTONE' => 'paziente',
            default => 'sistema',
        };
    }

    private function creaNotifica(int $idPaziente, string $titolo, string $messaggio, string $tipo): void
    {
        if ($idPaziente <= 0) {
            return;
        }

        $idUtente = DB::table('pazienti')->where('id', $idPaziente)->value('id_utente');
        if (!$idUtente) {
            return;
        }

        Notifica::create([
            'id_utente' => $idUtente,
            'id_paziente' => $idPaziente,
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'tipo' => $tipo,
        ]);
    }
}
