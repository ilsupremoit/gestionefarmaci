<?php

namespace App\Http\Controllers;

use PhpMqtt\Client\Facades\MQTT;

class DispenserController extends Controller
{
    public function inviaComando()
    {
        MQTT::publish('pillmate/disp_01/comandi', json_encode([
            'azione' => 'eroga_ora',
            'timestamp' => now()->toDateTimeString(),
        ]));

        return 'Comando inviato al dispenser.';
    }
}
