<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nome',
        'cognome',
        'username',
        'email',
        'password',
        'must_change_password',
        'ruolo',
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'must_change_password' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function paziente()
    {
        return $this->hasOne(Paziente::class, 'id_utente');
    }

    public function notifiche()
    {
        return $this->hasMany(Notifica::class, 'id_utente');
    }

    public function pazientiSeguiti()
    {
        return $this->belongsToMany(Paziente::class, 'medici_pazienti', 'id_medico', 'id_paziente');
    }

    public function pazientiMonitorati()
    {
        return $this->belongsToMany(Paziente::class, 'familiari_pazienti', 'id_familiare', 'id_paziente');
    }
}
