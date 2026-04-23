<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScompartoDispositivo extends Model
{
    protected $table = 'scomparti_dispositivo';

    protected $fillable = [
        'id_dispositivo',
        'numero_scomparto',
        'angolo',
        'id_farmaco',
        'id_terapia',
        'pieno',
    ];

    protected $casts = [
        'pieno' => 'boolean',
    ];

    // Angoli precalcolati per i 8 scomparti (stesso valore del firmware C++)
    const ANGOLI = [0 => 26, 1 => 46, 2 => 67, 3 => 93, 4 => 113, 5 => 137, 6 => 160, 7 => 180];

    public function dispositivo()
    {
        return $this->belongsTo(Dispositivo::class, 'id_dispositivo');
    }

    public function farmaco()
    {
        return $this->belongsTo(Farmaco::class, 'id_farmaco');
    }

    public function terapia()
    {
        return $this->belongsTo(Terapia::class, 'id_terapia');
    }
}
