<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabella scomparti_dispositivo se non esiste ancora.
 * Il DB potrebbe già averla (creata manualmente via SQL).
 * Aggiunge anche id_terapia per legare lo scomparto alla somministrazione.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('scomparti_dispositivo')) {
            Schema::create('scomparti_dispositivo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_dispositivo')->constrained('dispositivi')->cascadeOnDelete();
                $table->integer('numero_scomparto'); // 1-8
                $table->integer('angolo')->default(0); // angolo servo precalcolato
                $table->foreignId('id_farmaco')->nullable()->constrained('farmaci')->nullOnDelete();
                $table->foreignId('id_terapia')->nullable()->constrained('terapie')->nullOnDelete();
                $table->boolean('pieno')->default(false);
                $table->timestamps();

                $table->unique(['id_dispositivo', 'numero_scomparto'], 'scomp_disp_numero_unique');
            });
        }

        // Aggiunge colonna id_terapia se manca
        if (!Schema::hasColumn('scomparti_dispositivo', 'id_terapia')) {
            Schema::table('scomparti_dispositivo', function (Blueprint $table) {
                $table->foreignId('id_terapia')
                      ->nullable()
                      ->after('id_farmaco')
                      ->constrained('terapie')
                      ->nullOnDelete();
            });
        }

        // Aggiunge timestamps se mancano
        if (!Schema::hasColumn('scomparti_dispositivo', 'created_at')) {
            Schema::table('scomparti_dispositivo', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scomparti_dispositivo');
    }
};
