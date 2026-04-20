<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paziente extends Model
{
    protected $casts = [
        'data_nascita' => 'date', // <-- AGGIUNGI QUESTO!
        // eventuali altre date
    ];
    protected $table   = 'pazienti';
    public $timestamps = false;

    protected $fillable = ['id_utente', 'data_nascita', 'indirizzo', 'note_mediche'];

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
}
