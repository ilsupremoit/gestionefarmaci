<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'utenti';

    protected $fillable = [
        'nome',
        'cognome',
        'email',
        'password',
        'ruolo',
        'telefono',
    ];

    protected $hidden = [
        'password',
    ];
}
