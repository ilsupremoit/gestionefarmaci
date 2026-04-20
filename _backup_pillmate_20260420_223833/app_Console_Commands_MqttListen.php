<?php

namespace App\Console\Commands;

use App\Models\Dispositivo;
use App\Models\EventoDispositivo;
use App\Models\TelemetriaDispositivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;
use PhpMqtt\Client\Facades\MQTT;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Ascolta e salva i messaggi MQTT del dispenser';

    public function handle()
    {
        $this->info('Connessione al broker MQTT...');

        $mqtt = MQTT::connection();

        $this->info('Connesso. In ascolto su pillmate/+/+');

        $mqtt->subscribe('pillmate/+/+', function (string $topic, string $message) {
            try {
                $this->line('----------------------------');
                $this->info("Topic: {$topic}");
                $this->line("Messaggio grezzo: {$message}");

                $payload = json_decode($message, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Messaggio non JSON valido');
                    return;
                }

                $parti = explode('/', $topic);
                $deviceCode = $parti[1] ?? null;
                $tipoTopic  = $parti[2] ?? null;

                if (!$deviceCode || !$tipoTopic) {
                    $this->error('Topic non valido');
                    return;
                }

                $dispositivo = Dispositivo::where('codice_seriale', $deviceCode)->first();

                if (!$dispositivo) {
                    $this->error("Dispositivo {$deviceCode} non trovato nel database");
                    return;
                }

                $this->info("Dispositivo trovato: ID {$dispositivo->id}");

                switch ($tipoTopic) {
                    case 'telemetria':
                        $this->salvaTelemetria($dispositivo, $payload);
                        break;

                    case 'eventi':
                        $this->salvaEvento($dispositivo, $topic, $payload);
                        break;

                    case 'stato':
                        $this->salvaStato($dispositivo, $payload);
                        break;

                    default:
                        $this->warn("Tipo topic non gestito: {$tipoTopic}");
                        break;
                }
            } catch (Throwable $e) {
                $this->error('ERRORE CALLBACK MQTT: ' . $e->getMessage());
                $this->line($e->getTraceAsString());
            }
        }, 1);

        $mqtt->loop(true);

        return Command::SUCCESS;
    }

    protected function salvaTelemetria(Dispositivo $dispositivo, array $payload): void
    {
        TelemetriaDispositivo::create([
            'id_dispositivo' => $dispositivo->id,
            'temperatura' => $payload['temperatura'] ?? null,
            'umidita' => $payload['umidita'] ?? null,
            'allarme_attivo' => !empty($payload['allarme_attivo']) ? 1 : 0,
            'wifi_rssi' => $payload['wifi_rssi'] ?? null,
            'scomparto_attuale' => $payload['scomparto_attuale'] ?? null,
            'sveglia_impostata' => $payload['sveglia_impostata'] ?? null,
            'timestamp_dispositivo' => $payload['timestamp'] ?? now()->format('Y-m-d H:i:s'),
            'payload_json' => json_encode($payload),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $dispositivo->update([
            'temperatura' => $payload['temperatura'] ?? $dispositivo->temperatura,
            'umidita' => $payload['umidita'] ?? $dispositivo->umidita,
            'wifi_rssi' => $payload['wifi_rssi'] ?? $dispositivo->wifi_rssi,
            'allarme_attivo' => !empty($payload['allarme_attivo']) ? 1 : 0,
            'scomparto_attuale' => $payload['scomparto_attuale'] ?? $dispositivo->scomparto_attuale,
            'sveglia_impostata' => $payload['sveglia_impostata'] ?? $dispositivo->sveglia_impostata,
            'ultima_connessione' => $payload['timestamp'] ?? now()->format('Y-m-d H:i:s'),
            'ultimo_payload_at' => $payload['timestamp'] ?? now()->format('Y-m-d H:i:s'),
        ]);

        $this->info('Telemetria salvata correttamente');
    }

    protected function salvaEvento(Dispositivo $dispositivo, string $topic, array $payload): void
    {
        EventoDispositivo::create([
            'id_dispositivo' => $dispositivo->id,
            'id_paziente' => $dispositivo->id_paziente,
            'id_assunzione' => null,
            'topic' => $topic,
            'azione' => $payload['azione'] ?? 'evento_sconosciuto',
            'metodo_attivazione' => $payload['metodo_attivazione'] ?? null,
            'severita' => 'warning',
            'messaggio' => $payload['azione'] ?? null,
            'timestamp_dispositivo' => $payload['timestamp'] ?? now()->format('Y-m-d H:i:s'),
            'payload_json' => json_encode($payload),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->info('Evento salvato correttamente');
    }

    protected function salvaStato(Dispositivo $dispositivo, array $payload): void
    {
        $stato = $payload['status'] ?? null;

        if ($stato === 'online') {
            $stato = 'attivo';
        } elseif ($stato === 'offline') {
            $stato = 'offline';
        }

        $dispositivo->update([
            'stato' => $stato ?? $dispositivo->stato,
            'scomparto_attuale' => $payload['scomparto_iniziale'] ?? $dispositivo->scomparto_attuale,
            'ultima_connessione' => now()->format('Y-m-d H:i:s'),
            'ultimo_payload_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->info('Stato dispositivo aggiornato correttamente');
    }
}
