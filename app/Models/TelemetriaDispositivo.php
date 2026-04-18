<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetriaDispositivo extends Model
{
    protected $table = 'telemetrie_dispositivo';

    public $timestamps = false;

    protected $fillable = [
        'id_dispositivo',
        'temperatura',
        'umidita',
        'allarme_attivo',
        'wifi_rssi',
        'scomparto_attuale',
        'sveglia_impostata',
        'timestamp_dispositivo',
        'payload_json',
        'created_at',
    ];
}
