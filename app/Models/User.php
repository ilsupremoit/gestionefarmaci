<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'nome',
        'cognome',
        'username',
        'email',
        'password',
        'ruolo',
        'telefono',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'email_verified_at'    => 'datetime',
    ];

    // ── Relazioni ─────────────────────────────────────

    /** Pazienti seguiti dal medico (tramite medici_pazienti) */
    public function pazientiSeguiti()
    {
        return $this->belongsToMany(
            Paziente::class,
            'medici_pazienti',
            'id_medico',
            'id_paziente'
        );
    }

    /** Profilo paziente (se ruolo = paziente) */
    public function paziente()
    {
        return $this->hasOne(Paziente::class, 'id_utente');
    }
}
