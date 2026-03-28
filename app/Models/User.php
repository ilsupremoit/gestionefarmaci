<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * La tabella del database è `utenti`, non `users` (default Laravel).
     */
    protected $table = 'utenti';

    /**
     * Campi assegnabili in massa.
     */
    protected $fillable = [
        'nome',
        'cognome',
        'email',
        'password',
        'ruolo',
        'telefono',
    ];

    /**
     * Campi nascosti nella serializzazione.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast dei tipi.
     */
    protected function casts(): array
    {
        return [
            'password'   => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    // ── Relazioni ─────────────────────────────────────

    /** Profilo paziente (se ruolo = 'paziente') */
    public function paziente()
    {
        return $this->hasOne(Paziente::class, 'id_utente');
    }

    /** Notifiche ricevute */
    public function notifiche()
    {
        return $this->hasMany(Notifica::class, 'id_utente');
    }

    /** Pazienti seguiti (se ruolo = 'medico') */
    public function pazientiSeguiti()
    {
        return $this->belongsToMany(Paziente::class, 'medici_pazienti', 'id_medico', 'id_paziente');
    }

    /** Pazienti monitorati (se ruolo = 'familiare') */
    public function pazientiMonitorati()
    {
        return $this->belongsToMany(Paziente::class, 'familiari_pazienti', 'id_familiare', 'id_paziente');
    }
}
