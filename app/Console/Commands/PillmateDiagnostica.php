<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PillmateDiagnostica extends Command
{
    protected $signature   = 'pillmate:diagnostica';
    protected $description = 'Controlla DB, modelli, MQTT e configurazione PillMate';

    private int $errori = 0;
    private int $ok = 0;
    private int $warnings = 0;

    public function handle(): void
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('   PillMate - Diagnostica completa    ');
        $this->info('========================================');
        $this->newLine();
        $this->checkDatabase();
        $this->checkModels();
        $this->checkMqttConfig();
        $this->checkDispositivi();
        $this->printSummary();
    }

    private function checkDatabase(): void
    {
        $this->line('--- DATABASE --------------------------');
        foreach (['utenti','pazienti','farmaci','dispositivi','terapie','somministrazioni','assunzioni','notifiche','scomparti_dispositivo'] as $t) {
            Schema::hasTable($t)
                ? $this->msgOk("Tabella '{$t}' (" . DB::table($t)->count() . " righe)")
                : $this->msgErr("Tabella '{$t}' MANCANTE -> php artisan migrate");
        }
        $this->newLine();
        $this->line('  Controllo tipi FK (int vs bigint)...');
        foreach ([
                     ['scomparti_dispositivo','id_dispositivo','int'],
                     ['scomparti_dispositivo','id_farmaco','int'],
                 ] as [$table,$col,$exp]) {
            if (!Schema::hasTable($table)) continue;
            try {
                $row = DB::selectOne("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?",[$table,$col]);
                if ($row && strtolower($row->DATA_TYPE)==='bigint' && $exp==='int')
                    $this->msgErr("  {$table}.{$col} = BIGINT (deve essere INT) -> php artisan pillmate:fix");
                else
                    $this->msgOk("  {$table}.{$col} = " . ($row->DATA_TYPE ?? '?'));
            } catch(\Exception $e) { $this->msgWarn("  Impossibile verificare {$table}.{$col}"); }
        }
        $this->newLine();
    }

    private function checkModels(): void
    {
        $this->line('--- MODELLI ---------------------------');
        foreach (['App\Models\Dispositivo','App\Models\ScompartoDispositivo','App\Models\Farmaco','App\Models\Paziente','App\Models\Assunzione','App\Models\Somministrazione','App\Models\Terapia','App\Models\Notifica'] as $m) {
            class_exists($m) ? $this->msgOk($m) : $this->msgErr("{$m} NON TROVATO");
        }
        $this->newLine();
    }

    private function checkMqttConfig(): void
    {
        $this->line('--- MQTT ------------------------------');
        $host = env('MQTT_HOST','');
        $host ? $this->msgOk("MQTT_HOST = {$host}") : $this->msgErr("MQTT_HOST non impostato nel .env");
        $this->msgOk("MQTT_PORT = " . env('MQTT_PORT','1883'));
        $this->msgOk("MQTT_USE_TLS = " . env('MQTT_USE_TLS','false'));
        try {
            \PhpMqtt\Client\Facades\MQTT::connection()->disconnect();
            $this->msgOk("Connessione MQTT riuscita");
        } catch(\Exception $e) {
            $this->msgErr("Connessione MQTT FALLITA: " . $e->getMessage());
        }
        $this->newLine();
    }

    private function checkDispositivi(): void
    {
        $this->line('--- DISPOSITIVI -----------------------');
        if (!Schema::hasTable('dispositivi')) { $this->msgErr("Tabella mancante"); return; }
        $dispositivi = DB::table('dispositivi')->get();
        if ($dispositivi->isEmpty()) {
            $this->msgWarn("Nessun dispositivo nel DB.");
            $this->line("  Inseriscine uno con codice_seriale = 'disp_01'");
        }
        foreach ($dispositivi as $d) {
            $this->msgOk("{$d->codice_seriale} | stato:{$d->stato} | paziente:{$d->id_paziente}");
            if ($d->codice_seriale !== 'disp_01')
                $this->msgWarn("  Il firmware usa 'disp_01' ma il DB ha '{$d->codice_seriale}'");
            if ($d->stato === 'offline')
                $this->msgWarn("  Offline nel DB -> esegui php artisan pillmate:fix");
            $n = DB::table('scomparti_dispositivo')->where('id_dispositivo',$d->id)->count();
            $n >= 10 ? $this->msgOk("  {$n}/10 scomparti") : $this->msgWarn("  Solo {$n}/10 scomparti -> php artisan pillmate:fix");
        }
        $this->newLine();
    }

    private function msgOk(string $m): void  {
        $this->info("  [OK]     {$m}");
        $this->ok++;
    }

    private function msgErr(string $m): void {
        $this->error("  [ERRORE] {$m}");
        $this->errori++;
    }

    private function msgWarn(string $m): void {
        $this->warn("  [WARN]   {$m}");
        $this->warnings++;
    }

    private function printSummary(): void
    {
        $this->line('----------------------------------------');
        $this->line("  OK:{$this->ok}  Warn:{$this->warnings}  Errori:{$this->errori}");
        if ($this->errori > 0) {
            $this->error('  Errori trovati. Esegui: php artisan pillmate:fix');
        } else {
            $this->info('  Tutto OK!');
        }
        $this->line('----------------------------------------');
        $this->newLine();
    }
}
