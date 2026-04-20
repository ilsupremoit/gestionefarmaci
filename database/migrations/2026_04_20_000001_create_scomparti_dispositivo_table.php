<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mappa scomparto fisico -> farmaco per ogni dispositivo PillMate.
 * Hardware: servo MG90S (180 max) -> 10 scomparti x 20 gradi = 180
 * Angoli: [0, 20, 40, 60, 80, 100, 120, 140, 160, 180]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scomparti_dispositivo', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_dispositivo');
            $table->foreign('id_dispositivo')
                  ->references('id')->on('dispositivi')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unsignedTinyInteger('numero_scomparto'); // 1-10

            // (numero_scomparto - 1) * 20  ->  0, 20, 40 ... 180
            $table->unsignedSmallInteger('angolo')->default(0);

            // null = scomparto non ancora configurato
            $table->unsignedBigInteger('id_farmaco')->nullable();
            $table->foreign('id_farmaco')
                  ->references('id')->on('farmaci')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            // true = pillole fisicamente presenti
            $table->boolean('pieno')->default(false);

            $table->timestamps();

            $table->unique(
                ['id_dispositivo', 'numero_scomparto'],
                'uq_dispositivo_scomparto'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scomparti_dispositivo');
    }
};
