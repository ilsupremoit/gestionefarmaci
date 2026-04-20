<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Singolo scomparto fisico del carosello PillMate.
 *
 * @property int      $id
 * @property int      $id_dispositivo
 * @property int      $numero_scomparto  (1-10)
 * @property int      $angolo            (0,20,40...180)
 * @property int|null $id_farmaco
 * @property bool     $pieno
 */
class ScompartoDispositivo extends Model
{
    protected $table = 'scomparti_dispositivo';

    protected $fillable = [
        'id_dispositivo',
        'numero_scomparto',
        'angolo',
        'id_farmaco',
        'pieno',
    ];

    protected $casts = [
        'pieno'            => 'boolean',
        'numero_scomparto' => 'integer',
        'angolo'           => 'integer',
    ];

    // Speculare ad angoliFissi[] nel firmware C++
    public const NUM_SCOMPARTI = 10;
    public const ANGOLI_FISSI  = [0, 20, 40, 60, 80, 100, 120, 140, 160, 180];

    // â”€â”€ Relazioni â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class, 'id_dispositivo');
    }

    public function farmaco(): BelongsTo
    {
        return $this->belongsTo(Farmaco::class, 'id_farmaco');
    }

    // â”€â”€ Helper statici â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Calcola l'angolo fisico per il numero scomparto dato (1-based).
     * Speculare a angoloScomparto() nel firmware.
     */
    public static function calcolaAngolo(int $numeroScomparto): int
    {
        $idx = $numeroScomparto - 1;
        return self::ANGOLI_FISSI[$idx] ?? 0;
    }

    /**
     * Crea i 10 scomparti vuoti per un dispositivo appena registrato.
     */
    public static function inizializzaPerDispositivo(int $idDispositivo): void
    {
        for ($n = 1; $n <= self::NUM_SCOMPARTI; $n++) {
            self::updateOrCreate(
                ['id_dispositivo' => $idDispositivo, 'numero_scomparto' => $n],
                ['angolo' => self::calcolaAngolo($n), 'id_farmaco' => null, 'pieno' => false]
            );
        }
    }

    /**
     * Costruisce l'array JSON per il comando "configura_scomparti" dell'ESP32.
     */
    public static function buildPayloadPerDispositivo(int $idDispositivo): array
    {
        return self::with('farmaco')
            ->where('id_dispositivo', $idDispositivo)
            ->orderBy('numero_scomparto')
            ->get()
            ->map(fn($s) => [
                'numero'       => $s->numero_scomparto,
                'nome_farmaco' => $s->farmaco?->nome ?? 'Non configurato',
                'id_farmaco'   => $s->id_farmaco ?? 0,
                'pieno'        => $s->pieno,
            ])
            ->toArray();
    }
}
