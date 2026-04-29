<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\ScompartoDispositivo;
use App\Models\Somministrazione;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Scheduler PillMate — gira ogni minuto.
 *
 * Flusso:
 *  1. generaAssunzioniDelGiorno  → crea righe "in_attesa" per le somministrazioni di oggi
 *  2. attivaAllarmiInScadenza    → all'orario previsto manda attiva_allarme all'ESP32
 *  3. inviaReminder              → se allarme già attivo da > 5 min, manda ancora il suono
 *  4. marcaSaltateScadute        → dopo 30 min senza risposta → "saltata"
 *
 * Come funziona la conferma:
 *  - L'ESP32 pubblica su pillmate/disp_xx/eventi il JSON:
 *    {azione:"pillola_erogata", quantita:N, scomparto_usato:K}
 *
 *  - MqttController::handleEvento() confronta la nuova quantità con quella in DB,
 *    segna l'assunzione come "erogata" e aggiorna scomparti_dispositivo.quantita.
 *
 *  - Se non arriva risposta entro 30 min → "saltata".
 */
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

    // Minuti di tolleranza oltre i quali la dose è "saltata"
    private const MINUTI_SALTATA = 30;

    // Finestra entro cui inviare l'allarme rispetto all'orario previsto.
    // 180 secondi = 3 minuti. Utile per non perdere l'orario durante i test.
    private const SECONDI_FINESTRA = 180;

    // Ogni quanti minuti suona di nuovo se il paziente non ha ancora risposto
    private const MINUTI_REMINDER = 5;

    public function handle(): void
    {
        $now = now();

        $this->line('');
        $this->line('========== PillMate Scheduler ==========');
        $this->line("Ora server: {$now->format('Y-m-d H:i:s')}");
        $this->line('========================================');

        Log::info('PillMate scheduler avviato', [
            'ora_server' => $now->format('Y-m-d H:i:s'),
        ]);

        $this->generaAssunzioniDelGiorno($now);
        $this->attivaAllarmiInScadenza($now);
        $this->inviaReminder($now);
        $this->marcaSaltateScadute($now);

        $this->line('Scheduler completato.');
        $this->line('');

        Log::info('PillMate scheduler completato', [
            'ora_server' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 1. Genera assunzioni per oggi
    // ─────────────────────────────────────────────────────────────
    private function generaAssunzioniDelGiorno(Carbon $now): void
    {
        $giorno = self::GIORNI[(int) $now->dayOfWeekIso];

        $this->line("Giorno rilevato: {$giorno}");

        $somministrazioni = Somministrazione::with('terapia')
            ->where(function ($q) use ($giorno) {
                $q->where('giorno_settimana', 'Tutti')
                    ->orWhere('giorno_settimana', $giorno);
            })
            ->get();

        $this->line('Somministrazioni trovate per oggi: ' . $somministrazioni->count());

        Log::info('PillMate somministrazioni trovate per oggi', [
            'giorno' => $giorno,
            'count' => $somministrazioni->count(),
        ]);

        $create = 0;

        foreach ($somministrazioni as $s) {
            $terapia = $s->terapia;

            if (!$terapia) {
                $this->warn("  → Somministrazione {$s->id}: terapia mancante.");

                Log::warning('PillMate somministrazione senza terapia', [
                    'id_somministrazione' => $s->id,
                ]);

                continue;
            }

            if (!$terapia->attiva) {
                $this->line("  → Somministrazione {$s->id}: terapia non attiva.");
                continue;
            }

            if ($terapia->data_inizio && $now->toDateString() < $terapia->data_inizio->toDateString()) {
                $this->line("  → Somministrazione {$s->id}: terapia non ancora iniziata.");
                continue;
            }

            if ($terapia->data_fine && $now->toDateString() > $terapia->data_fine->toDateString()) {
                $this->line("  → Somministrazione {$s->id}: terapia terminata.");
                continue;
            }

            $ora = substr($s->ora, 0, 8);
            $dataPrevista = Carbon::parse($now->toDateString() . ' ' . $ora);

            $nuovo = Assunzione::firstOrCreate(
                [
                    'id_somministrazione' => $s->id,
                    'data_prevista' => $dataPrevista,
                ],
                [
                    'stato' => 'in_attesa',
                    'confermata_da' => 'sistema',
                ]
            );

            if ($nuovo->wasRecentlyCreated) {
                $create++;

                $this->info("  → Creata assunzione ID {$nuovo->id} per le {$dataPrevista->format('H:i:s')}");

                Log::info('PillMate assunzione creata', [
                    'id_assunzione' => $nuovo->id,
                    'id_somministrazione' => $s->id,
                    'data_prevista' => $dataPrevista->format('Y-m-d H:i:s'),
                    'id_terapia' => $terapia->id ?? null,
                    'id_paziente' => $terapia->id_paziente ?? null,
                ]);
            }
        }

        if ($create > 0) {
            $this->info("  → {$create} assunzioni create per oggi.");
        } else {
            $this->line('  → Nessuna nuova assunzione creata.');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 2. Attiva allarme quando scatta l'orario
    // ─────────────────────────────────────────────────────────────
    private function attivaAllarmiInScadenza(Carbon $now): void
    {
        $inizioFinestra = $now->copy()->subSeconds(self::SECONDI_FINESTRA);
        $fineFinestra = $now->copy()->addSeconds(self::SECONDI_FINESTRA);

        $this->line(
            'Cerco allarmi in scadenza tra '
            . $inizioFinestra->format('H:i:s')
            . ' e '
            . $fineFinestra->format('H:i:s')
        );

        $assunzioni = Assunzione::with('somministrazione.terapia.farmaco')
            ->where('stato', 'in_attesa')
            ->where(function ($q) {
                $q->whereNull('allarme_inviato')
                    ->orWhere('allarme_inviato', false);
            })
            ->whereBetween('data_prevista', [
                $inizioFinestra,
                $fineFinestra,
            ])
            ->get();

        $this->line('Assunzioni in scadenza trovate: ' . $assunzioni->count());

        Log::info('PillMate ricerca allarmi in scadenza', [
            'inizio_finestra' => $inizioFinestra->format('Y-m-d H:i:s'),
            'fine_finestra' => $fineFinestra->format('Y-m-d H:i:s'),
            'count' => $assunzioni->count(),
        ]);

        foreach ($assunzioni as $assunzione) {
            $this->inviaAllarme($assunzione, $now, 'automatico');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Reminder ogni 5 minuti se allarme già attivo
    // ─────────────────────────────────────────────────────────────
    private function inviaReminder(Carbon $now): void
    {
        $limiteReminder = $now->copy()->subMinutes(self::MINUTI_REMINDER);

        $assunzioni = Assunzione::with('somministrazione.terapia.farmaco')
            ->where('stato', 'allarme_attivo')
            ->where('data_allarme', '<=', $limiteReminder)
            ->get();

        $this->line('Reminder da inviare trovati: ' . $assunzioni->count());

        Log::info('PillMate ricerca reminder', [
            'limite_data_allarme' => $limiteReminder->format('Y-m-d H:i:s'),
            'count' => $assunzioni->count(),
        ]);

        foreach ($assunzioni as $assunzione) {
            $this->inviaAllarme($assunzione, $now, 'reminder');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Marca saltate quelle scadute
    // ─────────────────────────────────────────────────────────────
    private function marcaSaltateScadute(Carbon $now): void
    {
        $limite = $now->copy()->subMinutes(self::MINUTI_SALTATA);

        $n = Assunzione::whereIn('stato', ['in_attesa', 'allarme_attivo'])
            ->where('data_prevista', '<', $limite)
            ->update([
                'stato' => 'saltata',
                'note_evento' => 'Dose non confermata entro ' . self::MINUTI_SALTATA . ' minuti',
            ]);

        if ($n) {
            $this->warn("  → {$n} assunzioni marcate come saltate.");

            Log::warning('PillMate assunzioni marcate come saltate', [
                'count' => $n,
                'limite' => $limite->format('Y-m-d H:i:s'),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helper: pubblica attiva_allarme su MQTT
    // ─────────────────────────────────────────────────────────────
    private function inviaAllarme(Assunzione $assunzione, Carbon $now, string $tipo): void
    {
        $terapia = $assunzione->somministrazione?->terapia;
        $farmaco = $terapia?->farmaco;

        if (!$terapia || !$farmaco) {
            $this->warn("  → Assunzione {$assunzione->id}: terapia o farmaco mancante.");

            Log::warning('PillMate allarme non inviato: terapia o farmaco mancante', [
                'id_assunzione' => $assunzione->id,
            ]);

            return;
        }

        $dispositivo = Dispositivo::where('id_paziente', $terapia->id_paziente)
            ->where('stato', 'attivo')
            ->first();

        if (!$dispositivo) {
            $this->warn("  → Nessun dispositivo attivo per paziente {$terapia->id_paziente}");

            Log::warning('PillMate allarme non inviato: nessun dispositivo attivo', [
                'id_assunzione' => $assunzione->id,
                'id_paziente' => $terapia->id_paziente,
                'id_farmaco' => $farmaco->id,
                'farmaco' => $farmaco->nome,
            ]);

            return;
        }

        $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
            ->where('id_farmaco', $farmaco->id)
            ->where('quantita', '>', 0)
            ->orderBy('numero_scomparto')
            ->first();

        if (!$scomparto) {
            $this->warn("  → Scomparto vuoto o non trovato: {$farmaco->nome} (paziente {$terapia->id_paziente})");

            Log::warning('PillMate allarme non inviato: scomparto vuoto o non trovato', [
                'id_assunzione' => $assunzione->id,
                'id_paziente' => $terapia->id_paziente,
                'id_dispositivo' => $dispositivo->id,
                'id_farmaco' => $farmaco->id,
                'farmaco' => $farmaco->nome,
            ]);

            $this->creaNotificaScompartoVuoto($terapia->id_paziente, $farmaco->nome);

            $assunzione->update([
                'stato' => 'saltata',
                'note_evento' => 'Scomparto vuoto al momento dell\'allarme',
            ]);

            return;
        }

        $topic = $dispositivo->topicComandi();

        $payload = [
            'comando' => 'attiva_allarme',
            'id_farmaco' => (int) $farmaco->id,
            'numero_scomparto' => (int) $scomparto->numero_scomparto,
        ];

        try {
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

            $this->info("  → MQTT topic: {$topic}");
            $this->info("  → MQTT payload: {$jsonPayload}");

            Log::info('PillMate MQTT allarme - prima della publish', [
                'tipo' => $tipo,
                'topic' => $topic,
                'payload' => $payload,
                'json' => $jsonPayload,
                'id_assunzione' => $assunzione->id,
                'id_paziente' => $terapia->id_paziente,
                'id_dispositivo' => $dispositivo->id,
                'codice_seriale' => $dispositivo->codice_seriale ?? null,
                'id_farmaco' => $farmaco->id,
                'farmaco' => $farmaco->nome,
                'id_scomparto' => $scomparto->id,
                'numero_scomparto' => $scomparto->numero_scomparto,
                'quantita_scomparto' => $scomparto->quantita,
            ]);

            MQTT::publish($topic, $jsonPayload);

            $assunzione->update([
                'stato' => 'allarme_attivo',
                'allarme_inviato' => true,
                'data_allarme' => $now,
                'id_dispositivo' => $dispositivo->id,
                'scomparto_numero' => $scomparto->numero_scomparto,
                'note_evento' => "Allarme {$tipo} inviato alle " . $now->format('H:i'),
            ]);

            $this->info(
                "  → Allarme [{$tipo}] inviato: paziente {$terapia->id_paziente}, "
                . "{$farmaco->nome}, scomparto {$scomparto->numero_scomparto}"
            );

            Log::info('PillMate MQTT allarme - publish completata', [
                'tipo' => $tipo,
                'topic' => $topic,
                'payload' => $payload,
                'id_assunzione' => $assunzione->id,
                'id_paziente' => $terapia->id_paziente,
                'id_dispositivo' => $dispositivo->id,
                'id_farmaco' => $farmaco->id,
                'farmaco' => $farmaco->nome,
                'numero_scomparto' => $scomparto->numero_scomparto,
            ]);
        } catch (\Throwable $e) {
            $this->error('  → Errore MQTT allarme: ' . $e->getMessage());

            Log::error('PillMate errore MQTT allarme', [
                'errore' => $e->getMessage(),
                'topic' => $topic,
                'payload' => $payload,
                'id_assunzione' => $assunzione->id,
                'id_paziente' => $terapia->id_paziente,
                'id_dispositivo' => $dispositivo->id,
                'id_farmaco' => $farmaco->id,
                'farmaco' => $farmaco->nome,
                'numero_scomparto' => $scomparto->numero_scomparto,
            ]);
        }
    }

    private function creaNotificaScompartoVuoto(int $idPaziente, string $nomeFarmaco): void
    {
        $idUtente = DB::table('pazienti')
            ->where('id', $idPaziente)
            ->value('id_utente');

        if (!$idUtente) {
            Log::warning('PillMate notifica scomparto vuoto non creata: utente paziente non trovato', [
                'id_paziente' => $idPaziente,
                'farmaco' => $nomeFarmaco,
            ]);

            return;
        }

        DB::table('notifiche')->insert([
            'id_utente' => $idUtente,
            'id_paziente' => $idPaziente,
            'titolo' => 'Scomparto vuoto — ' . $nomeFarmaco,
            'messaggio' => "Lo scomparto per {$nomeFarmaco} è vuoto. Necessaria ricarica urgente.",
            'tipo' => 'allarme',
            'letta' => false,
            'data_invio' => now(),
        ]);

        Log::info('PillMate notifica scomparto vuoto creata', [
            'id_utente' => $idUtente,
            'id_paziente' => $idPaziente,
            'farmaco' => $nomeFarmaco,
        ]);
    }
}
