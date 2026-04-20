<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmaco extends Model
{
    protected $table = 'farmaci';
    public $timestamps = false;

    protected $fillable = ['nome', 'descrizione', 'dose', 'note'];

    public function terapie() { return $this->hasMany(Terapia::class, 'id_farmaco'); }
}
