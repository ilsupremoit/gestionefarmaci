<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── pazienti: codice fiscale ────────────────────────────────
        Schema::table('pazienti', function (Blueprint $table) {
            if (!Schema::hasColumn('pazienti', 'codice_fiscale')) {
                $table->string('codice_fiscale', 16)->nullable()->unique()->after('indirizzo');
            }
        });

        // ── notifiche: messaggistica interna ───────────────────────
        Schema::table('notifiche', function (Blueprint $table) {
            // Chi ha mandato il messaggio
            if (!Schema::hasColumn('notifiche', 'id_mittente')) {
                $table->foreignId('id_mittente')->nullable()->after('id_utente')
                      ->constrained('users')->nullOnDelete();
            }
            // Tipo esteso (include 'messaggio' per chat interna)
            // Alterare l'ENUM in modo sicuro
            \DB::statement("
                ALTER TABLE notifiche
                MODIFY COLUMN tipo ENUM('promemoria','allarme','errore','info','messaggio')
                NOT NULL DEFAULT 'info'
            ");
            // Conferma lettura del familiare (timestamp)
            if (!Schema::hasColumn('notifiche', 'letto_at')) {
                $table->dateTime('letto_at')->nullable()->after('letta');
            }
        });

        // ── dispositivi: campo errore_stato per ESP32 ──────────────
        Schema::table('dispositivi', function (Blueprint $table) {
            if (!Schema::hasColumn('dispositivi', 'errore')) {
                // ENUM già esistente potrebbe non avere 'errore'
                \DB::statement("
                    ALTER TABLE dispositivi
                    MODIFY COLUMN stato ENUM('attivo','offline','manutenzione','errore')
                    NOT NULL DEFAULT 'attivo'
                ");
            }
        });
    }

    public function down(): void
    {
        Schema::table('pazienti', function (Blueprint $table) {
            $table->dropColumn('codice_fiscale');
        });
        Schema::table('notifiche', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_mittente');
            $table->dropColumn('letto_at');
            \DB::statement("ALTER TABLE notifiche MODIFY COLUMN tipo ENUM('promemoria','allarme','errore','info') NOT NULL DEFAULT 'info'");
        });
    }
};
