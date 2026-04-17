<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Ascolta i messaggi MQTT del dispenser';

    public function handle()
    {
        $this->info('Connessione al broker MQTT...');

        $mqtt = MQTT::connection();

        $this->info('Connesso. In ascolto sui topic pillmate/+/eventi');

        $mqtt->subscribe('pillmate/+/eventi', function (string $topic, string $message) {
            $this->info("Ricevuto su {$topic}: {$message}");

            $payload = json_decode($message, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Payload non valido in formato JSON');
                return;
            }

            // esempio:
            // \Log::info('Messaggio MQTT ricevuto', [
            //     'topic' => $topic,
            //     'payload' => $payload,
            // ]);
        }, 1);

        $mqtt->loop(true);

        return Command::SUCCESS;
    }
}
