<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScompartoDispositivo extends Model
{
    protected $table = 'scomparti_dispositivo';

    public const NUM_SCOMPARTI = 8;

    // Angoli firmware C++ (indice 0-based)
    public const ANGOLI = [26, 46, 67, 93, 113, 137, 160, 180];

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
        'pieno'    => 'boolean',
        'quantita' => 'integer',
    ];

    // ── Relazioni ─────────────────────────────────────────────────

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

    // ── Helpers statici ───────────────────────────────────────────

    public static function calcolaAngolo(int $numero): int
    {
        return self::ANGOLI[$numero - 1] ?? 0;
    }

    public static function inizializzaPerDispositivo(int $idDispositivo): void
    {
        for ($i = 1; $i <= self::NUM_SCOMPARTI; $i++) {
            self::firstOrCreate(
                ['id_dispositivo' => $idDispositivo, 'numero_scomparto' => $i],
                ['angolo' => self::calcolaAngolo($i), 'pieno' => false, 'quantita' => 0]
            );
        }
    }

    /**
     * Costruisce il payload MQTT configura_scomparti con quantita.
     * Il firmware C++ leggerà "quantita" invece di "pieno".
     */
    public static function buildPayloadPerDispositivo(int $idDispositivo): array
    {
        self::inizializzaPerDispositivo($idDispositivo);

        return self::with('farmaco')
            ->where('id_dispositivo', $idDispositivo)
            ->orderBy('numero_scomparto')
            ->get()
            ->map(fn(self $s) => [
                'numero'       => (int) $s->numero_scomparto,
                'id_farmaco'   => (int) ($s->id_farmaco ?? 0),
                'nome_farmaco' => $s->farmaco?->nome ?? '---',
                'quantita'     => (int) ($s->quantita ?? 0),
                // pieno rimane per retro-compatibilità firmware vecchio
                'pieno'        => ($s->quantita ?? 0) > 0,
            ])
            ->toArray();
    }
}
