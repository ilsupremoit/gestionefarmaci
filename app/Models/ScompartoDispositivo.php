<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScompartoDispositivo extends Model
{
    protected $table = 'scomparti_dispositivo';

    public const NUM_SCOMPARTI = 8;
    // allineato al firmware C++ condiviso dall'utente
    public const ANGOLI = [0, 22, 44, 66, 88, 110, 132, 154];

    protected $fillable = [
        'id_dispositivo',
        'numero_scomparto',
        'angolo',
        'id_farmaco',
        'id_terapia',
        'pieno',
        'quantita',
    ];

    protected $casts = [
        'pieno' => 'boolean',
    ];

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

    public static function calcolaAngolo(int $numeroScomparto): int
    {
        $index = $numeroScomparto - 1;
        return self::ANGOLI[$index] ?? 0;
    }

    public static function inizializzaPerDispositivo(int $idDispositivo): void
    {
        for ($i = 1; $i <= self::NUM_SCOMPARTI; $i++) {
            self::firstOrCreate(
                ['id_dispositivo' => $idDispositivo, 'numero_scomparto' => $i],
                [
                    'angolo' => self::calcolaAngolo($i),
                    'pieno' => false,
                    'quantita' => 0,
                ]
            );
        }
    }

    public static function buildPayloadPerDispositivo(int $idDispositivo): array
    {
        self::inizializzaPerDispositivo($idDispositivo);

        return self::with('farmaco')
            ->where('id_dispositivo', $idDispositivo)
            ->orderBy('numero_scomparto')
            ->get()
            ->map(function (self $s) {
                $quantita = (int) ($s->quantita ?? 0);

                return [
                    'numero' => (int) $s->numero_scomparto,
                    'id_farmaco' => (int) ($s->id_farmaco ?? 0),
                    'nome_farmaco' => $s->farmaco?->nome ?? '---',
                    'quantita' => $quantita,
                ];
            })
            ->toArray();
    }
}
