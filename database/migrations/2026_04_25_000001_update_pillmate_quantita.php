<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Aggiunge quantita agli scomparti (al posto del solo bool pieno)
        Schema::table('scomparti_dispositivo', function (Blueprint $table) {
            if (!Schema::hasColumn('scomparti_dispositivo', 'quantita')) {
                $table->unsignedSmallInteger('quantita')->default(0)->after('pieno');
            }
        });

        // 2. Arricchisce assunzioni con info erogazione
        Schema::table('assunzioni', function (Blueprint $table) {
            if (!Schema::hasColumn('assunzioni', 'quantita_erogata')) {
                $table->unsignedSmallInteger('quantita_erogata')->nullable()->after('scomparto_numero')
                    ->comment('Quantità ricevuta dal broker MQTT (pillola_erogata)');
            }
            if (!Schema::hasColumn('assunzioni', 'forzata_medico')) {
                $table->boolean('forzata_medico')->default(false)->after('quantita_erogata');
            }
            if (!Schema::hasColumn('assunzioni', 'id_medico_forzante')) {
                $table->foreignId('id_medico_forzante')->nullable()->after('forzata_medico')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('scomparti_dispositivo', function (Blueprint $table) {
            $table->dropColumnIfExists('quantita');
        });
        Schema::table('assunzioni', function (Blueprint $table) {
            $table->dropColumnIfExists('quantita_erogata');
            $table->dropColumnIfExists('forzata_medico');
            $table->dropForeignIfExists(['id_medico_forzante']);
            $table->dropColumnIfExists('id_medico_forzante');
        });
    }
};
