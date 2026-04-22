<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate Admin — Terapie</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')
</head>
<body>
@include('admin._sidebar', ['active' => 'terapie'])
<main class="main">
    <div class="page-header">
        <div>
            <h1>💊 Tutte le terapie</h1>
            <p>Panoramica di tutte le terapie nel sistema con medico e paziente correlati</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <span style="font-size:13px;color:var(--muted);">Totale: <strong>{{ $terapie->total() }}</strong></span>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Filtri --}}
    <form method="GET" action="{{ route('admin.terapie') }}" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" value="{{ $q }}" placeholder="🔍 Cerca farmaco o paziente..."
               style="flex:1;min-width:200px;background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font:inherit;font-size:14px;color:var(--text);outline:none;"
               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'"/>
        <select name="stato" style="background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font:inherit;font-size:14px;color:var(--text);" onchange="this.form.submit()">
            <option value="tutte"   {{ $stato==='tutte' ? 'selected':'' }}>Tutte</option>
            <option value="attive"  {{ $stato==='attive' ? 'selected':'' }}>✅ Solo attive</option>
            <option value="concluse"{{ $stato==='concluse' ? 'selected':'' }}>⏹ Solo concluse</option>
        </select>
        <button type="submit" class="btn btn-ghost">Cerca</button>
        @if($q || $stato !== 'tutte')
            <a href="{{ route('admin.terapie') }}" class="btn btn-ghost">✕ Reset</a>
        @endif
    </form>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Farmaco</th>
                        <th>Paziente</th>
                        <th>Medico prescrivente</th>
                        <th>Periodo</th>
                        <th>Dose / Freq.</th>
                        <th>Orari</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($terapie as $t)
                @php
                    $paz    = $t->paziente;
                    $utente = $paz?->utente;
                    $med    = $t->medico;
                    $farm   = $t->farmaco;
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:13px;">{{ $farm?->nome ?? '—' }}</div>
                        @if($farm?->dose)<div style="font-size:11px;color:var(--muted);">{{ $farm->dose }}</div>@endif
                    </td>
                    <td>
                        @if($utente)
                        <div style="font-weight:600;font-size:13px;">{{ $utente->cognome }} {{ $utente->nome }}</div>
                        <div style="font-size:11px;color:var(--muted);">@{{ $utente->username }}</div>
                        @else <span style="color:var(--muted);">—</span> @endif
                    </td>
                    <td>
                        @if($med)
                        <div style="font-size:13px;">Dr. {{ $med->cognome }} {{ $med->nome }}</div>
                        @if($med->email)<div style="font-size:11px;color:var(--muted);">{{ $med->email }}</div>@endif
                        @else <span style="color:var(--muted);">—</span> @endif
                    </td>
                    <td style="font-size:12px;">
                        <div>{{ $t->data_inizio->format('d/m/Y') }}</div>
                        @if($t->data_fine)
                            <div style="color:var(--muted);">→ {{ $t->data_fine->format('d/m/Y') }}</div>
                        @else
                            <div style="color:var(--muted);">→ Indefinita</div>
                        @endif
                    </td>
                    <td style="font-size:12px;">
                        <div>💊 {{ $t->quantita }} pill./dose</div>
                        @if($t->frequenza)<div style="color:var(--muted);">⏱ {{ $t->frequenza }}</div>@endif
                    </td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($t->somministrazioni as $s)
                                <span style="font-size:10px;background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.2);color:#3b82f6;padding:1px 6px;border-radius:8px;">
                                    {{ $s->giorno_settimana }} {{ substr($s->ora,0,5) }}
                                </span>
                            @endforeach
                            @if($t->somministrazioni->isEmpty()) <span style="color:var(--muted);font-size:11px;">—</span> @endif
                        </div>
                    </td>
                    <td>
                        @if($t->attiva)
                            <span class="ruolo-badge" style="background:#f0fdf4;color:#15803d;border:1px solid #86efac;">✅ Attiva</span>
                        @else
                            <span class="ruolo-badge" style="background:#f8fafc;color:var(--muted);border:1px solid var(--border);">⏹ Conclusa</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state">Nessuna terapia trovata.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pag">{{ $terapie->links('pagination::simple-tailwind') }}</div>
    </div>
</main>
</body>
</html>
