<?php
namespace App\Console\Commands;
use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\ScompartoDispositivo;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Processo persistente: ascolta MQTT e aggiorna il DB.
 * Avvio: php artisan mqtt:listen
 * In produzione: tenerlo vivo con Supervisor.
 */
class MqttListen extends Command
{
    protected $signature   = 'mqtt:listen';
    protected $description = 'Ascolta i topic MQTT dei dispositivi PillMate e aggiorna il DB';

    public function handle(): void
    {
        $this->info('[MQTT] Listener avviato. In ascolto su pillmate/+/...');

        $mqtt = MQTT::connection();


        $mqtt->subscribe('pillmate/+/eventi', function (string $topic, string $raw) {
            $serial  = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (! $payload || ! isset($payload['azione'])) return;

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (! $dispositivo) { $this->warn("[MQTT] Dispositivo sconosciuto: $serial"); return; }

            match ($payload['azione']) {
                'pillola_erogata'            => $this->gestisciErogazione($dispositivo, $payload),
                'mappa_scomparti'            => $this->sincronizzaMappa($dispositivo, $payload),
                'errore_farmaco_non_trovato' => $this->gestisciErroreFarmaco($dispositivo, $payload),
                'tentativo_fuori_orario'     => $this->gestisciTentativoFuoriOrario($dispositivo, $payload),
                default                      => $this->line("[MQTT] Azione ignota: {$payload['azione']}"),
            };
        }, 1);


        $mqtt->subscribe('pillmate/+/telemetria', function (string $topic, string $raw) {
            $serial  = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (! $payload) return;

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (! $dispositivo) return;

            $update = ['ultima_connessione' => now()];
            if (isset($payload['temperatura'])) $update['temperatura'] = $payload['temperatura'];
            if (isset($payload['umidita']))      $update['umidita']     = $payload['umidita'];
            $dispositivo->update($update);

            if (isset($payload['scomparti']) && is_array($payload['scomparti'])) {
                foreach ($payload['scomparti'] as $s) {
                    if (! isset($s['numero'])) continue;
                    ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                        ->where('numero_scomparto', $s['numero'])
                        ->update(['pieno' => $s['pieno'] ?? false]);
                }
            }
            $this->line("[TELEM] {$serial} | T:{$payload['temperatura']}C H:{$payload['umidita']}%");
        }, 0);


        $mqtt->subscribe('pillmate/+/stato', function (string $topic, string $raw) {
            $serial  = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (! $payload) return;

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (! $dispositivo) return;

            $nuovoStato = match ($payload['status'] ?? '') {
                'online'  => 'attivo',
                'offline' => 'offline',
                default   => null,
            };

            if ($nuovoStato) {
                $dispositivo->update(['stato' => $nuovoStato, 'ultima_connessione' => now()]);
                $this->info("[STATO] {$serial} -> {$nuovoStato}");
            }

            // Quando torna online, manda subito la configurazione scomparti
            if ($nuovoStato === 'attivo') {
                $this->inviaConfigurazione($dispositivo);
            }
        }, 1);

        $mqtt->loop(true,true);
    }



    private function gestisciErogazione(Dispositivo $d, array $p): void
    {
        $idFarmaco    = $p['id_farmaco']      ?? null;
        $numScomparto = $p['scomparto_usato'] ?? null;
        $metodo       = $p['metodo_attivazione'] ?? 'sconosciuto';
        $timestamp    = $p['timestamp']       ?? now();

        $this->info("[EVENTO] Erogazione | {$d->codice_seriale} | Farmaco:{$idFarmaco} | Scmp:{$numScomparto} | {$metodo}");

        // Segna scomparto vuoto
        if ($numScomparto) {
            ScompartoDispositivo::where('id_dispositivo', $d->id)
                ->where('numero_scomparto', $numScomparto)
                ->update(['pieno' => false]);
        }

        // Aggiorna assunzione in_attesa
        if ($idFarmaco && $d->id_paziente) {
            $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($d, $idFarmaco) {
                $q->where('id_paziente', $d->id_paziente)
                    ->where('id_farmaco', $idFarmaco)
                    ->where('attiva', true);
            })
                ->where('stato', 'in_attesa')
                ->orderBy('data_prevista')
                ->first();

            if ($assunzione) {
                $assunzione->update([
                    'stato'           => 'erogata',
                    'data_erogazione' => $timestamp,
                    'confermata_da'   => $this->mappaMetodo($metodo),
                ]);
            }
        }

        $nome = $p['nome_farmaco'] ?? "Farmaco #{$idFarmaco}";
        $this->creaNotifica($d->id_paziente, 'Pillola erogata',
            "La pillola \"{$nome}\" e stata erogata (metodo: {$metodo}).", 'info');
    }

    private function sincronizzaMappa(Dispositivo $d, array $p): void
    {
        foreach ($p['scomparti'] ?? [] as $s) {
            if (! isset($s['numero'])) continue;
            $idFarmaco = isset($s['id_farmaco']) && $s['id_farmaco'] > 0 ? $s['id_farmaco'] : null;
            ScompartoDispositivo::updateOrCreate(
                ['id_dispositivo' => $d->id, 'numero_scomparto' => $s['numero']],
                [
                    'angolo'     => $s['angolo'] ?? ScompartoDispositivo::calcolaAngolo($s['numero']),
                    'id_farmaco' => $idFarmaco,
                    'pieno'      => $s['pieno'] ?? false,
                ]
            );
        }
        $this->line("[MAPPA] Sincronizzata per {$d->codice_seriale}.");
    }

    private function gestisciErroreFarmaco(Dispositivo $d, array $p): void
    {
        $nome = $p['nome_farmaco'] ?? ("Farmaco ID " . ($p['id_farmaco'] ?? '?'));
        $this->warn("[ERRORE] Farmaco non trovato | {$d->codice_seriale}");
        $this->creaNotifica($d->id_paziente, 'Farmaco non disponibile',
            "Il farmaco \"{$nome}\" non e presente in nessuno scomparto carico.", 'allarme');
    }

    private function gestisciTentativoFuoriOrario(Dispositivo $d, array $p): void
    {
        $metodo = $p['metodo_attivazione'] ?? 'sconosciuto';
        $this->warn("[FUORI ORARIO] {$d->codice_seriale} | {$metodo}");
        $this->creaNotifica($d->id_paziente, 'Tentativo fuori orario',
            "Tentativo di erogazione fuori orario (metodo: {$metodo}).", 'allarme');
    }

    private function inviaConfigurazione(Dispositivo $d): void
    {
        $payload = json_encode([
            'comando'   => 'configura_scomparti',
            'scomparti' => ScompartoDispositivo::buildPayloadPerDispositivo($d->id),
        ]);
        MQTT::publish($d->topicComandi(), $payload);
        $this->info("[AUTO-CONFIG] Configurazione inviata a {$d->codice_seriale}.");
    }



    private function estraiSeriale(string $topic): string
    {
        return explode('/', $topic)[1] ?? '';
    }

    private function mappaMetodo(string $metodo): string
    {
        return match (strtoupper($metodo)) {
            'SENSORE_PIR'    => 'sensore',
            'BOTTONE_FISICO' => 'paziente',
            'COMANDO_REMOTO' => 'sistema',
            default          => 'sistema',
        };
    }

    private function creaNotifica(int $idUtente, string $titolo, string $messaggio, string $tipo): void
    {
        Notifica::create([
            'id_utente' => $idUtente,
            'titolo'    => $titolo,
            'messaggio' => $messaggio,
            'tipo'      => $tipo,
        ]);
    }
}
