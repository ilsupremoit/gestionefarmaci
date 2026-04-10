<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabella principale utenti (sostituisce la default 'users' di Laravel)
        Schema::create('utenti', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 50);
            $table->string('cognome', 50);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->enum('ruolo', ['paziente', 'medico', 'familiare', 'admin']);
            $table->string('telefono', 20)->nullable();
            $table->rememberToken(); // necessario per Auth::attempt() con remember me
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        // Tabella pazienti — estende utenti con dati medici
        Schema::create('pazienti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utente')->unique()->constrained('utenti')->cascadeOnDelete();
            $table->date('data_nascita')->nullable();
            $table->string('indirizzo', 150)->nullable();
            $table->text('note_mediche')->nullable();
        });

        // Tabella farmaci
        Schema::create('farmaci', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->text('descrizione')->nullable();
            $table->string('dose', 50)->nullable();
            $table->text('note')->nullable();
        });

        // Relazione medici ↔ pazienti
        Schema::create('medici_pazienti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_medico')->constrained('utenti')->cascadeOnDelete();
            $table->foreignId('id_paziente')->constrained('pazienti')->cascadeOnDelete();
            $table->unique(['id_medico', 'id_paziente']);
        });

        // Relazione familiari ↔ pazienti
        Schema::create('familiari_pazienti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_familiare')->constrained('utenti')->cascadeOnDelete();
            $table->foreignId('id_paziente')->constrained('pazienti')->cascadeOnDelete();
            $table->string('grado_parentela', 30)->nullable();
            $table->unique(['id_familiare', 'id_paziente']);
        });

        // Dispositivi IoT
        Schema::create('dispositivi', function (Blueprint $table) {
            $table->id();
            $table->string('codice_seriale', 100)->unique();
            $table->foreignId('id_paziente')->constrained('pazienti')->cascadeOnDelete();
            $table->string('nome_dispositivo', 50)->nullable();
            $table->enum('stato', ['attivo', 'offline', 'manutenzione'])->default('attivo');
            $table->dateTime('ultima_connessione')->nullable();
            $table->integer('batteria')->nullable();
            $table->decimal('temperatura', 5, 2)->nullable();
            $table->decimal('umidita', 5, 2)->nullable();
        });

        // Terapie
        Schema::create('terapie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_paziente')->constrained('pazienti')->cascadeOnDelete();
            $table->foreignId('id_medico')->constrained('utenti')->cascadeOnDelete();
            $table->foreignId('id_farmaco')->constrained('farmaci')->cascadeOnDelete();
            $table->date('data_inizio');
            $table->date('data_fine')->nullable();
            $table->string('frequenza', 50)->nullable();
            $table->integer('quantita');
            $table->text('istruzioni')->nullable();
            $table->boolean('attiva')->default(true);
        });

        // Somministrazioni pianificate
        Schema::create('somministrazioni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_terapia')->constrained('terapie')->cascadeOnDelete();
            $table->time('ora');
            $table->enum('giorno_settimana', ['Lun','Mar','Mer','Gio','Ven','Sab','Dom','Tutti'])->default('Tutti');
        });

        // Assunzioni effettive
        Schema::create('assunzioni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_somministrazione')->constrained('somministrazioni')->cascadeOnDelete();
            $table->dateTime('data_prevista');
            $table->dateTime('data_erogazione')->nullable();
            $table->dateTime('data_conferma')->nullable();
            $table->enum('stato', ['in_attesa','erogata','assunta','saltata','ritardo'])->default('in_attesa');
            $table->enum('confermata_da', ['paziente','sensore','familiare','sistema'])->default('sistema');
        });

        // Feedback paziente
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_paziente')->constrained('pazienti')->cascadeOnDelete();
            $table->foreignId('id_assunzione')->nullable()->constrained('assunzioni')->nullOnDelete();
            $table->text('messaggio');
            $table->string('stato_salute', 100)->nullable();
            $table->dateTime('data_feedback')->useCurrent();
        });

        // Notifiche
        Schema::create('notifiche', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utente')->constrained('utenti')->cascadeOnDelete();
            $table->string('titolo', 100);
            $table->text('messaggio');
            $table->enum('tipo', ['promemoria','allarme','errore','info'])->default('info');
            $table->boolean('letta')->default(false);
            $table->dateTime('data_invio')->useCurrent();
        });

        // Tabella sessioni Laravel
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Reset password
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('notifiche');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('assunzioni');
        Schema::dropIfExists('somministrazioni');
        Schema::dropIfExists('terapie');
        Schema::dropIfExists('dispositivi');
        Schema::dropIfExists('familiari_pazienti');
        Schema::dropIfExists('medici_pazienti');
        Schema::dropIfExists('farmaci');
        Schema::dropIfExists('pazienti');
        Schema::dropIfExists('utenti');
    }
};
