<?php

namespace App\Http\Controllers;

use App\Models\Paziente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        if ($search = $request->get('q')) {
            $query->whereHas('utente', function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('cognome', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
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
     * Form di creazione paziente.
     */
    public function create()
    {
        return view('medico.pazienti.create', [
            'medico' => Auth::user(),
        ]);
    }

    /**
     * Salva il nuovo paziente.
     * Il medico assegna username e password provvisoria; l'email è opzionale.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'          => ['required', 'string', 'max:50'],
            'cognome'       => ['required', 'string', 'max:50'],
            'username'      => ['required', 'string', 'max:50', 'unique:users,username'],
            'password_temp' => ['required', 'string', 'min:6'],
            'email'         => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'telefono'      => ['nullable', 'string', 'max:20'],
            'codice_fiscale'=> ['nullable', 'string', 'size:16', 'unique:pazienti,codice_fiscale'],
            'data_nascita'  => ['nullable', 'date'],
            'indirizzo'     => ['nullable', 'string', 'max:150'],
            'note_mediche'  => ['nullable', 'string'],
        ], [
            'nome.required'          => 'Il nome è obbligatorio.',
            'cognome.required'       => 'Il cognome è obbligatorio.',
            'username.required'      => 'Il nome utente è obbligatorio.',
            'username.unique'        => 'Questo nome utente è già in uso.',
            'password_temp.required' => 'La password provvisoria è obbligatoria.',
            'password_temp.min'      => 'La password deve essere di almeno 6 caratteri.',
            'email.unique'           => 'Questa email è già registrata.',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'nome'                 => $request->nome,
                'cognome'              => $request->cognome,
                'username'             => $request->username,
                'email'                => $request->email ?: null,
                'password'             => Hash::make($request->password_temp),
                'ruolo'                => 'paziente',
                'telefono'             => $request->telefono,
                'must_change_password' => true,
            ]);

            $paziente = Paziente::create([
                'id_utente'      => $user->id,
                'data_nascita'   => $request->data_nascita,
                'indirizzo'      => $request->indirizzo,
                'codice_fiscale' => $request->codice_fiscale ? strtoupper($request->codice_fiscale) : null,
                'note_mediche'   => $request->note_mediche,
            ]);

            DB::table('medici_pazienti')->insert([
                'id_medico'   => Auth::id(),
                'id_paziente' => $paziente->id,
            ]);
        });

        return redirect()->route('medico.pazienti.index')
            ->with('success', 'Paziente creato con successo. Credenziali: ' . $request->username . ' / ' . $request->password_temp);
    }
}
