<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Terapia extends Model
{
    protected $table   = 'terapie';
    public $timestamps = false;

    protected $fillable = [
        'id_paziente','id_medico','id_farmaco',
        'data_inizio','data_fine','frequenza',
        'quantita','istruzioni','attiva',
    ];

    protected $casts = [
        'attiva'      => 'boolean',
        'data_inizio' => 'date',
        'data_fine'   => 'date',
    ];

    public function paziente(): BelongsTo
    {
        return $this->belongsTo(Paziente::class, 'id_paziente');
    }

    public function farmaco(): BelongsTo
    {
        return $this->belongsTo(Farmaco::class, 'id_farmaco');
    }

    public function somministrazioni(): HasMany
    {
        return $this->hasMany(Somministrazione::class, 'id_terapia');
    }
}
