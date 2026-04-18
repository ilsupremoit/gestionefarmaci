<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggiunge i campi IoT/MQTT alla tabella dispositivi:
 * wifi_rssi, allarme_attivo, scomparto_attuale, sveglia_impostata,
 * ultimo_payload_at
 *
 * Aggiorna la tabella assunzioni con i nuovi stati e campi:
 * allarme_inviato, data_allarme, apertura_forzata, data_apertura_forzata,
 * note_evento, scomparto_numero, id_dispositivo
 *
 * Aggiunge campi mancanti a notifiche (id_paziente, id_dispositivo, id_assunzione)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── dispositivi: campi IoT mancanti ─────────────────────────
        Schema::table('dispositivi', function (Blueprint $table) {
            if (!Schema::hasColumn('dispositivi', 'wifi_rssi')) {
                $table->integer('wifi_rssi')->nullable()->after('umidita');
            }
            if (!Schema::hasColumn('dispositivi', 'allarme_attivo')) {
                $table->boolean('allarme_attivo')->default(false)->after('wifi_rssi');
            }
            if (!Schema::hasColumn('dispositivi', 'scomparto_attuale')) {
                $table->integer('scomparto_attuale')->nullable()->after('allarme_attivo');
            }
            if (!Schema::hasColumn('dispositivi', 'sveglia_impostata')) {
                $table->time('sveglia_impostata')->nullable()->after('scomparto_attuale');
            }
            if (!Schema::hasColumn('dispositivi', 'ultimo_payload_at')) {
                $table->dateTime('ultimo_payload_at')->nullable()->after('sveglia_impostata');
            }
        });

        // ── assunzioni: nuovi stati + campi allarme/forzata ─────────
        // Modifica l'ENUM degli stati per includere i nuovi valori
        // (Laravel non ha un helper nativo per alterare ENUM, usiamo statement raw)
        \DB::statement("
            ALTER TABLE assunzioni
            MODIFY COLUMN stato ENUM(
                'in_attesa','erogata','assunta','saltata','ritardo',
                'allarme_attivo','apertura_forzata','non_ritirata'
            ) NOT NULL DEFAULT 'in_attesa'
        ");

        Schema::table('assunzioni', function (Blueprint $table) {
            if (!Schema::hasColumn('assunzioni', 'id_dispositivo')) {
                $table->foreignId('id_dispositivo')
                      ->nullable()
                      ->after('id_somministrazione')
                      ->constrained('dispositivi')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('assunzioni', 'allarme_inviato')) {
                $table->boolean('allarme_inviato')->default(false)->after('confermata_da');
            }
            if (!Schema::hasColumn('assunzioni', 'data_allarme')) {
                $table->dateTime('data_allarme')->nullable()->after('allarme_inviato');
            }
            if (!Schema::hasColumn('assunzioni', 'apertura_forzata')) {
                $table->boolean('apertura_forzata')->default(false)->after('data_allarme');
            }
            if (!Schema::hasColumn('assunzioni', 'data_apertura_forzata')) {
                $table->dateTime('data_apertura_forzata')->nullable()->after('apertura_forzata');
            }
            if (!Schema::hasColumn('assunzioni', 'note_evento')) {
                $table->text('note_evento')->nullable()->after('data_apertura_forzata');
            }
            if (!Schema::hasColumn('assunzioni', 'scomparto_numero')) {
                $table->integer('scomparto_numero')->nullable()->after('note_evento');
            }
        });

        // ── notifiche: campi extra ────────────────────────────────────
        Schema::table('notifiche', function (Blueprint $table) {
            if (!Schema::hasColumn('notifiche', 'id_paziente')) {
                $table->foreignId('id_paziente')
                      ->nullable()
                      ->after('id_utente')
                      ->constrained('pazienti')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('notifiche', 'id_dispositivo')) {
                $table->foreignId('id_dispositivo')
                      ->nullable()
                      ->after('id_paziente')
                      ->constrained('dispositivi')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('notifiche', 'id_assunzione')) {
                $table->foreignId('id_assunzione')
                      ->nullable()
                      ->after('id_dispositivo')
                      ->constrained('assunzioni')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // notifiche
        Schema::table('notifiche', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_assunzione');
            $table->dropConstrainedForeignId('id_dispositivo');
            $table->dropConstrainedForeignId('id_paziente');
        });

        // assunzioni
        Schema::table('assunzioni', function (Blueprint $table) {
            $table->dropColumn(['allarme_inviato','data_allarme','apertura_forzata','data_apertura_forzata','note_evento','scomparto_numero']);
            $table->dropConstrainedForeignId('id_dispositivo');
        });

        \DB::statement("
            ALTER TABLE assunzioni
            MODIFY COLUMN stato ENUM('in_attesa','erogata','assunta','saltata','ritardo')
            NOT NULL DEFAULT 'in_attesa'
        ");

        // dispositivi
        Schema::table('dispositivi', function (Blueprint $table) {
            $table->dropColumn(['wifi_rssi','allarme_attivo','scomparto_attuale','sveglia_impostata','ultimo_payload_at']);
        });
    }
};
