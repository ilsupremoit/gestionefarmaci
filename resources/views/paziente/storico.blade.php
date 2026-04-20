<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Storico assunzioni</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('paziente._styles')
</head>
<body>
@include('paziente._sidebar', ['active' => 'storico'])
<main class="main">

    <div class="page-header">
        <div>
            <h1>📋 Storico assunzioni</h1>
            <p>Registro completo di tutte le somministrazioni</p>
        </div>
    </div>

    {{-- Statistiche globali --}}
    <div class="stats" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Totale registrate</span><div class="stat-ico blue">📊</div></div>
            <div class="stat-value c-blue">{{ $stats['totale'] }}</div>
            <div class="stat-sub">assunzioni totali</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Dosi prese</span><div class="stat-ico green">✅</div></div>
            <div class="stat-value c-green">{{ $stats['prese'] }}</div>
            <div class="stat-sub">confermate</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Aderenza globale</span><div class="stat-ico teal">📈</div></div>
            <div class="stat-value c-teal">{{ $stats['aderenza'] }}%</div>
            <div class="stat-sub">tasso di assunzione</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Forzate dal medico</span><div class="stat-ico yellow">🔓</div></div>
            <div class="stat-value c-yellow">{{ $stats['forzate'] }}</div>
            <div class="stat-sub">erogazioni forzate</div>
        </div>
    </div>

    <div class="card">
        {{-- Filtri --}}
        <form method="GET" action="{{ route('paziente.storico') }}">
            <div class="filters">
                <div class="filter-group">
                    <label>Stato</label>
                    <select name="stato">
                        <option value="">Tutti gli stati</option>
                        <option value="assunta"      {{ request('stato')=='assunta'?'selected':'' }}>✅ Presa</option>
                        <option value="erogata"      {{ request('stato')=='erogata'?'selected':'' }}>💊 Erogata</option>
                        <option value="saltata"      {{ request('stato')=='saltata'?'selected':'' }}>❌ Saltata</option>
                        <option value="non_ritirata" {{ request('stato')=='non_ritirata'?'selected':'' }}>⏸ Non ritirata</option>
                        <option value="in_attesa"    {{ request('stato')=='in_attesa'?'selected':'' }}>⏳ In attesa</option>
                        <option value="apertura_forzata" {{ request('stato')=='apertura_forzata'?'selected':'' }}>🔓 Forzata</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Dal</label>
                    <input type="date" name="dal" value="{{ request('dal') }}"/>
                </div>
                <div class="filter-group">
                    <label>Al</label>
                    <input type="date" name="al" value="{{ request('al') }}"/>
                </div>
                <div class="filter-group" style="justify-content:flex-end;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">🔍 Filtra</button>
                </div>
                @if(request()->hasAny(['stato','dal','al']))
                <div class="filter-group" style="justify-content:flex-end;">
                    <label>&nbsp;</label>
                    <a href="{{ route('paziente.storico') }}" class="btn btn-ghost">✕ Reset</a>
                </div>
                @endif
            </div>
        </form>

        {{-- Tabella --}}
        @if($assunzioni->isEmpty())
        <div class="empty-state">Nessuna assunzione trovata per i filtri selezionati.</div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Data prevista</th>
                        <th>Farmaco</th>
                        <th>Ora</th>
                        <th>Stato</th>
                        <th>Erogazione</th>
                        <th>Conferma</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($assunzioni as $a)
                @php
                    $farm = $a->somministrazione->terapia->farmaco ?? null;
                    $ora  = $a->somministrazione->ora ?? '--';
                @endphp
                <tr class="clickable-row" onclick="window.location='{{ route('paziente.assunzione.show', $a->id) }}'">
                    <td>
                        <div style="font-weight:600;">{{ \Carbon\Carbon::parse($a->data_prevista)->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:var(--muted);">{{ \Carbon\Carbon::parse($a->data_prevista)->isoFormat('dddd') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;">{{ $farm->nome ?? 'N/A' }}</div>
                        @if($farm?->dose)<div style="font-size:11px;color:var(--muted);">{{ $farm->dose }}</div>@endif
                    </td>
                    <td style="font-weight:600;font-family:'Syne',sans-serif;">{{ substr($ora,0,5) }}</td>
                    <td><span class="stato-badge stato-{{ $a->stato }}">{{ statoLbl($a->stato) }}</span></td>
                    <td style="font-size:12px;color:var(--muted);">
                        {{ $a->data_erogazione ? \Carbon\Carbon::parse($a->data_erogazione)->format('d/m H:i') : '—' }}
                        @if($a->apertura_forzata)<br><span style="color:var(--accent);font-size:10px;">🔓 Forzata medico</span>@endif
                    </td>
                    <td style="font-size:12px;color:var(--muted);">{{ $a->data_conferma ? \Carbon\Carbon::parse($a->data_conferma)->format('d/m H:i') : '—' }}</td>
                    <td style="font-size:12px;color:var(--muted);">{{ $a->note_evento ? \Illuminate\Support\Str::limit($a->note_evento,30) : '—' }}</td>
                    <td><span style="color:var(--accent);font-size:13px;">→</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginazione --}}
        <div class="pagination">
            {{ $assunzioni->links('pagination::simple-tailwind') }}
        </div>
        @endif
    </div>

</main>
</body>
</html>

@php
function statoLbl(string $s): string {
    return match($s) {
        'assunta'=>'✅ Presa','erogata'=>'💊 Erogata','saltata'=>'❌ Saltata',
        'non_ritirata'=>'⏸ Non ritirata','in_attesa'=>'⏳ In attesa',
        'ritardo'=>'⚠️ Ritardo','allarme_attivo'=>'🔔 Allarme',
        'apertura_forzata'=>'🔓 Forzata', default=>ucfirst($s),
    };
}
@endphp
