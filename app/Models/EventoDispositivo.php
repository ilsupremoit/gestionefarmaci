<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoDispositivo extends Model
{
    protected $table = 'eventi_dispositivo';

    public $timestamps = false;

    protected $fillable = [
        'id_dispositivo',
        'id_paziente',
        'id_assunzione',
        'topic',
        'azione',
        'metodo_attivazione',
        'severita',
        'messaggio',
        'timestamp_dispositivo',
        'payload_json',
        'created_at',
    ];
}
