<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('cognome');
            $table->boolean('must_change_password')->default(true)->after('password');
        });

        $users = DB::table('users')->select('id', 'nome', 'cognome')->get();

        foreach ($users as $user) {
            $base = Str::lower(trim(($user->nome ?? 'user') . '.' . ($user->cognome ?? $user->id)));
            $base = preg_replace('/[^a-z0-9._-]/', '', $base);

            if ($base === '' || $base === '.') {
                $base = 'user' . $user->id;
            }

            $username = $base;
            $counter = 1;

            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base . $counter;
                $counter++;
            }

            DB::table('users')->where('id', $user->id)->update([
                'username' => $username,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'must_change_password']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 100)->nullable(false)->change();
        });
    }
};
