<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"/><title>PillMate Admin — Dispositivi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')</head>
<body>
@include('admin._sidebar', ['active' => 'dispositivi'])
<main class="main">
    <div class="page-header"><div><h1>📡 Dispositivi</h1><p>Tutti i dispenser PillMate nel sistema</p></div></div>
    <div class="card">
        <div class="table-wrap"><table>
                <thead><tr><th>Dispositivo</th><th>Paziente</th><th>Stato</th><th>Temperatura</th><th>Batteria</th><th>Ultimo contatto</th></tr></thead>
                <tbody>
                @forelse($dispositivi as $d)
                    @php $u = $d->paziente?->utente; @endphp
                    <tr>
                        <td><div style="font-weight:600;font-size:13px;">{{ $d->nome_dispositivo ?? 'PillMate Dispenser' }}</div><div style="font-size:11px;color:var(--muted);font-family:monospace;">{{ $d->codice_seriale }}</div></td>
                        <td style="font-size:13px;">{{ $u ? $u->cognome.' '.$u->nome : '—' }}</td>
                        <td>@if($d->allarme_attivo)<span class="dev-allarme">⚠ Allarme</span>@elseif($d->stato==='attivo')<span class="dev-attivo">● Online</span>@else<span class="dev-offline">○ {{ ucfirst($d->stato) }}</span>@endif</td>
                        <td style="font-size:13px;">{{ $d->temperatura ? $d->temperatura.'°C' : '—' }}</td>
                        <td style="font-size:13px;">{{ $d->batteria !== null ? $d->batteria.'%' : '—' }}</td>
                        <td style="font-size:12px;color:var(--muted);">{{ $d->ultima_connessione ? \Carbon\Carbon::parse($d->ultima_connessione)->diffForHumans() : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">Nessun dispositivo.</div></td></tr>
                @endforelse
                </tbody>
            </table></div>
        <div class="pag">{{ $dispositivi->links('pagination::simple-default') }}</div>
    </div>
</main>
</body>
</html>
