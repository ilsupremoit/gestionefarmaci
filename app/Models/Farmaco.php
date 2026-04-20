<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmaco extends Model
{
    protected $table   = 'farmaci';
    public $timestamps = false;

    protected $fillable = ['nome', 'descrizione', 'dose', 'note'];

    public function terapie(): HasMany
    {
        return $this->hasMany(Terapia::class, 'id_farmaco');
    }

    public function scomparti(): HasMany
    {
        return $this->hasMany(ScompartoDispositivo::class, 'id_farmaco');
    }
}
