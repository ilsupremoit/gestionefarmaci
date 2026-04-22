<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate Admin — Pazienti</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')
</head>
<body>
@include('admin._sidebar', ['active' => 'pazienti'])
<main class="main">
    <div class="page-header">
        <div>
            <h1>🧑‍🦯 Pazienti</h1>
            <p>Tutti i pazienti registrati nel sistema</p>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    {{-- Ricerca --}}
    <form method="GET" style="display:flex;gap:10px;margin-bottom:20px;">
        <input type="text" name="q" value="{{ $q }}" placeholder="🔍 Cerca per nome o cognome..."
               style="flex:1;background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font:inherit;font-size:14px;color:var(--text);outline:none;"
               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'"/>
        <button type="submit" class="btn btn-ghost">Cerca</button>
        @if($q) <a href="{{ route('admin.pazienti') }}" class="btn btn-ghost">✕ Reset</a> @endif
    </form>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Paziente</th>
                        <th>Contatti</th>
                        <th>Nascita / CF</th>
                        <th>Terapie</th>
                        <th>Dispositivi</th>
                        <th>Medici</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pazienti as $p)
                @php $u = $p->utente; @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $u->cognome }} {{ $u->nome }}</div>
                        <div style="font-size:11px;color:var(--muted);">@{{ $u->username }}</div>
                    </td>
                    <td style="font-size:12px;color:var(--muted);">
                        @if($u->email) <div>{{ $u->email }}</div> @endif
                        @if($u->telefono) <div>{{ $u->telefono }}</div> @endif
                    </td>
                    <td style="font-size:12px;">
                        @if($p->data_nascita) {{ $p->data_nascita->format('d/m/Y') }}<br> @endif
                        @if($p->codice_fiscale) <span style="font-family:monospace;">{{ $p->codice_fiscale }}</span> @endif
                    </td>
                    <td style="text-align:center;">
                        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;">{{ $p->terapie->where('attiva',true)->count() }}</span>
                        <div style="font-size:10px;color:var(--muted);">attive</div>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;">{{ $p->dispositivi->count() }}</span>
                    </td>
                    <td style="font-size:12px;color:var(--muted);">
                        @foreach($p->medici ?? [] as $m)
                            <div>Dr. {{ $m->cognome }}</div>
                        @endforeach
                        @if(!isset($p->medici) || $p->medici->count() === 0) — @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.pazienti.show', $p->id) }}" class="btn btn-sm btn-ghost">👁 Vedi</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state">Nessun paziente trovato.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pag">{{ $pazienti->links('pagination::simple-tailwind') }}</div>
    </div>
</main>
</body>
</html>
