<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    protected $table = 'dispositivi';

    public $timestamps = false;

    protected $fillable = [
        'codice_seriale',
        'id_paziente',
        'nome_dispositivo',
        'stato',
        'ultima_connessione',
        'batteria',
        'temperatura',
        'umidita',
        'wifi_rssi',
        'allarme_attivo',
        'scomparto_attuale',
        'sveglia_impostata',
        'ultimo_payload_at',
    ];
}
