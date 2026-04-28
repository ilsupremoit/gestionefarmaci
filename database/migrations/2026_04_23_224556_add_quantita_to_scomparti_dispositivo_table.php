<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scomparti_dispositivo', function (Blueprint $table) {
            if (!Schema::hasColumn('scomparti_dispositivo', 'quantita')) {
                $table->integer('quantita')->default(0)->after('pieno');
            }
        });
    }

    public function down(): void
    {
        Schema::table('scomparti_dispositivo', function (Blueprint $table) {
            if (Schema::hasColumn('scomparti_dispositivo', 'quantita')) {
                $table->dropColumn('quantita');
            }
        });
    }
};
