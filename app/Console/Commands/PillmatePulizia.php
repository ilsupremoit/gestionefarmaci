<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

/**
 * pillmate:pulizia
 *
 * Mostra cosa sta causando allarmi nel sistema e permette di
 * resettare lo stato in modo pulito prima di un nuovo ciclo di test.
 *
 * Uso:
 *   php artisan pillmate:pulizia          -> mostra diagnostica + chiede conferma
 *   php artisan pillmate:pulizia --forza  -> esegue la pulizia senza chiedere
 *   php artisan pillmate:pulizia --solo-info -> solo diagnostica, nessuna modifica
 */
class PillmatePulizia extends Command
{
    protected $signature   = 'pillmate:pulizia
                                {--forza       : Esegue la pulizia senza chiedere conferma}
                                {--solo-info   : Mostra solo lo stato, non modifica nulla}';

    protected $description = 'Diagnostica e reset degli allarmi/terapie stantie per sessioni di test';

    public function handle(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════╗');
        $this->line('║   PillMate — Diagnostica allarmi         ║');
        $this->line('╚══════════════════════════════════════════╝');
        $this->newLine();

        // ── 1. Terapie attive (causa principale di allarmi ricorrenti) ──
        $this->line('─── TERAPIE ATTIVE ────────────────────────────────────────');
        $terapie = DB::table('terapie')
            ->join('farmaci', 'terapie.id_farmaco', '=', 'farmaci.id')
            ->join('pazienti', 'terapie.id_paziente', '=', 'pazienti.id')
            ->join('users', 'pazienti.id_utente', '=', 'users.id')
            ->where('terapie.attiva', true)
            ->select(
                'terapie.id',
                'terapie.data_inizio',
                'terapie.data_fine',
                'farmaci.nome as farmaco',
                'users.cognome as paziente'
            )
            ->get();

        if ($terapie->isEmpty()) {
            $this->info('  [OK] Nessuna terapia attiva nel DB.');
        } else {
            foreach ($terapie as $t) {
                $fine = $t->data_fine ?? '⚠ NESSUNA SCADENZA';
                $this->warn("  Terapia ID {$t->id} | {$t->farmaco} | Paziente: {$t->paziente} | Fine: {$fine}");

                $somm = DB::table('somministrazioni')->where('id_terapia', $t->id)->get();
                foreach ($somm as $s) {
                    $this->line("    └─ {$s->giorno_settimana} alle " . substr($s->ora, 0, 5));
                }
            }
        }
        $this->newLine();

        // ── 2. Assunzioni pending (in_attesa e allarme_attivo) ──────────
        $this->line('─── ASSUNZIONI PENDENTI ───────────────────────────────────');
        $pending = DB::table('assunzioni')
            ->join('somministrazioni', 'assunzioni.id_somministrazione', '=', 'somministrazioni.id')
            ->join('terapie', 'somministrazioni.id_terapia', '=', 'terapie.id')
            ->join('farmaci', 'terapie.id_farmaco', '=', 'farmaci.id')
            ->whereIn('assunzioni.stato', ['in_attesa', 'allarme_attivo'])
            ->select(
                'assunzioni.id',
                'assunzioni.stato',
                'assunzioni.data_prevista',
                'assunzioni.data_allarme',
                'assunzioni.id_dispositivo',
                'farmaci.nome as farmaco',
                'terapie.id as id_terapia'
            )
            ->orderBy('assunzioni.data_prevista')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('  [OK] Nessuna assunzione pendente.');
        } else {
            foreach ($pending as $a) {
                $allarme = $a->data_allarme ? ' | allarme: ' . $a->data_allarme : '';
                $disp    = $a->id_dispositivo ? " | disp: #{$a->id_dispositivo}" : ' | disp: null';
                $this->warn("  Assunzione ID {$a->id} | {$a->stato} | {$a->farmaco} | prevista: {$a->data_prevista}{$allarme}{$disp}");
            }
        }
        $this->newLine();

        // ── 3. Riepilogo numeri ─────────────────────────────────────────
        $totTerapie  = $terapie->count();
        $totPending  = $pending->count();
        $totAllarme  = $pending->where('stato', 'allarme_attivo')->count();

        $this->line('─── RIEPILOGO ─────────────────────────────────────────────');
        $this->line("  Terapie attive:            {$totTerapie}");
        $this->line("  Assunzioni in_attesa:       " . $pending->where('stato', 'in_attesa')->count());
        $this->line("  Assunzioni allarme_attivo:  {$totAllarme}");
        $this->newLine();

        if ($totTerapie === 0 && $totPending === 0) {
            $this->info('  Sistema pulito. Nessuna pulizia necessaria.');
            $this->newLine();
            return;
        }

        if ($this->option('solo-info')) {
            $this->line('  [--solo-info] Nessuna modifica eseguita.');
            $this->newLine();
            return;
        }

        // ── 4. Chiedi conferma o esegui direttamente ────────────────────
        $esegui = $this->option('forza') || $this->confirm(
            "Vuoi resettare tutto? (disattiva le terapie + segna le assunzioni pendenti come saltate + disattiva allarmi ESP32)",
            true
        );

        if (!$esegui) {
            $this->warn('  Pulizia annullata dall\'utente.');
            $this->newLine();
            return;
        }

        $this->newLine();
        $this->line('─── PULIZIA IN CORSO ──────────────────────────────────────');

        // 4a. Disattiva allarmi sull'ESP32 per ogni dispositivo coinvolto
        $dispositiviConAllarme = $pending
            ->where('stato', 'allarme_attivo')
            ->pluck('id_dispositivo')
            ->filter()
            ->unique();

        foreach ($dispositiviConAllarme as $idDisp) {
            $dispositivo = Dispositivo::find($idDisp);
            if (!$dispositivo) continue;
            $this->inviaDisattivaAllarme($dispositivo);
        }

        // Se ci sono assunzioni allarme_attivo senza id_dispositivo,
        // cerca il dispositivo dalla terapia e invia ugualmente
        $senzaDisp = $pending->where('stato', 'allarme_attivo')->where('id_dispositivo', null);
        foreach ($senzaDisp as $ass) {
            $dispositivo = DB::table('dispositivi')
                ->join('terapie', 'dispositivi.id_paziente', '=', 'terapie.id_paziente')
                ->join('somministrazioni', 'terapie.id', '=', 'somministrazioni.id_terapia')
                ->where('somministrazioni.id', function ($q) use ($ass) {
                    $q->select('id_somministrazione')
                      ->from('assunzioni')
                      ->where('id', $ass->id)
                      ->limit(1);
                })
                ->select('dispositivi.*')
                ->first();

            if ($dispositivo) {
                $this->inviaDisattivaAllarme((object) $dispositivo);
            }
        }

        // 4b. Marca tutte le assunzioni pendenti come saltate
        if ($totPending > 0) {
            $n = Assunzione::whereIn('stato', ['in_attesa', 'allarme_attivo'])->update([
                'stato'       => 'saltata',
                'note_evento' => 'Reset manuale via pillmate:pulizia',
            ]);
            $this->info("  [OK] {$n} assunzioni pendenti → saltate");
        }

        // 4c. Disattiva tutte le terapie attive
        if ($totTerapie > 0) {
            $n = DB::table('terapie')->where('attiva', true)->update(['attiva' => false]);
            $this->info("  [OK] {$n} terapie → disattivate");
            $this->warn("  ⚠  Le terapie sono state disattivate. Ricrea quelle che ti servono per il prossimo test.");
        }

        $this->newLine();
        $this->info('  Pulizia completata! Sistema pronto per un nuovo ciclo di test.');
        $this->line('  Prossimi passi:');
        $this->line('    1. Crea una nuova terapia dal pannello medico');
        $this->line('    2. Imposta un orario a 2-3 minuti dal momento attuale');
        $this->line('    3. Esegui: php artisan pillmate:scheduler');
        $this->newLine();
    }

    private function inviaDisattivaAllarme(object $dispositivo): void
    {
        if (empty($dispositivo->codice_seriale)) return;

        try {
            $clientId = 'pillmate-pulizia-' . uniqid();
            $settings = (new ConnectionSettings())
                ->setUsername(env('MQTT_AUTH_USERNAME'))
                ->setPassword(env('MQTT_AUTH_PASSWORD'))
                ->setUseTls(true)
                ->setTlsSelfSignedAllowed(true)
                ->setTlsVerifyPeer(false)
                ->setTlsVerifyPeerName(false)
                ->setConnectTimeout(10)
                ->setSocketTimeout(10);

            $client = new MqttClient(
                env('MQTT_HOST'),
                (int) env('MQTT_PORT', 8883),
                $clientId
            );

            $topic = "pillmate/{$dispositivo->codice_seriale}/comandi";

            $client->connect($settings, true);
            $client->publish($topic, json_encode(['comando' => 'disattiva_allarme']), 1);
            $client->disconnect();

            $this->info("  [OK] disattiva_allarme inviato a {$dispositivo->codice_seriale}");
        } catch (\Throwable $e) {
            $this->warn("  [WARN] MQTT fallito per {$dispositivo->codice_seriale}: " . $e->getMessage());
        }
    }
}
