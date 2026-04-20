<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assunzione extends Model
{
    protected $table   = 'assunzioni';
    public $timestamps = false;

    protected $fillable = [
        'id_somministrazione',
        'data_prevista',
        'data_erogazione',
        'data_conferma',
        'stato',
        'confermata_da',
    ];

    protected $casts = [
        'data_prevista'  => 'datetime',
        'data_erogazione'=> 'datetime',
        'data_conferma'  => 'datetime',
    ];

    public function somministrazione(): BelongsTo
    {
        return $this->belongsTo(Somministrazione::class, 'id_somministrazione');
    }
}
