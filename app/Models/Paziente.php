<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Paziente extends Model
{
    protected $table   = 'pazienti';
    public $timestamps = false;

    protected $fillable = [
        'id_utente',
        'data_nascita',
        'indirizzo',
        'note_mediche',
        'codice_fiscale',
    ];

    protected $casts = [
        'data_nascita' => 'date',
    ];

    // ── Relazioni ─────────────────────────────────────────────────

    public function utente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utente');
    }

    public function dispositivi(): HasMany
    {
        return $this->hasMany(Dispositivo::class, 'id_paziente');
    }

    public function terapie(): HasMany
    {
        return $this->hasMany(Terapia::class, 'id_paziente');
    }

    /**
     * Medici che seguono questo paziente (tramite medici_pazienti).
     */
    public function medici(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'medici_pazienti',
            'id_paziente',
            'id_medico'
        );
    }
}
