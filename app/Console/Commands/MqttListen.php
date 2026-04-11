<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Models\Notifica;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Ascolta i messaggi MQTT e li salva nel database';

    public function handle()
    {
        $mqtt = MQTT::connection();

        $mqtt->subscribe('pillmate/paziente/+/farmaco', function (string $topic, string $message) {
            $this->info("Messaggio ricevuto su {$topic}: {$message}");

            $payload = json_decode($message, true);

            if (!$payload) {
                return;
            }

            Notifica::create([
                'topic' => $topic,
                'messaggio' => $message,
            ]);
        }, 1);

        $mqtt->loop(true);
    }
}
