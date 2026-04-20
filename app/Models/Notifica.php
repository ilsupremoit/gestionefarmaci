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
        'titolo',
        'messaggio',
        'tipo',
        'letta',
        'data_invio',
    ];

    protected $casts = ['letta' => 'boolean'];

    public function utente(): BelongsTo
    {
        return $this->belongsTo(Utente::class, 'id_utente');
    }
}
