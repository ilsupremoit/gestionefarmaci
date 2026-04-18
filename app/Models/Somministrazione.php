<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Somministrazione extends Model
{
    protected $table = 'somministrazioni';
    public $timestamps = false;

    protected $fillable = ['id_terapia', 'ora', 'giorno_settimana'];

    public function terapia()   { return $this->belongsTo(Terapia::class, 'id_terapia'); }
    public function assunzioni(){ return $this->hasMany(Assunzione::class, 'id_somministrazione'); }
}
