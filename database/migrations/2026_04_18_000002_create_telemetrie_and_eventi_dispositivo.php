<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea le tabelle MQTT/IoT:
 * - telemetrie_dispositivo  (storico telemetria)
 * - eventi_dispositivo      (log comandi/eventi)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Telemetria storica ────────────────────────────────────────
        if (!Schema::hasTable('telemetrie_dispositivo')) {
            Schema::create('telemetrie_dispositivo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_dispositivo')
                      ->constrained('dispositivi')
                      ->cascadeOnDelete();
                $table->decimal('temperatura', 5, 2)->nullable();
                $table->decimal('umidita', 5, 2)->nullable();
                $table->boolean('allarme_attivo')->default(false);
                $table->integer('wifi_rssi')->nullable();
                $table->integer('scomparto_attuale')->nullable();
                $table->time('sveglia_impostata')->nullable();
                $table->dateTime('timestamp_dispositivo')->nullable();
                $table->longText('payload_json')->nullable();
                $table->dateTime('created_at')->useCurrent();

                $table->index(['id_dispositivo', 'timestamp_dispositivo'], 'idx_tel_disp_data');
                $table->index('created_at', 'idx_tel_created');
            });
        }

        // ── Log eventi / comandi ──────────────────────────────────────
        if (!Schema::hasTable('eventi_dispositivo')) {
            Schema::create('eventi_dispositivo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_dispositivo')
                      ->constrained('dispositivi')
                      ->cascadeOnDelete();
                $table->foreignId('id_paziente')
                      ->nullable()
                      ->constrained('pazienti')
                      ->nullOnDelete();
                $table->foreignId('id_assunzione')
                      ->nullable()
                      ->constrained('assunzioni')
                      ->nullOnDelete();
                $table->string('topic', 150)->nullable();
                $table->string('azione', 100);
                $table->string('metodo_attivazione', 100)->nullable();
                $table->enum('severita', ['info','warning','critico'])->default('info');
                $table->text('messaggio')->nullable();
                $table->dateTime('timestamp_dispositivo')->nullable();
                $table->longText('payload_json')->nullable();
                $table->dateTime('created_at')->useCurrent();

                $table->index(['id_dispositivo', 'timestamp_dispositivo'], 'idx_eventi_disp_data');
                $table->index('azione', 'idx_eventi_azione');
                $table->index('id_assunzione', 'idx_eventi_assunzione');
                $table->index('id_paziente', 'idx_eventi_paziente');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eventi_dispositivo');
        Schema::dropIfExists('telemetrie_dispositivo');
    }
};
