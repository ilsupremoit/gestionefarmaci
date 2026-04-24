<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\ScompartoDispositivo;
use App\Models\Somministrazione;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Scheduler PillMate — gira ogni minuto.
 *
 * Flusso:
 *  1. generaAssunzioniDelGiorno  → crea righe "in_attesa" per le somministrazioni di oggi
 *  2. attivaAllarmiInScadenza    → a ±30s dall'orario previsto, manda attiva_allarme all'ESP32
 *  3. marcaSaltateInAttesa       → dopo 30 min senza risposta → "saltata"
 *  4. riesamina allarme_attivo   → se allarme già attivo da > 5 min, manda ancora il suono (reminder 5min)
 *
 * Come funziona la conferma:
 *  - L'ESP32 pubblica su pillmate/disp_xx/eventi il JSON {azione:"pillola_erogata", quantita:N, scomparto_usato:K}
 *  - MqttController::handleEvento() confronta la nuova quantità con quella in DB,
 *    segna l'assunzione come "erogata" e aggiorna scomparti_dispositivo.quantita.
 *  - Se non arriva risposta entro 30 min → "saltata".
 */
class SomministrazioneScheduler extends Command
{
    protected $signature   = 'pillmate:scheduler';
    protected $description = 'Genera assunzioni previste, attiva allarmi e marca le dosi saltate';

    private const GIORNI = [
        1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Gio',
        5 => 'Ven', 6 => 'Sab', 7 => 'Dom',
    ];

    // Minuti di tolleranza oltre i quali la dose è "saltata"
    private const MINUTI_SALTATA = 30;

    // Finestra (secondi) entro cui inviare l'allarme rispetto all'orario esatto
    // 90 secondi = funziona anche se lo scheduler gira a +59s dall'orario
    private const SECONDI_FINESTRA = 90;

    // Ogni quanti minuti suona di nuovo se il paziente non ha ancora risposto
    private const MINUTI_REMINDER = 5;

    public function handle(): void
    {
        $now = now();
        $this->line("[{$now->format('H:i:s')}] Scheduler avviato.");

        $this->generaAssunzioniDelGiorno($now);
        $this->attivaAllarmiInScadenza($now);
        $this->inviaReminder($now);
        $this->marcaSaltateScadute($now);
    }

    // ─────────────────────────────────────────────────────────────
    // 1. Genera assunzioni per oggi
    // ─────────────────────────────────────────────────────────────
    private function generaAssunzioniDelGiorno(Carbon $now): void
    {
        $giorno = self::GIORNI[(int) $now->dayOfWeekIso];

        $somministrazioni = Somministrazione::with('terapia')
            ->where(function ($q) use ($giorno) {
                $q->where('giorno_settimana', 'Tutti')
                  ->orWhere('giorno_settimana', $giorno);
            })
            ->get();

        $create = 0;
        foreach ($somministrazioni as $s) {
            $terapia = $s->terapia;
            if (!$terapia || !$terapia->attiva) continue;
            if ($now->toDateString() < ($terapia->data_inizio?->toDateString() ?? '')) continue;
            if ($terapia->data_fine && $now->toDateString() > $terapia->data_fine->toDateString()) continue;

            $dataPrevista = Carbon::parse($now->toDateString() . ' ' . substr($s->ora, 0, 8));

            $nuovo = Assunzione::firstOrCreate(
                ['id_somministrazione' => $s->id, 'data_prevista' => $dataPrevista],
                ['stato' => 'in_attesa', 'confermata_da' => 'sistema']
            );
            if ($nuovo->wasRecentlyCreated) $create++;
        }

        if ($create) $this->info("  → {$create} assunzioni create per oggi.");
    }

    // ─────────────────────────────────────────────────────────────
    // 2. Attiva allarme quando scatta l'orario
    // ─────────────────────────────────────────────────────────────
    private function attivaAllarmiInScadenza(Carbon $now): void
    {
        $assunzioni = Assunzione::with('somministrazione.terapia.farmaco')
            ->where('stato', 'in_attesa')
            ->where(function ($q) {
                $q->whereNull('allarme_inviato')->orWhere('allarme_inviato', false);
            })
            ->whereBetween('data_prevista', [
                $now->copy()->subSeconds(self::SECONDI_FINESTRA),
                $now->copy()->addSeconds(self::SECONDI_FINESTRA),
            ])
            ->get();

        foreach ($assunzioni as $assunzione) {
            $this->inviaAllarme($assunzione, $now, 'automatico');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Reminder ogni 5 minuti se allarme già attivo (non confermato)
    // ─────────────────────────────────────────────────────────────
    private function inviaReminder(Carbon $now): void
    {
        $assunzioni = Assunzione::with('somministrazione.terapia.farmaco')
            ->where('stato', 'allarme_attivo')
            ->where('data_allarme', '<=', $now->copy()->subMinutes(self::MINUTI_REMINDER))
            ->get();

        foreach ($assunzioni as $assunzione) {
            $this->inviaAllarme($assunzione, $now, 'reminder');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Marca saltate quelle scadute
    // ─────────────────────────────────────────────────────────────
    private function marcaSaltateScadute(Carbon $now): void
    {
        $n = Assunzione::whereIn('stato', ['in_attesa', 'allarme_attivo'])
            ->where('data_prevista', '<', $now->copy()->subMinutes(self::MINUTI_SALTATA))
            ->update([
                'stato'       => 'saltata',
                'note_evento' => 'Dose non confermata entro ' . self::MINUTI_SALTATA . ' minuti',
            ]);

        if ($n) $this->warn("  → {$n} assunzioni marcate come saltate.");
    }

    // ─────────────────────────────────────────────────────────────
    // Helper: pubblica attiva_allarme su MQTT
    // ─────────────────────────────────────────────────────────────
    private function inviaAllarme(Assunzione $assunzione, Carbon $now, string $tipo): void
    {
        $terapia = $assunzione->somministrazione?->terapia;
        $farmaco = $terapia?->farmaco;
        if (!$terapia || !$farmaco) return;

        $dispositivo = Dispositivo::where('id_paziente', $terapia->id_paziente)
            ->where('stato', 'attivo')
            ->first();

        if (!$dispositivo) {
            $this->warn("  → Nessun dispositivo attivo per paziente {$terapia->id_paziente}");
            return;
        }

        $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
            ->where('id_farmaco', $farmaco->id)
            ->where('quantita', '>', 0)
            ->first();

        if (!$scomparto) {
            $this->warn("  → Scomparto vuoto: {$farmaco->nome} (paziente {$terapia->id_paziente})");
            $this->creaNotificaScompartoVuoto($terapia->id_paziente, $farmaco->nome);
            $assunzione->update(['stato' => 'saltata', 'note_evento' => 'Scomparto vuoto al momento dell\'allarme']);
            return;
        }

        try {
            MQTT::publish($dispositivo->topicComandi(), json_encode([
                'comando'          => 'attiva_allarme',
                'id_farmaco'       => (int) $farmaco->id,
                'numero_scomparto' => (int) $scomparto->numero_scomparto,
            ], JSON_UNESCAPED_UNICODE));

            $assunzione->update([
                'stato'            => 'allarme_attivo',
                'allarme_inviato'  => true,
                'data_allarme'     => $now,
                'id_dispositivo'   => $dispositivo->id,
                'scomparto_numero' => $scomparto->numero_scomparto,
                'note_evento'      => "Allarme {$tipo} inviato alle " . $now->format('H:i'),
            ]);

            $this->info("  → Allarme [{$tipo}] paziente {$terapia->id_paziente} — {$farmaco->nome} scomp.{$scomparto->numero_scomparto}");
        } catch (\Throwable $e) {
            $this->error("  → Errore MQTT allarme: " . $e->getMessage());
        }
    }

    private function creaNotificaScompartoVuoto(int $idPaziente, string $nomeFarmaco): void
    {
        $idUtente = DB::table('pazienti')->where('id', $idPaziente)->value('id_utente');
        if (!$idUtente) return;

        DB::table('notifiche')->insert([
            'id_utente'  => $idUtente,
            'id_paziente'=> $idPaziente,
            'titolo'     => 'Scomparto vuoto — ' . $nomeFarmaco,
            'messaggio'  => "Lo scomparto per {$nomeFarmaco} è vuoto. Necessaria ricarica urgente.",
            'tipo'       => 'allarme',
            'letta'      => false,
            'data_invio' => now(),
        ]);
    }
}
