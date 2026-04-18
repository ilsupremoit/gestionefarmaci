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

        $this->info('Connesso. In ascolto su pillmate/+/eventi e pillmate/+/comandi e pillmate/disp_01/stato');

        $mqtt->subscribe('pillmate/disp_01/telemetria', function (string $topic, string $message) {
            $this->line('----------------------------');
            $this->info("EVENTO | Topic: {$topic}");
            $this->line("Messaggio grezzo: {$message}");

            $payload = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->info('JSON decodificato correttamente:');
                print_r($payload);
            } else {
                $this->error('Il messaggio non è un JSON valido.');
            }
        }, 1);

        $mqtt->subscribe('pillmate/disp_01/stato', function (string $topic, string $message) {
            $this->line('----------------------------');
            $this->info("EVENTO | Topic: {$topic}");
            $this->line("Messaggio grezzo: {$message}");

            $payload = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->info('JSON decodificato correttamente:');
                print_r($payload);
            } else {
                $this->error('Il messaggio non è un JSON valido.');
            }
        }, 1);



        $mqtt->subscribe('pillmate/disp_01/eventi', function (string $topic, string $message) {
            $this->line('----------------------------');
            $this->info("COMANDO | Topic: {$topic}");
            $this->line("Messaggio grezzo: {$message}");

            $payload = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->info('JSON decodificato correttamente:');
                print_r($payload);
            } else {
                $this->error('Il messaggio non è un JSON valido.');
            }
        }, 1);

        $mqtt->loop(true);

        return Command::SUCCESS;
    }


}
