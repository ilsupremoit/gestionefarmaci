<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\ScompartoDispositivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;
use Throwable;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Ascolta i topic MQTT dei dispositivi PillMate e aggiorna il DB';

    public function handle(): void
    {
        while (true) {
            try {
                $this->info('[MQTT] Listener avviato. In ascolto su pillmate/+/...');

                // Usa client_id dedicato al listener (diverso dal publisher)
                // e aumenta i timeout per non perdere la connessione tra telemetrie
                config([
                    'mqtt-client.connections.default.client_id'                                        => env('MQTT_LISTENER_CLIENT_ID', 'laravel-pillmate-listener-' . getmypid()),
                    'mqtt-client.connections.default.connection_settings.connect_timeout'              => 30,
                    'mqtt-client.connections.default.connection_settings.socket_timeout'               => 120,
                    'mqtt-client.connections.default.connection_settings.keep_alive_interval'          => 30,
                    'mqtt-client.connections.default.connection_settings.resend_timeout'               => 15,
                ]);

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
                        'pillola_erogata'   => $this->gestisciErogazione($dispositivo, $payload),
                        'mappa_scomparti'   => $this->sincronizzaMappa($dispositivo, $payload),
                        'richiesta_ricarica'=> $this->gestisciRichiestaRicarica($dispositivo, $payload),
                        'errore_erogazione' => $this->gestisciErroreFarmaco($dispositivo, $payload),
                        'pillola_mancata'   => $this->gestisciPillolaMancata($dispositivo, $payload),
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

                $mqtt->subscribe('pillmate/+/stato', function (string $topic, string $raw) use ($mqtt) {
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
                        $this->inviaConfigurazione($mqtt, $dispositivo);
                    }
                }, 1);

                $mqtt->loop(true, true);
            } catch (Throwable $e) {
                $this->error('[MQTT] Listener disconnesso: ' . $e->getMessage());
                $this->warn('[MQTT] Riprovo la connessione tra 3 secondi...');

                try {
                    MQTT::disconnect();
                } catch (Throwable $ignored) {
                    //
                }

                sleep(3);
            }
        }
    }

    private function gestisciErogazione(Dispositivo $dispositivo, array $payload): void
    {
        Log::debug('gestisciErogazione: payload ricevuto', $payload);

        $idFarmaco    = (int) ($payload['id_farmaco'] ?? 0);
        // FIX: supporta sia "scomparto_usato" che "scomparto_numero" (entrambi 1-based)
        $numScomparto = (int) ($payload['scomparto_usato'] ?? $payload['scomparto_numero'] ?? 0);
        $metodo       = (string) ($payload['metodo_attivazione'] ?? 'sconosciuto');
        // FIX: supporta sia "quantita" (nuovo standard) che "quantita_rimanente" (legacy)
        $quantitaRimanente = max(0, (int) ($payload['quantita'] ?? $payload['quantita_rimanente'] ?? 0));
        $timestamp         = now();

        Log::debug('gestisciErogazione: dati estratti', [
            'id_farmaco' => $idFarmaco,
            'num_scomparto' => $numScomparto,
            'metodo' => $metodo,
            'quantita_rimanente' => $quantitaRimanente,
        ]);

        // ── 1. Aggiorna quantità scomparto ───────────────────────────────
        if ($numScomparto > 0) {
            ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                ->where('numero_scomparto', $numScomparto)
                ->update([
                    'quantita' => $quantitaRimanente,
                    'pieno'    => $quantitaRimanente > 0,
                ]);
        }

        // ── 2. Ricava id_farmaco dal DB se mancante nel payload ──────────
        // Questo avviene quando il PIR o il BOTTONE scattano senza che l'ESP32
        // includa l'id_farmaco (non dovrebbe più succedere dopo il fix C++,
        // ma lo teniamo come safety net).
        if ($idFarmaco <= 0 && $numScomparto > 0) {
            $idFarmaco = (int) ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                ->where('numero_scomparto', $numScomparto)
                ->value('id_farmaco');

            if ($idFarmaco > 0) {
                $this->line("[MQTT] id_farmaco ricavato dallo scomparto {$numScomparto}: #{$idFarmaco}");
                Log::debug('gestisciErogazione: id_farmaco ricavato dal DB', ['id_farmaco' => $idFarmaco]);
            }
        }

        // ── 3. Trova l'assunzione con 3 livelli di fallback ──────────────
        // Livello A → match per scomparto_numero (salvato dallo scheduler)
        // Livello B → match per id_farmaco nella terapia
        // Livello C → qualsiasi assunzione attiva del paziente (più imminente)
        $assunzione = null;

        if ($dispositivo->id_paziente) {

            // Finestra temporale: entro 60 min dall'allarme fisico
            $statiValidi  = ['in_attesa', 'allarme_attivo'];
            $finestraRace = now()->subMinutes(60);

            // Livello A: scomparto_numero
            // FIX: usa '>' per priorità allarme_attivo, poi in_attesa, poi saltata
            if ($numScomparto > 0) {
                $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($dispositivo) {
                    $q->where('id_paziente', $dispositivo->id_paziente);
                })
                ->where(function ($q) use ($statiValidi, $finestraRace) {
                    $q->whereIn('stato', $statiValidi)
                      ->orWhere(function ($q2) use ($finestraRace) {
                          // Saltata dal marcaSaltateScadute ma allarme era stato inviato
                          // (race condition: ESP32 ha erogato prima del timeout Laravel)
                          $q2->where('stato', 'saltata')
                             ->where('allarme_inviato', true)
                             ->where('data_allarme', '>=', $finestraRace);
                      });
                })
                ->where('scomparto_numero', $numScomparto)
                ->orderByRaw("FIELD(stato,'allarme_attivo','in_attesa','saltata')")
                ->orderBy('data_prevista')
                ->first();

                if ($assunzione) {
                    $this->line("[MQTT] Livello A: trovata assunzione ID {$assunzione->id} per scomparto {$numScomparto}");
                    Log::debug('gestisciErogazione: Trovata assunzione (Livello A)', ['assunzione_id' => $assunzione->id]);
                }
            }

            // Livello B: id_farmaco nella terapia
            if (!$assunzione && $idFarmaco > 0) {
                $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($dispositivo, $idFarmaco) {
                    $q->where('id_paziente', $dispositivo->id_paziente)
                      ->where('id_farmaco', $idFarmaco);
                })
                ->where(function ($q) use ($statiValidi, $finestraRace) {
                    $q->whereIn('stato', $statiValidi)
                      ->orWhere(function ($q2) use ($finestraRace) {
                          $q2->where('stato', 'saltata')
                             ->where('allarme_inviato', true)
                             ->where('data_allarme', '>=', $finestraRace);
                      });
                })
                ->orderByRaw("FIELD(stato,'allarme_attivo','in_attesa','saltata')")
                ->orderBy('data_prevista')
                ->first();

                if ($assunzione) {
                    $this->line("[MQTT] Livello B: trovata assunzione ID {$assunzione->id} per farmaco #{$idFarmaco}");
                    Log::debug('gestisciErogazione: Trovata assunzione (Livello B)', ['assunzione_id' => $assunzione->id]);
                }
            }

            // Livello C: qualsiasi assunzione attiva del paziente
            if (!$assunzione) {
                $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($dispositivo) {
                    $q->where('id_paziente', $dispositivo->id_paziente);
                })
                ->where(function ($q) use ($statiValidi, $finestraRace) {
                    $q->whereIn('stato', $statiValidi)
                      ->orWhere(function ($q2) use ($finestraRace) {
                          $q2->where('stato', 'saltata')
                             ->where('allarme_inviato', true)
                             ->where('data_allarme', '>=', $finestraRace);
                      });
                })
                ->orderByRaw("FIELD(stato,'allarme_attivo','in_attesa','saltata')")
                ->orderBy('data_prevista')
                ->first();

                if ($assunzione) {
                    $this->warn("[MQTT] Livello C (fallback): assunzione ID {$assunzione->id} (stato: {$assunzione->stato})");
                    Log::debug('gestisciErogazione: Trovata assunzione (Livello C)', ['assunzione_id' => $assunzione->id]);
                }
            }

            if ($assunzione) {
                $assunzione->update([
                    'stato'                 => 'assunta',
                    'data_erogazione'       => $timestamp,
                    'data_conferma'         => $timestamp,
                    'confermata_da'         => $this->mappaMetodo($metodo),
                    'id_dispositivo'        => $dispositivo->id,
                    'scomparto_numero'      => $numScomparto ?: $assunzione->scomparto_numero,
                    'allarme_inviato'       => true,
                    'data_allarme'          => $assunzione->data_allarme ?? $timestamp,
                    'apertura_forzata'      => strtoupper($metodo) === 'MQTT_DIRETTO',
                    'data_apertura_forzata' => strtoupper($metodo) === 'MQTT_DIRETTO' ? $timestamp : null,
                    'note_evento'           => "Erogata via {$metodo}.",
                ]);

                $this->info("[EROGAZIONE] Assunzione ID {$assunzione->id} → assunta (metodo: {$metodo})");
                Log::debug('gestisciErogazione: Assunzione aggiornata', ['assunzione_id' => $assunzione->id]);
            } else {
                $this->warn("[MQTT] Nessuna assunzione in_attesa/allarme_attivo trovata per paziente {$dispositivo->id_paziente}");
                Log::warning('gestisciErogazione: Nessuna assunzione trovata da aggiornare', ['dispositivo_id' => $dispositivo->id]);
            }
        }

        $nome = $payload['nome_farmaco'] ?? ($idFarmaco > 0 ? "Farmaco #{$idFarmaco}" : 'Farmaco');

        $this->creaNotifica(
            (int) $dispositivo->id_paziente,
            'Pillola erogata',
            "La pillola \"{$nome}\" è stata erogata (metodo: {$metodo}).",
            'info'
        );
    }

    private function sincronizzaMappa(Dispositivo $dispositivo, array $payload): void
    {
        foreach (($payload['scomparti'] ?? []) as $s) {
            if (!isset($s['numero'])) {
                continue;
            }

            $numero = (int) $s['numero'];
            $idFarmaco = isset($s['id_farmaco']) && (int) $s['id_farmaco'] > 0 ? (int) $s['id_farmaco'] : null;
            $quantita = max(0, (int) ($s['quantita'] ?? 0));

            ScompartoDispositivo::updateOrCreate(
                [
                    'id_dispositivo' => $dispositivo->id,
                    'numero_scomparto' => $numero,
                ],
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

    private function gestisciPillolaMancata(Dispositivo $dispositivo, array $payload): void
    {
        $idFarmaco    = (int) ($payload['id_farmaco'] ?? 0);
        $numScomparto = (int) ($payload['scomparto'] ?? $payload['scomparto_usato'] ?? 0);
        $nomeFarmaco  = $payload['nome_farmaco'] ?? "Farmaco #{$idFarmaco}";

        // Cerca l'assunzione con allarme_attivo o in_attesa corrispondente
        $assunzione = null;

        if ($dispositivo->id_paziente) {
            // Prima per scomparto_numero
            if ($numScomparto > 0) {
                $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($dispositivo) {
                    $q->where('id_paziente', $dispositivo->id_paziente);
                })
                ->whereIn('stato', ['in_attesa', 'allarme_attivo'])
                ->where('scomparto_numero', $numScomparto)
                ->orderByRaw("FIELD(stato,'allarme_attivo','in_attesa')")
                ->orderBy('data_prevista')
                ->first();
            }

            // Poi per id_farmaco nella terapia
            if (!$assunzione && $idFarmaco > 0) {
                $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($dispositivo, $idFarmaco) {
                    $q->where('id_paziente', $dispositivo->id_paziente)
                      ->where('id_farmaco', $idFarmaco);
                })
                ->whereIn('stato', ['in_attesa', 'allarme_attivo'])
                ->orderByRaw("FIELD(stato,'allarme_attivo','in_attesa')")
                ->orderBy('data_prevista')
                ->first();
            }
        }

        if ($assunzione) {
            $assunzione->update([
                'stato'       => 'saltata',
                'note_evento' => "Timeout segnalato dall'ESP32: pillola non presa",
            ]);
            $this->warn("[PILLOLA_MANCATA] Assunzione ID {$assunzione->id} → saltata (segnalato da ESP32)");
        } else {
            $this->warn("[PILLOLA_MANCATA] Nessuna assunzione attiva trovata per {$nomeFarmaco}");
        }

        $this->creaNotifica(
            (int) $dispositivo->id_paziente,
            'Pillola non presa',
            "La pillola \"${nomeFarmaco}\" non è stata presa entro il tempo previsto.",
            'allarme'
        );
    }

    private function gestisciRichiestaRicarica(Dispositivo $dispositivo, array $payload): void
    {
        $num = (int) ($payload['scomparto'] ?? 0);
        $nome = $payload['nome_farmaco'] ?? 'Farmaco';

        $this->creaNotifica(
            (int) $dispositivo->id_paziente,
            'Ricarica scomparto',
            "Scomparto {$num} ({$nome}) da ricaricare.",
            'allarme'
        );
    }

    private function gestisciErroreFarmaco(Dispositivo $dispositivo, array $payload): void
    {
        $nome = $payload['nome_farmaco'] ?? ('Farmaco ID ' . ($payload['id_farmaco'] ?? '?'));

        $this->creaNotifica(
            (int) $dispositivo->id_paziente,
            'Farmaco non disponibile',
            "Il farmaco \"{$nome}\" non è disponibile nello scomparto configurato.",
            'allarme'
        );
    }

    private function inviaConfigurazione($mqtt, Dispositivo $dispositivo): void
    {
        $payload = json_encode([
            'comando' => 'configura_scomparti',
            'scomparti' => ScompartoDispositivo::buildPayloadPerDispositivo($dispositivo->id),
        ]);

        $mqtt->publish($dispositivo->topicComandi(), $payload, 0);

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
