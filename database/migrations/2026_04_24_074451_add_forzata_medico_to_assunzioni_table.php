<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assunzioni', function (Blueprint $table) {
            $table->boolean('forzata_medico')->default(false)->after('apertura_forzata');
        });
    }

    public function down(): void
    {
        Schema::table('assunzioni', function (Blueprint $table) {
            $table->dropColumn('forzata_medico');
        });
    }

};
