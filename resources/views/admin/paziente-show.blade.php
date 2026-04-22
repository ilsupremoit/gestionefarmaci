<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"/><title>PillMate Admin — Paziente</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')</head>
<body>
@include('admin._sidebar', ['active' => 'pazienti'])
<main class="main">
    @php $u = $paziente->utente; @endphp
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <a href="{{ route('admin.pazienti') }}" class="btn btn-ghost btn-sm">← Pazienti</a>
        <h1 style="font-family:'Syne',sans-serif;font-size:22px;font-weight:700;">{{ $u->cognome }} {{ $u->nome }}</h1>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div class="card">
            <div class="card-title">👤 Dati anagrafici</div>
            <div style="display:grid;gap:10px;font-size:13px;">
                <div><span style="color:var(--muted);">Email:</span> {{ $u->email ?? '—' }}</div>
                <div><span style="color:var(--muted);">Tel:</span> {{ $u->telefono ?? '—' }}</div>
                <div><span style="color:var(--muted);">Username:</span> {{ $u->username }}</div>
                <div><span style="color:var(--muted);">Nascita:</span> {{ $paziente->data_nascita ? $paziente->data_nascita->format('d/m/Y') : '—' }}</div>
                <div><span style="color:var(--muted);">CF:</span> {{ $paziente->codice_fiscale ?? '—' }}</div>
                <div><span style="color:var(--muted);">Indirizzo:</span> {{ $paziente->indirizzo ?? '—' }}</div>
                @if($paziente->note_mediche)<div><span style="color:var(--muted);">Note:</span> {{ $paziente->note_mediche }}</div>@endif
            </div>
        </div>
        <div class="card">
            <div class="card-title">📡 Dispositivi</div>
            @forelse($paziente->dispositivi as $d)
                <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:20px;">📡</span>
                    <div style="flex:1;"><div style="font-weight:600;font-size:13px;">{{ $d->nome_dispositivo ?? $d->codice_seriale }}</div><div style="font-size:11px;color:var(--muted);">{{ $d->codice_seriale }}</div></div>
                    @if($d->allarme_attivo)<span class="dev-allarme">⚠ Allarme</span>
                    @elseif($d->stato==='attivo')<span class="dev-attivo">● Online</span>
                    @else<span class="dev-offline">○ {{ ucfirst($d->stato) }}</span>@endif
                </div>
            @empty<div class="empty-state">Nessun dispositivo.</div>@endforelse
        </div>
    </div>
    <div class="card" style="margin-bottom:20px;">
        <div class="card-title">💊 Terapie attive</div>
        @forelse($paziente->terapie->where('attiva',true) as $t)
            <div style="padding:10px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;">
                <div style="font-weight:700;font-size:14px;">{{ $t->farmaco->nome ?? '—' }}</div>
                <div style="font-size:12px;color:var(--muted);">Dal {{ $t->data_inizio->format('d/m/Y') }} · {{ $t->quantita }} pillola/e</div>
            </div>
        @empty<div class="empty-state">Nessuna terapia attiva.</div>@endforelse
    </div>
    <div class="card">
        <div class="card-title">📋 Assunzioni (ultimi 7 giorni)</div>
        @forelse($assunzioni as $a)
            @php $farm = $a->somministrazione->terapia->farmaco ?? null; @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                <div style="flex:1;font-size:13px;font-weight:600;">{{ $farm->nome ?? '—' }}</div>
                <div style="font-size:12px;color:var(--muted);">{{ \Carbon\Carbon::parse($a->data_prevista)->format('d/m H:i') }}</div>
                <span class="ruolo-badge" style="background:{{ in_array($a->stato,['assunta','erogata']) ? '#f0fdf4' : (in_array($a->stato,['saltata','non_ritirata']) ? '#fef2f2' : '#fff7ed') }};color:{{ in_array($a->stato,['assunta','erogata']) ? '#15803d' : (in_array($a->stato,['saltata','non_ritirata']) ? '#b91c1c' : '#c2410c') }}">{{ ucfirst($a->stato) }}</span>
            </div>
        @empty<div class="empty-state">Nessuna assunzione registrata.</div>@endforelse
    </div>
</main>
</body>
</html>
