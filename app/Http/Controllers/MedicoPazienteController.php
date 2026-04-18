<?php

namespace App\Http\Controllers;

use App\Models\Paziente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MedicoPazienteController extends Controller
{
    /**
     * Elenco pazienti seguiti dal medico autenticato.
     */
    public function index(Request $request)
    {
        $medico = Auth::user();

        $query = $medico->pazientiSeguiti()
            ->with([
                'utente',
                'terapie' => fn($q) => $q->where('attiva', true),
                'dispositivi',
            ]);

        // Ricerca per nome / cognome / email
        if ($search = $request->get('q')) {
            $query->whereHas('utente', function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('cognome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $pazienti = $query->orderBy(
            User::select('cognome')
                ->whereColumn('users.id', 'pazienti.id_utente')
                ->limit(1)
        )->paginate(12)->withQueryString();

        return view('medico.pazienti.index', compact('medico', 'pazienti'));
    }

    /**
     * Form di creazione paziente + account utente.
     */
    public function create()
    {
        return view('medico.pazienti.create', [
            'medico' => Auth::user(),
        ]);
    }

    /**
     * Salva il nuovo paziente e lo collega al medico.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'         => ['required', 'string', 'max:50'],
            'cognome'      => ['required', 'string', 'max:50'],
            'email'        => ['required', 'email', 'max:100', 'unique:users,email'],
            'telefono'     => ['nullable', 'string', 'max:20'],
            'data_nascita' => ['required', 'date'],
            'indirizzo'    => ['nullable', 'string', 'max:150'],
            'note_mediche' => ['nullable', 'string'],
        ], [
            'nome.required'         => 'Il nome e\' obbligatorio.',
            'cognome.required'      => 'Il cognome e\' obbligatorio.',
            'email.required'        => 'L\'email e\' obbligatoria.',
            'email.unique'          => 'Questa email e\' gia\' registrata.',
            'data_nascita.required' => 'La data di nascita e\' obbligatoria.',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'nome'                => $request->nome,
                'cognome'             => $request->cognome,
                'email'               => $request->email,
                'password'            => Hash::make(\Illuminate\Support\Str::random(16)),
                'ruolo'               => 'paziente',
                'telefono'            => $request->telefono,
                'must_change_password'=> true,
            ]);

            $paziente = Paziente::create([
                'id_utente'    => $user->id,
                'data_nascita' => $request->data_nascita,
                'indirizzo'    => $request->indirizzo,
                'note_mediche' => $request->note_mediche,
            ]);

            // Collega il medico al paziente
            DB::table('medici_pazienti')->insert([
                'id_medico'  => Auth::id(),
                'id_paziente'=> $paziente->id,
            ]);
        });

        return redirect()->route('medico.pazienti.index')
            ->with('success', 'Paziente aggiunto con successo.');
    }
}
