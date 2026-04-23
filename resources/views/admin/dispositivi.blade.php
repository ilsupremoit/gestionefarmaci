<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <title>PillMate Admin — Dispositivi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')
</head>
<body>
@include('admin._sidebar', ['active' => 'dispositivi'])

<main class="main">
    <div class="page-header">
        <div>
            <h1>Dispositivi</h1>
            <p>Tutti i dispenser PillMate nel sistema</p>
        </div>
        <span style="font-size:13px;color:var(--muted);">Totale: <strong>{{ $dispositivi->total() }}</strong></span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-title">
            <i data-lucide="radio"></i>
            Elenco dispositivi
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Dispositivo</th>
                    <th>Paziente</th>
                    <th>Stato</th>
                    <th>Temperatura</th>
                    <th>Umidità</th>
                    <th>WiFi RSSI</th>
                    <th>Scomparto</th>
                    <th>Allarme</th>
                    <th>Ultimo contatto</th>
                </tr>
                </thead>
                <tbody>
                @forelse($dispositivi as $d)
                    @php
                        $u = $d->paziente?->utente;
                        $minutiFa = $d->ultimo_payload_at ? \Carbon\Carbon::parse($d->ultimo_payload_at)->diffInMinutes(now()) : null;
                        $isOnline = $d->stato === 'attivo' && $minutiFa !== null && $minutiFa <= 5;
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#ede9fe,#dbeafe);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
                                    <i data-lucide="pill"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:13px;">{{ $d->nome_dispositivo ?? 'PillMate Dispenser' }}</div>
                                    <div style="font-size:11px;color:var(--muted);font-family:monospace;">{{ $d->codice_seriale }}</div>
                                </div>
                            </div>
                        </td>

                        <td style="font-size:13px;">
                            @if($u)
                                <div style="font-weight:600;">{{ $u->cognome }} {{ $u->nome }}</div>
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>

                        <td>
                            @if($d->allarme_attivo)
                                <span class="dev-allarme">Allarme</span>
                            @elseif($isOnline)
                                <span class="dev-attivo">Online</span>
                            @else
                                <span class="dev-offline">{{ ucfirst($d->stato) }}</span>
                            @endif
                        </td>

                        <td style="font-size:13px;font-weight:{{ ($d->temperatura ?? 0) > 35 ? '700' : '400' }};color:{{ ($d->temperatura ?? 0) > 35 ? 'var(--red)' : 'var(--text)' }};">
                            {{ $d->temperatura !== null ? $d->temperatura.'°C' : '—' }}
                        </td>

                        <td style="font-size:13px;">
                            {{ $d->umidita !== null ? $d->umidita.'%' : '—' }}
                        </td>

                        <td style="font-size:13px;color:{{ ($d->wifi_rssi ?? 0) < -80 ? 'var(--red)' : (($d->wifi_rssi ?? 0) < -60 ? 'var(--yellow)' : 'var(--green)') }};">
                            {{ $d->wifi_rssi !== null ? $d->wifi_rssi.' dBm' : '—' }}
                        </td>

                        <td style="font-size:13px;">
                            {{ $d->scomparto_attuale !== null ? 'N° '.$d->scomparto_attuale : '—' }}
                        </td>

                        <td>
                            @if($d->allarme_attivo)
                                <span style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;">
                                Sì
                            </span>
                            @else
                                <span style="color:var(--muted);font-size:12px;">No</span>
                            @endif
                        </td>

                        <td style="font-size:12px;color:var(--muted);">
                            @if($d->ultimo_payload_at)
                                {{ \Carbon\Carbon::parse($d->ultimo_payload_at)->diffForHumans() }}
                            @elseif($d->ultima_connessione)
                                {{ \Carbon\Carbon::parse($d->ultima_connessione)->diffForHumans() }}
                            @else
                                Mai connesso
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">Nessun dispositivo registrato nel sistema.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pag">{{ $dispositivi->links('pagination::simple-tailwind') }}</div>
    </div>
</main>
</body>
</html>
