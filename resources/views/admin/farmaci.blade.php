<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"/><title>PillMate Admin — Farmaci</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')</head>
<body>
@include('admin._sidebar', ['active' => 'farmaci'])
<main class="main">
    <div class="page-header">
        <div><h1>🧪 Catalogo Farmaci</h1><p>Aggiungi e gestisci i farmaci disponibili per le terapie</p></div>
        <span style="font-size:13px;color:var(--muted);">{{ $farmaci->total() }} farmaci nel catalogo</span>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <div style="display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;">

        {{-- FORM AGGIUNGI --}}
        <div class="card" style="position:sticky;top:24px;">
            <div class="card-title">➕ Aggiungi farmaco al catalogo</div>
            <form method="POST" action="{{ route('admin.farmaci.store') }}">
                @csrf
                <div class="field">
                    <label>Nome *</label>
                    <input type="text" name="nome" required placeholder="es. Aspirina"/>
                </div>
                <div class="field">
                    <label>Principio attivo / Dose</label>
                    <input type="text" name="dose" placeholder="es. Acido acetilsalicilico 100mg"/>
                </div>
                <div class="field">
                    <label>Descrizione</label>
                    <textarea name="descrizione" placeholder="Indicazioni terapeutiche, avvertenze..."></textarea>
                </div>
                <div class="field">
                    <label>Note</label>
                    <input type="text" name="note" placeholder="es. Non assumere a stomaco vuoto"/>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">✅ Aggiungi al catalogo</button>
            </form>
        </div>

        {{-- ELENCO FARMACI --}}
        <div class="card">
            <div class="card-title">📋 Farmaci nel catalogo ({{ $farmaci->total() }})</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Nome</th><th>Dose / Principio attivo</th><th>Descrizione</th><th>Note</th><th>Terapie</th><th>Azioni</th></tr>
                    </thead>
                    <tbody>
                    @forelse($farmaci as $f)
                    <tr>
                        <td style="font-weight:700;font-size:13px;">{{ $f->nome }}</td>
                        <td style="font-size:12px;color:var(--muted);">{{ $f->dose ?? '—' }}</td>
                        <td style="font-size:12px;color:var(--muted);max-width:200px;">{{ $f->descrizione ? \Illuminate\Support\Str::limit($f->descrizione, 60) : '—' }}</td>
                        <td style="font-size:12px;color:var(--muted);">{{ $f->note ? \Illuminate\Support\Str::limit($f->note,40) : '—' }}</td>
                        <td style="text-align:center;">
                            <span style="font-family:'Syne',sans-serif;font-weight:700;">{{ $f->terapie_count ?? \App\Models\Terapia::where('id_farmaco',$f->id)->count() }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.farmaci.elimina', $f->id) }}"
                                  onsubmit="return confirm('Eliminare {{ addslashes($f->nome) }}? Tutte le terapie correlate potrebbero essere influenzate.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Elimina">🗑 Elimina</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="empty-state">Nessun farmaco nel catalogo. Aggiungine uno.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pag">{{ $farmaci->links('pagination::simple-tailwind') }}</div>
        </div>
    </div>
</main>
</body>
</html>
