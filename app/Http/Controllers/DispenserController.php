<?php

namespace App\Http\Controllers;

use PhpMqtt\Client\Facades\MQTT;

class DispenserController extends Controller
{
    public function inviaComando()
    {
        // Nota: il firmware legge "comando" non "azione"
        MQTT::publish('pillmate/disp_01/comandi', json_encode([
            'comando'   => 'eroga_farmaco',
            'timestamp' => now()->toDateTimeString(),
        ]));

        return 'Comando inviato al dispenser.';
    }
}
