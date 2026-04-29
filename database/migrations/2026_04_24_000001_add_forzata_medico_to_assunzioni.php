<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggiunge le colonne mancanti alla tabella assunzioni:
 *  - forzata_medico   (bool)  — indica se è stata aperta forzatamente dal medico
 *  - id_medico_forzante (fk)  — quale medico ha forzato
 *  - quantita_erogata (int)   — quantità rimasta nel scomparto dopo l'erogazione
 *
 * Tutte le colonne sono protette da hasColumn() per idempotenza.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assunzioni', function (Blueprint $table) {
            if (!Schema::hasColumn('assunzioni', 'forzata_medico')) {
                $table->boolean('forzata_medico')->default(false)->after('apertura_forzata');
            }
            if (!Schema::hasColumn('assunzioni', 'id_medico_forzante')) {
                $table->foreignId('id_medico_forzante')
                      ->nullable()
                      ->after('forzata_medico')
                      ->constrained('users')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('assunzioni', 'quantita_erogata')) {
                $table->integer('quantita_erogata')->nullable()->after('id_medico_forzante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assunzioni', function (Blueprint $table) {
            $table->dropColumn(['quantita_erogata', 'forzata_medico']);
            if (Schema::hasColumn('assunzioni', 'id_medico_forzante')) {
                $table->dropConstrainedForeignId('id_medico_forzante');
            }
        });
    }
};
