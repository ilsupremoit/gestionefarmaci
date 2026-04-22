<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"/><title>PillMate Admin — Farmaci</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')</head>
<body>
@include('admin._sidebar', ['active' => 'farmaci'])
<main class="main">
    <div class="page-header"><div><h1>💊 Farmaci</h1><p>Gestisci il catalogo farmaci del sistema</p></div></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;align-items:start;">
        <div class="card">
            <div class="card-title">➕ Aggiungi farmaco</div>
            <form method="POST" action="{{ route('admin.farmaci.store') }}">
                @csrf
                <div class="field"><label>Nome *</label><input type="text" name="nome" required placeholder="es. Aspirina"/></div>
                <div class="field"><label>Dose</label><input type="text" name="dose" placeholder="es. 100mg"/></div>
                <div class="field"><label>Descrizione</label><textarea name="descrizione" placeholder="Breve descrizione..."></textarea></div>
                <button type="submit" class="btn btn-primary" style="width:100%;">✅ Aggiungi</button>
            </form>
        </div>
        <div class="card">
            <div class="card-title">📋 Farmaci ({{ $farmaci->total() }})</div>
            <div class="table-wrap"><table>
                    <thead><tr><th>Nome</th><th>Dose</th><th>Descrizione</th><th>Azioni</th></tr></thead>
                    <tbody>
                    @forelse($farmaci as $f)
                        <tr>
                            <td style="font-weight:600;">{{ $f->nome }}</td>
                            <td style="font-size:12px;color:var(--muted);">{{ $f->dose ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--muted);">{{ \Illuminate\Support\Str::limit($f->descrizione, 60) ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.farmaci.elimina', $f->id) }}" onsubmit="return confirm('Eliminare {{ addslashes($f->nome) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">🗑</button>
                                </form>
                            </td>
                        </tr>
                    @empty<tr><td colspan="4"><div class="empty-state">Nessun farmaco.</div></td></tr>@endforelse
                    </tbody>
                </table></div>
            <div class="pag">{{ $farmaci->links('pagination::simple-default') }}</div>
        </div>
    </div>
</main>
</body>
</html>
