<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Farmaco;
use App\Models\Paziente;
use App\Models\Terapia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────

    private function stats(): array
    {
        return [
            'utenti'      => User::count(),
            'medici'      => User::where('ruolo', 'medico')->count(),
            'pazienti'    => User::where('ruolo', 'paziente')->count(),
            'dispositivi' => Dispositivo::count(),
            'farmaci'     => Farmaco::count(),
            'notifiche'   => DB::table('notifiche')->where('letta', false)->count(),
        ];
    }

    // ── DASHBOARD ─────────────────────────────────────────────────

    public function dashboard()
    {
        $admin              = Auth::user();
        $stats              = $this->stats();
        $ultimiUtenti       = User::orderByDesc('created_at')->take(8)->get();
        $dispositiviRecenti = Dispositivo::with('paziente.utente')->orderByDesc('id')->take(6)->get();

        return view('admin.dashboard', compact('admin', 'stats', 'ultimiUtenti', 'dispositiviRecenti'));
    }

    // ── UTENTI ────────────────────────────────────────────────────

    public function utenti(Request $request)
    {
        $admin = Auth::user();
        $q     = $request->get('q');
        $ruolo = $request->get('ruolo');

        $query = User::query();
        if ($q)     $query->where(fn($x) => $x->where('nome','like',"%$q%")->orWhere('cognome','like',"%$q%")->orWhere('email','like',"%$q%")->orWhere('username','like',"%$q%"));
        if ($ruolo) $query->where('ruolo', $ruolo);

        $utenti = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.utenti', compact('admin', 'utenti', 'q', 'ruolo'));
    }

    public function creaUtente(Request $request)
    {
        $admin = Auth::user();
        return view('admin.utente-create', compact('admin'));
    }

    public function salvaUtente(Request $request)
    {
        $request->validate([
            'nome'           => ['required', 'string', 'max:50'],
            'cognome'        => ['required', 'string', 'max:50'],
            'ruolo'          => ['required', 'in:medico,paziente,familiare,admin'],
            'username'       => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'          => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:6'],
            'telefono'       => ['nullable', 'string', 'max:20'],
            'data_nascita'   => ['nullable', 'date'],
            'indirizzo'      => ['nullable', 'string', 'max:150'],
            'codice_fiscale' => ['nullable', 'string', 'size:16'],
            'note_mediche'   => ['nullable', 'string'],
        ], [
            'username.unique'        => 'Questo nome utente è già in uso.',
            'email.unique'           => 'Questa email è già registrata.',
            'codice_fiscale.size'    => 'Il codice fiscale deve essere di 16 caratteri.',
            'password.min'           => 'La password deve essere di almeno 6 caratteri.',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'nome'                 => $request->nome,
                'cognome'              => $request->cognome,
                'username'             => $request->username,
                'email'                => $request->email ?: null,
                'password'             => Hash::make($request->password),
                'ruolo'                => $request->ruolo,
                'telefono'             => $request->telefono,
                'must_change_password' => $request->boolean('must_change_password', false),
            ]);

            if ($request->ruolo === 'paziente') {
                Paziente::create([
                    'id_utente'      => $user->id,
                    'data_nascita'   => $request->data_nascita,
                    'indirizzo'      => $request->indirizzo,
                    'codice_fiscale' => $request->codice_fiscale ? strtoupper($request->codice_fiscale) : null,
                    'note_mediche'   => $request->note_mediche,
                ]);
            }
        });

        return redirect()->route('admin.utenti')
            ->with('success', "✅ Utente '{$request->username}' creato con successo.");
    }

    public function eliminaUtente(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', '❌ Non puoi eliminare il tuo stesso account.');
        }
        $nome = $user->nome . ' ' . $user->cognome;
        $user->delete();
        return back()->with('success', "✅ Utente '{$nome}' eliminato.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['nuova_password' => ['required', 'string', 'min:6']]);
        $user->update([
            'password'             => Hash::make($request->nuova_password),
            'must_change_password' => true,
        ]);
        return back()->with('success', "✅ Password di '{$user->nome} {$user->cognome}' resettata.");
    }

    // ── PAZIENTI ─────────────────────────────────────────────────

    public function pazienti(Request $request)
    {
        $admin = Auth::user();
        $q     = $request->get('q');

        $query = Paziente::with(['utente', 'terapie', 'dispositivi', 'medici']);
        if ($q) {
            $query->whereHas('utente', fn($x) =>
                $x->where('nome','like',"%$q%")->orWhere('cognome','like',"%$q%")->orWhere('username','like',"%$q%")
            );
        }

        $pazienti = $query->orderByDesc('id')->paginate(20)->withQueryString();
        return view('admin.pazienti', compact('admin', 'pazienti', 'q'));
    }

    public function pazienteShow(Paziente $paziente)
    {
        $admin = Auth::user();
        $paziente->load(['utente', 'dispositivi', 'terapie.farmaco', 'terapie.somministrazioni', 'medici']);

        $assunzioni = Assunzione::whereHas('somministrazione.terapia', fn($q) =>
                $q->where('id_paziente', $paziente->id))
            ->with('somministrazione.terapia.farmaco')
            ->where('data_prevista', '>=', now()->subDays(7))
            ->orderByDesc('data_prevista')
            ->get();

        return view('admin.paziente-show', compact('admin', 'paziente', 'assunzioni'));
    }

    // ── TERAPIE (tutte, con info medico) ─────────────────────────

    public function terapie(Request $request)
    {
        $admin = Auth::user();
        $q     = $request->get('q');
        $stato = $request->get('stato', 'tutte');

        $query = Terapia::with(['farmaco', 'paziente.utente', 'medico'])
            ->orderByDesc('attiva')
            ->orderByDesc('data_inizio');

        if ($stato === 'attive')   $query->where('attiva', true);
        if ($stato === 'concluse') $query->where('attiva', false);
        if ($q) {
            $query->where(function ($x) use ($q) {
                $x->whereHas('farmaco', fn($f) => $f->where('nome','like',"%$q%"))
                  ->orWhereHas('paziente.utente', fn($u) =>
                      $u->where('nome','like',"%$q%")->orWhere('cognome','like',"%$q%")
                  );
            });
        }

        $terapie = $query->paginate(25)->withQueryString();
        return view('admin.terapie', compact('admin', 'terapie', 'q', 'stato'));
    }

    // ── DISPOSITIVI ───────────────────────────────────────────────

    public function dispositivi(Request $request)
    {
        $admin       = Auth::user();
        $dispositivi = Dispositivo::with('paziente.utente')
            ->orderByRaw("FIELD(stato,'attivo','offline','manutenzione','errore')")
            ->paginate(20)->withQueryString();
        return view('admin.dispositivi', compact('admin', 'dispositivi'));
    }

    // ── FARMACI ───────────────────────────────────────────────────

    public function farmaci(Request $request)
    {
        $admin   = Auth::user();
        $farmaci = Farmaco::orderBy('nome')->paginate(30)->withQueryString();
        return view('admin.farmaci', compact('admin', 'farmaci'));
    }

    public function salvaFarmaco(Request $request)
    {
        $request->validate([
            'nome'        => ['required', 'string', 'max:100'],
            'dose'        => ['nullable', 'string', 'max:50'],
            'descrizione' => ['nullable', 'string'],
            'note'        => ['nullable', 'string'],
        ]);
        Farmaco::create($request->only('nome','dose','descrizione','note'));
        return back()->with('success', '✅ Farmaco aggiunto.');
    }

    public function eliminaFarmaco(Farmaco $farmaco)
    {
        $nome = $farmaco->nome;
        $farmaco->delete();
        return back()->with('success', "✅ Farmaco '{$nome}' eliminato.");
    }

    // ── NOTIFICHE / MESSAGGI ──────────────────────────────────────

    public function notifiche()
    {
        $admin   = Auth::user();

        // Destinatari possibili: tutti tranne se stessi
        $medici    = User::where('ruolo', 'medico')->orderBy('cognome')->get();
        $adminList = User::where('ruolo', 'admin')->where('id', '!=', $admin->id)->orderBy('cognome')->get();
        $pazienti  = Paziente::with('utente')->get();

        // Messaggi inviati dall'admin
        $inviati = DB::table('notifiche')
            ->where('id_mittente', $admin->id)
            ->orderByDesc('data_invio')
            ->paginate(15, ['*'], 'inviati');

        // Messaggi ricevuti dall'admin (dai medici / pazienti)
        $ricevuti = DB::table('notifiche')
            ->where('id_utente', $admin->id)
            ->orderByDesc('data_invio')
            ->paginate(15, ['*'], 'ricevuti');

        // Segna ricevuti come letti
        DB::table('notifiche')
            ->where('id_utente', $admin->id)
            ->where('letta', false)
            ->update(['letta' => true, 'letto_at' => now()]);

        return view('admin.notifiche', compact('admin', 'medici', 'adminList', 'inviati', 'ricevuti'));
    }

    public function inviaNotifica(Request $request)
    {
        $request->validate([
            'id_utente' => ['required', 'exists:users,id'],
            'titolo'    => ['required', 'string', 'max:100'],
            'messaggio' => ['required', 'string'],
            'tipo'      => ['required', 'in:info,promemoria,allarme,messaggio'],
        ]);

        $dest = User::findOrFail($request->id_utente);

        // Admin non può mandare messaggi a se stesso
        if ($dest->id === Auth::id()) {
            return back()->with('error', '❌ Non puoi inviare un messaggio a te stesso.');
        }

        DB::table('notifiche')->insert([
            'id_utente'   => $request->id_utente,
            'id_mittente' => Auth::id(),
            'titolo'      => $request->titolo,
            'messaggio'   => $request->messaggio,
            'tipo'        => $request->tipo,
            'letta'       => false,
            'data_invio'  => now(),
        ]);

        return back()->with('success', "✅ Messaggio inviato a {$dest->nome} {$dest->cognome}.");
    }
}
