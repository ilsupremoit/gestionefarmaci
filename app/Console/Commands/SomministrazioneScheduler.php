<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\ScompartoDispositivo;
use App\Models\Somministrazione;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;

class SomministrazioneScheduler extends Command
{
    protected $signature = 'pillmate:scheduler';
    protected $description = 'Genera assunzioni previste, attiva allarmi e marca le dosi saltate';

    private const GIORNI = [
        1 => 'Lun',
        2 => 'Mar',
        3 => 'Mer',
        4 => 'Gio',
        5 => 'Ven',
        6 => 'Sab',
        7 => 'Dom',
    ];

    public function handle(): void
    {
        $now = now();

        $this->generaAssunzioniDelGiorno($now);
        $this->attivaAllarmiInScadenza($now);
        $this->marcaSaltateScadute($now);
    }

    private function generaAssunzioniDelGiorno(Carbon $now): void
    {
        $giorno = self::GIORNI[(int) $now->dayOfWeekIso];

        $somministrazioni = Somministrazione::with('terapia')
            ->where(function ($q) use ($giorno) {
                $q->where('giorno_settimana', 'Tutti')
                  ->orWhere('giorno_settimana', $giorno);
            })
            ->get();

        foreach ($somministrazioni as $somministrazione) {
            $terapia = $somministrazione->terapia;
            if (!$terapia || !$terapia->attiva) {
                continue;
            }

            if ($now->toDateString() < $terapia->data_inizio?->toDateString()) {
                continue;
            }
            if ($terapia->data_fine && $now->toDateString() > $terapia->data_fine->toDateString()) {
                continue;
            }

            $dataPrevista = Carbon::parse($now->toDateString() . ' ' . substr($somministrazione->ora, 0, 8));

            Assunzione::firstOrCreate(
                [
                    'id_somministrazione' => $somministrazione->id,
                    'data_prevista' => $dataPrevista,
                ],
                [
                    'stato' => 'in_attesa',
                    'confermata_da' => 'sistema',
                ]
            );
        }
    }

    private function attivaAllarmiInScadenza(Carbon $now): void
    {
        $assunzioni = Assunzione::with('somministrazione.terapia.farmaco')
            ->where('stato', 'in_attesa')
            ->where(function ($q) {
                $q->whereNull('allarme_inviato')->orWhere('allarme_inviato', false);
            })
            ->whereBetween('data_prevista', [$now->copy()->subSeconds(30), $now->copy()->addSeconds(30)])
            ->get();

        foreach ($assunzioni as $assunzione) {
            $terapia = $assunzione->somministrazione?->terapia;
            $farmaco = $terapia?->farmaco;
            if (!$terapia || !$farmaco) {
                continue;
            }

            $dispositivo = Dispositivo::where('id_paziente', $terapia->id_paziente)
                ->where('stato', 'attivo')
                ->first();

            if (!$dispositivo) {
                continue;
            }

            $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                ->where('id_farmaco', $farmaco->id)
                ->where('quantita', '>', 0)
                ->first();

            if (!$scomparto) {
                $this->creaNotificaScompartoVuoto($terapia->id_paziente, $farmaco->nome);
                continue;
            }

            MQTT::publish($dispositivo->topicComandi(), json_encode([
                'comando' => 'attiva_allarme',
                'id_farmaco' => $farmaco->id,
            ]));

            $assunzione->update([
                'stato' => 'allarme_attivo',
                'allarme_inviato' => true,
                'data_allarme' => $now,
                'id_dispositivo' => $dispositivo->id,
                'scomparto_numero' => $scomparto->numero_scomparto,
                'note_evento' => 'Allarme attivato automaticamente dallo scheduler',
            ]);

            $this->info("[SCHEDULER] Allarme attivato paziente {$terapia->id_paziente} farmaco {$farmaco->nome}");
        }
    }

    private function marcaSaltateScadute(Carbon $now): void
    {
        $scadute = Assunzione::whereIn('stato', ['in_attesa', 'allarme_attivo'])
            ->where('data_prevista', '<', $now->copy()->subMinutes(30))
            ->get();

        foreach ($scadute as $assunzione) {
            $assunzione->update([
                'stato' => 'saltata',
                'note_evento' => 'Dose non confermata dal paziente entro 30 minuti',
            ]);
        }
    }

    private function creaNotificaScompartoVuoto(int $idPaziente, string $nomeFarmaco): void
    {
        $idUtente = DB::table('pazienti')->where('id', $idPaziente)->value('id_utente');
        if (!$idUtente) {
            return;
        }

        Notifica::create([
            'id_utente' => $idUtente,
            'id_paziente' => $idPaziente,
            'titolo' => 'Scomparto vuoto',
            'messaggio' => "Lo scomparto per {$nomeFarmaco} è vuoto. Necessaria ricarica.",
            'tipo' => 'allarme',
        ]);
    }
}
