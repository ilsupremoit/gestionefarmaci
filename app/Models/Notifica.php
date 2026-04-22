<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifica extends Model
{
    protected $table   = 'notifiche';
    public $timestamps = false;

    protected $fillable = [
        'id_utente',
        'id_mittente',
        'id_paziente',
        'id_dispositivo',
        'id_assunzione',
        'titolo',
        'messaggio',
        'tipo',
        'letta',
        'data_invio',
    ];

    protected $casts = [
        'letta'      => 'boolean',
        'data_invio' => 'datetime',
    ];

    // ── Relazioni ─────────────────────────────────────────────────

    /** Destinatario della notifica */
    public function utente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utente');
    }

    /** Chi ha inviato la notifica (medico o sistema) */
    public function mittente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_mittente');
    }

    /** Paziente collegato (se presente) */
    public function paziente(): BelongsTo
    {
        return $this->belongsTo(Paziente::class, 'id_paziente');
    }
}
