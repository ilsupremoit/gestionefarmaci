<?php

namespace App\Console\Commands;

use App\Models\Dispositivo;
use App\Models\ScompartoDispositivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PillmateFix extends Command
{
    protected $signature   = 'pillmate:fix';
    protected $description = 'Corregge automaticamente FK, scomparti e stato dispositivi';

    public function handle(): void
    {
        $this->newLine();
        $this->line('â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—');
        $this->line('â•‘   PillMate - Fix automatico          â•‘');
        $this->line('â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•');
        $this->newLine();
        $this->fixForeignKeys();
        $this->fixScomparti();
        $this->fixStatoDispositivi();
        $this->fixRetainedOfflineMessage();
        $this->line('  <fg=green>Fix completato! Esegui: php artisan pillmate:diagnostica</>');
        $this->newLine();
    }

    private function fixForeignKeys(): void
    {
        $this->line('â”€â”€ Fix 1: Tipi FK (bigint -> int) â”€â”€â”€â”€â”€â”€â”€');
        if (!Schema::hasTable('scomparti_dispositivo')) { $this->warn('  Tabella mancante, salto.'); $this->newLine(); return; }
        try {
            $row = DB::selectOne("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='scomparti_dispositivo' AND COLUMN_NAME='id_dispositivo'");
            if ($row && strtolower($row->DATA_TYPE) === 'bigint') {
                DB::statement('ALTER TABLE scomparti_dispositivo DROP FOREIGN KEY IF EXISTS scomparti_dispositivo_id_dispositivo_foreign');
                DB::statement('ALTER TABLE scomparti_dispositivo DROP FOREIGN KEY IF EXISTS scomparti_dispositivo_id_farmaco_foreign');
                DB::statement('ALTER TABLE scomparti_dispositivo MODIFY id_dispositivo INT(11) UNSIGNED NOT NULL');
                DB::statement('ALTER TABLE scomparti_dispositivo MODIFY id_farmaco INT(11) UNSIGNED NULL');
                DB::statement('ALTER TABLE scomparti_dispositivo ADD CONSTRAINT scomparti_dispositivo_id_dispositivo_foreign FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id) ON DELETE CASCADE ON UPDATE CASCADE');
                DB::statement('ALTER TABLE scomparti_dispositivo ADD CONSTRAINT scomparti_dispositivo_id_farmaco_foreign FOREIGN KEY (id_farmaco) REFERENCES farmaci(id) ON DELETE SET NULL ON UPDATE CASCADE');
                $this->info('  [OK] BIGINT -> INT corretto');
            } else {
                $this->info('  [OK] Tipi FK gia corretti');
            }
        } catch(\Exception $e) {
            $this->error('  [ERRORE] ' . $e->getMessage());
            $this->line('  Esegui manualmente in phpMyAdmin:');
            $this->line('  ALTER TABLE scomparti_dispositivo MODIFY id_dispositivo INT(11) UNSIGNED NOT NULL;');
            $this->line('  ALTER TABLE scomparti_dispositivo MODIFY id_farmaco INT(11) UNSIGNED NULL;');
        }
        $this->newLine();
    }

    private function fixScomparti(): void
    {
        $this->line('â”€â”€ Fix 2: Scomparti mancanti â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€');
        if (!Schema::hasTable('scomparti_dispositivo') || !Schema::hasTable('dispositivi')) { $this->warn('Tabelle mancanti.'); $this->newLine(); return; }
        foreach (Dispositivo::all() as $d) {
            $count = ScompartoDispositivo::where('id_dispositivo', $d->id)->count();
            if ($count < ScompartoDispositivo::NUM_SCOMPARTI) {
                ScompartoDispositivo::inizializzaPerDispositivo($d->id);
                $this->info("  [OK] Scomparti inizializzati per {$d->codice_seriale}");
            } else {
                $this->info("  [OK] {$d->codice_seriale} ha gia {$count} scomparti");
            }
        }
        $this->newLine();
    }

    private function fixStatoDispositivi(): void
    {
        $this->line('â”€â”€ Fix 3: Dispositivi bloccati offline â”€â”€');
        $offline = DB::table('dispositivi')->where('stato','offline')->get();
        if ($offline->isEmpty()) { $this->info('  [OK] Nessun dispositivo offline'); $this->newLine(); return; }
        foreach ($offline as $d) {
            DB::table('dispositivi')->where('id',$d->id)->update(['stato'=>'attivo','ultima_connessione'=>now()]);
            $this->info("  [OK] {$d->codice_seriale} -> stato forzato ad 'attivo'");
        }
        $this->newLine();
    }

    private function fixRetainedOfflineMessage(): void
    {
        $this->line('â”€â”€ Fix 4: Cancella retained offline â”€â”€â”€â”€â”€');
        $dispositivi = DB::table('dispositivi')->get();
        if ($dispositivi->isEmpty()) { $this->warn('  Nessun dispositivo, salto.'); $this->newLine(); return; }
        try {
            $mqtt = \PhpMqtt\Client\Facades\MQTT::connection();
            foreach ($dispositivi as $d) {
                $topic = "pillmate/{$d->codice_seriale}/stato";
                $mqtt->publish($topic, '', 1, true);
                $this->info("  [OK] Retained cancellato per {$d->codice_seriale}");
            }
            $mqtt->disconnect();
        } catch(\Exception $e) {
            $this->warn("  Impossibile via PHP: " . $e->getMessage());
            $this->line("  FIX MANUALE con MQTTX/MQTT Explorer:");
            foreach ($dispositivi as $d) {
                $this->line("    Topic: pillmate/{$d->codice_seriale}/stato | Payload: (vuoto) | Retain: ON");
            }
        }
        $this->newLine();
    }
}
