<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assunzione extends Model
{
    protected $table = 'assunzioni';
    public $timestamps = false;

    protected $fillable = [
        'id_somministrazione', 'id_dispositivo',
        'data_prevista', 'data_erogazione', 'data_conferma',
        'stato', 'confermata_da',
        'allarme_inviato', 'data_allarme',
        'apertura_forzata', 'data_apertura_forzata',
        'note_evento', 'scomparto_numero',
    ];

    protected $casts = [
        'data_prevista'        => 'datetime',
        'data_erogazione'      => 'datetime',
        'data_conferma'        => 'datetime',
        'data_allarme'         => 'datetime',
        'data_apertura_forzata'=> 'datetime',
        'allarme_inviato'      => 'boolean',
        'apertura_forzata'     => 'boolean',
    ];

    public function somministrazione() { return $this->belongsTo(Somministrazione::class, 'id_somministrazione'); }
    public function dispositivo()      { return $this->belongsTo(Dispositivo::class, 'id_dispositivo'); }
}
