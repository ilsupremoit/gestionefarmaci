<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispositivo extends Model
{
    protected $casts = [
        'ultima_connessione' => 'datetime', //
        'allarme_attivo'     => 'boolean',
        'temperatura'        => 'float',
        'umidita'            => 'float',
    ];
    protected $table  = 'dispositivi';
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
    ];



    // â”€â”€ Relazioni â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function paziente(): BelongsTo
    {
        return $this->belongsTo(Paziente::class, 'id_paziente');
    }

    public function scomparti(): HasMany
    {
        return $this->hasMany(ScompartoDispositivo::class, 'id_dispositivo')
                    ->orderBy('numero_scomparto');
    }

    // â”€â”€ Topic MQTT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function topicComandi(): string
    {
        return "pillmate/{$this->codice_seriale}/comandi";
    }

    public function topicEventi(): string
    {
        return "pillmate/{$this->codice_seriale}/eventi";
    }

    public function topicTelemetria(): string
    {
        return "pillmate/{$this->codice_seriale}/telemetria";
    }

    public function topicStato(): string
    {
        return "pillmate/{$this->codice_seriale}/stato";
    }
}
