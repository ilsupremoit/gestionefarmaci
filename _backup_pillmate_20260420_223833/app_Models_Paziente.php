<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paziente extends Model
{
    protected $table = 'pazienti';

    public $timestamps = false;

    protected $fillable = [
        'id_utente',
        'data_nascita',
        'indirizzo',
        'codice_fiscale',
        'note_mediche',
    ];

    protected $casts = [
        'data_nascita' => 'date',
    ];

    // ── Relazioni ─────────────────────────────────────

    /** Utente associato */
    public function utente()
    {
        return $this->belongsTo(User::class, 'id_utente');
    }

    /** Terapie del paziente */
    public function terapie()
    {
        return $this->hasMany(Terapia::class, 'id_paziente');
    }

    /** Assunzioni del paziente (tramite somministrazioni → terapie) */
    public function assunzioni()
    {
        return Assunzione::whereHas('somministrazione.terapia', function ($q) {
            $q->where('id_paziente', $this->id);
        });
    }

    /** Dispositivi IoT del paziente */
    public function dispositivi()
    {
        return $this->hasMany(Dispositivo::class, 'id_paziente');
    }

    /** Medici che seguono il paziente */
    public function medici()
    {
        return $this->belongsToMany(User::class, 'medici_pazienti', 'id_paziente', 'id_medico');
    }

    /** Familiari del paziente */
    public function familiari()
    {
        return $this->belongsToMany(User::class, 'familiari_pazienti', 'id_paziente', 'id_familiare')
                    ->withPivot('grado_parentela');
    }
}
