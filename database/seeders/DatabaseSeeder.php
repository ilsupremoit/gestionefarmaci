<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin di default
        User::factory()->admin()->create([
            'nome'     => 'Admin',
            'cognome'  => 'PillMate',
            'email'    => 'admin@pillmate.it',
            'password' => Hash::make('password'),
        ]);

        // Medico di test
        User::factory()->medico()->create([
            'nome'     => 'Mario',
            'cognome'  => 'Rossi',
            'email'    => 'medico@pillmate.it',
            'password' => Hash::make('password'),
        ]);
    }
}
