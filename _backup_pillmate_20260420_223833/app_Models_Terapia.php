<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Terapia extends Model
{
    protected $table = 'terapie';

    public $timestamps = false;

    protected $fillable = [
        'id_paziente', 'id_medico', 'id_farmaco',
        'data_inizio', 'data_fine', 'frequenza',
        'quantita', 'istruzioni', 'attiva',
    ];

    protected $casts = [
        'data_inizio' => 'date',
        'data_fine'   => 'date',
        'attiva'      => 'boolean',
    ];

    public function paziente()     { return $this->belongsTo(Paziente::class, 'id_paziente'); }
    public function medico()       { return $this->belongsTo(User::class, 'id_medico'); }
    public function farmaco()      { return $this->belongsTo(Farmaco::class, 'id_farmaco'); }
    public function somministrazioni() { return $this->hasMany(Somministrazione::class, 'id_terapia'); }
}
