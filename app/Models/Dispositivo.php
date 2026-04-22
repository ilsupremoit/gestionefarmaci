<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispositivo extends Model
{
    protected $table   = 'dispositivi';
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

    protected $casts = [
        'ultima_connessione' => 'datetime',
        'ultimo_payload_at'  => 'datetime',
        'allarme_attivo'     => 'boolean',
        'temperatura'        => 'float',
        'umidita'            => 'float',
    ];

    // ── Relazioni ─────────────────────────────────────────────────

    public function paziente(): BelongsTo
    {
        return $this->belongsTo(Paziente::class, 'id_paziente');
    }

    public function scomparti(): HasMany
    {
        return $this->hasMany(ScompartoDispositivo::class, 'id_dispositivo')
                    ->orderBy('numero_scomparto');
    }

    // ── Topic MQTT ────────────────────────────────────────────────

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
