<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Somministrazione extends Model
{
    protected $table   = 'somministrazioni';
    public $timestamps = false;

    protected $fillable = ['id_terapia', 'ora', 'giorno_settimana'];

    public function terapia(): BelongsTo
    {
        return $this->belongsTo(Terapia::class, 'id_terapia');
    }

    public function assunzioni(): HasMany
    {
        return $this->hasMany(Assunzione::class, 'id_somministrazione');
    }
}
