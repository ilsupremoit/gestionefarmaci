<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>PillMate — Dispositivo {{ $dispositivo->codice_seriale }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/medico/dispositivo-show.css')
</head>
<body>
@php
    $utente   = $paziente->utente;
    $isOnline = $dispositivo->stato === 'attivo' && $dispositivo->ultimo_payload_at
                && \Carbon\Carbon::parse($dispositivo->ultimo_payload_at)->diffInMinutes(now()) <= 5;
@endphp

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="pill"></i>
        </div>
        <span class="brand-name">PillMate</span>
    </div>

    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>
        Dashboard
    </a>

    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">
        <span class="ico"><i data-lucide="users"></i></span>
        Pazienti
    </a>

    <a class="nav-item" href="{{ route('medico.notifiche') }}">
        <span class="ico"><i data-lucide="bell"></i></span>
        Notifiche
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->nome }} {{ auth()->user()->cognome }}</div>
                <div class="user-role">
                    <i data-lucide="user-round"></i>
                    Medico
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i data-lucide="log-out"></i>
                Esci
            </button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="breadcrumb">
        <a href="{{ route('medico.pazienti.index') }}">Pazienti</a>
        <span>›</span>
        <a href="{{ route('medico.pazienti.show', $paziente->id) }}">{{ $utente->cognome }} {{ $utente->nome }}</a>
        <span>›</span>
        <span style="color:var(--text);">{{ $dispositivo->codice_seriale }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i data-lucide="circle-alert"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="page-header">
        <div style="display:flex;align-items:center;gap:16px;">
            <div class="header-device-icon">
                <i data-lucide="pill"></i>
            </div>
            <div>
                <h1>{{ $dispositivo->nome_dispositivo ?? 'PillMate Dispenser' }}</h1>
                <div style="font-size:13px;color:var(--muted);margin-top:3px;">
                    S/N: <code>{{ $dispositivo->codice_seriale }}</code> &nbsp;·&nbsp;
                    Paziente: {{ $utente->cognome }} {{ $utente->nome }}
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            @if($dispositivo->allarme_attivo)
                <span class="device-status-badge status-allarme">
                    <i data-lucide="bell-ring"></i>
                    ALLARME ATTIVO
                </span>
            @elseif($isOnline)
                <span class="device-status-badge status-attivo">
                    <span class="status-dot dot-green"></span>
                    Online
                </span>
            @else
                <span class="device-status-badge status-offline">
                    <span class="status-dot dot-gray"></span>
                    {{ ucfirst($dispositivo->stato) }}
                </span>
            @endif

            <a href="{{ route('medico.pazienti.show', $paziente->id) }}" class="btn-secondary">
                <i data-lucide="arrow-left"></i>
                Torna al paziente
            </a>
        </div>
    </div>

    <div class="grid4">
        <div class="metric-card">
            <div class="metric-icon"><i data-lucide="thermometer"></i></div>
            <div class="metric-val" id="val-temp" style="color:{{ ($dispositivo->temperatura ?? 0) > 35 ? 'var(--red)' : 'var(--text)' }}">
                {{ $dispositivo->temperatura !== null ? $dispositivo->temperatura.'°C' : '—' }}
            </div>
            <div class="metric-lbl">Temperatura</div>
        </div>

        <div class="metric-card">
            <div class="metric-icon"><i data-lucide="droplets"></i></div>
            <div class="metric-val" id="val-hum">{{ $dispositivo->umidita !== null ? $dispositivo->umidita.'%' : '—' }}</div>
            <div class="metric-lbl">Umidità</div>
        </div>

        <div class="metric-card">
            <div class="metric-icon"><i data-lucide="wifi"></i></div>
            <div class="metric-val" id="val-rssi" style="color:{{ ($dispositivo->wifi_rssi ?? 0) < -80 ? 'var(--red)' : (($dispositivo->wifi_rssi ?? 0) < -60 ? 'var(--yellow)' : 'var(--green)') }}">
                {{ $dispositivo->wifi_rssi !== null ? $dispositivo->wifi_rssi.' dBm' : '—' }}
            </div>
            <div class="metric-lbl">WiFi RSSI</div>
        </div>

        <div class="metric-card">
            <div class="metric-icon"><i data-lucide="package-2"></i></div>
            <div class="metric-val" id="val-scomp">{{ $dispositivo->scomparto_attuale !== null ? 'N° '.$dispositivo->scomparto_attuale : '—' }}</div>
            <div class="metric-lbl">Scomparto</div>
        </div>
    </div>

    <div style="font-size:11px;color:var(--muted);margin-bottom:20px;text-align:right;">
        Ultimo aggiornamento: <span id="last-update">{{ $dispositivo->ultimo_payload_at ? \Carbon\Carbon::parse($dispositivo->ultimo_payload_at)->diffForHumans() : 'Mai' }}</span>
        &nbsp;·&nbsp; Sveglia impostata: <span id="val-sveglia"><strong>{{ $dispositivo->sveglia_impostata ? substr($dispositivo->sveglia_impostata,0,5) : 'N/D' }}</strong></span>
        <span id="live-dot" style="margin-left:8px;">{{ $isOnline ? '●' : '○' }}</span>
    </div>

    <div class="grid2">
        <div class="card">
            <div class="card-title">
                <i data-lucide="settings-2"></i>
                Comandi dispositivo
            </div>

            <div class="cmd-grid">
                <button class="cmd-btn success" onclick="inviaComando('eroga_ora')">
                    <span class="cmd-icon"><i data-lucide="pill"></i></span>
                    Eroga subito
                </button>

                @if($dispositivo->allarme_attivo)
                    <button class="cmd-btn danger" onclick="inviaComando('disattiva_allarme')">
                        <span class="cmd-icon"><i data-lucide="bell-off"></i></span>
                        Disattiva allarme
                    </button>
                @else
                    <button class="cmd-btn danger" onclick="inviaComando('attiva_allarme')">
                        <span class="cmd-icon"><i data-lucide="bell-ring"></i></span>
                        Attiva allarme
                    </button>
                @endif

                <button class="cmd-btn" onclick="inviaComando('reset')">
                    <span class="cmd-icon"><i data-lucide="rotate-ccw"></i></span>
                    Reset dispositivo
                </button>
            </div>

            <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="alarm-clock"></i>
                    Imposta sveglia
                </div>

                <div class="inline-form">
                    <input type="time" id="input-sveglia" value="{{ $dispositivo->sveglia_impostata ? substr($dispositivo->sveglia_impostata,0,5) : '08:00' }}"/>
                    <button class="cmd-btn success" style="flex-direction:row;padding:9px 14px;" onclick="impostaSveglia()">
                        <i data-lucide="check"></i>
                        Imposta
                    </button>
                </div>
            </div>

            <div style="margin-top:18px;padding:12px;background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:8px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="radio-tower"></i>
                    Topic MQTT
                </div>

                <div style="font-size:12px;line-height:1.8;color:var(--muted);">
                    <div>Subscribe: <code>pillmate/{{ $dispositivo->codice_seriale }}/+</code></div>
                    <div>Stato: <code>pillmate/{{ $dispositivo->codice_seriale }}/stato</code></div>
                    <div>Telemetria: <code>pillmate/{{ $dispositivo->codice_seriale }}/telemetria</code></div>
                    <div>Eventi: <code>pillmate/{{ $dispositivo->codice_seriale }}/eventi</code></div>
                    <div>Comandi: <code>pillmate/{{ $dispositivo->codice_seriale }}/comandi</code></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">
                <i data-lucide="clipboard"></i>
                Log eventi recenti
                <span id="badge-nuovi" style="display:none;background:var(--red);color:#fff;font-size:10px;padding:1px 7px;border-radius:10px;font-weight:700;margin-left:auto;">LIVE</span>
            </div>

            <div id="log-eventi" style="max-height:380px;overflow-y:auto;">
                @forelse($eventi as $e)
                    <div class="evento-row {{ $e->severita }}">
                        <div class="evento-icon {{ $e->severita }}">
                            @if($e->severita === 'critico')
                                <i data-lucide="circle-alert"></i>
                            @elseif($e->severita === 'warning')
                                <i data-lucide="triangle-alert"></i>
                            @else
                                <i data-lucide="info"></i>
                            @endif
                        </div>

                        <div>
                            <div class="evento-azione">{{ str_replace('_', ' ', $e->azione) }}</div>
                            @if($e->metodo_attivazione)
                                <span class="evento-metodo">{{ $e->metodo_attivazione }}</span>
                            @endif
                            @if($e->messaggio)
                                <div style="color:var(--muted);font-size:11px;margin-top:3px;">{{ $e->messaggio }}</div>
                            @endif
                            <div class="evento-ts">{{ \Carbon\Carbon::parse($e->created_at)->format('d/m H:i:s') }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;color:var(--muted);padding:20px;font-size:13px;">Nessun evento registrato.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if($storicoTelemetria->count())
        <div class="card">
            <div class="card-title">
                <i data-lucide="chart-column"></i>
                Storico telemetria (ultimi {{ $storicoTelemetria->count() }} punti)
            </div>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                    <tr>
                        <th>Timestamp</th><th>Temp °C</th><th>Umidità %</th><th>RSSI dBm</th><th>Scomparto</th><th>Sveglia</th><th>Allarme</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($storicoTelemetria->reverse() as $t)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d/m H:i:s') }}</td>
                            <td style="color:{{ $t->temperatura > 35 ? 'var(--red)' : 'var(--text)' }}">{{ $t->temperatura ?? '—' }}</td>
                            <td>{{ $t->umidita ?? '—' }}</td>
                            <td style="color:{{ $t->wifi_rssi < -80 ? 'var(--red)' : ($t->wifi_rssi < -60 ? 'var(--yellow)' : 'var(--green)') }}">{{ $t->wifi_rssi ?? '—' }}</td>
                            <td>{{ $t->scomparto_attuale ?? '—' }}</td>
                            <td>{{ $t->sveglia_impostata ? substr($t->sveglia_impostata,0,5) : '—' }}</td>
                            <td>{{ $t->allarme_attivo ? 'Sì' : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</main>

<div class="toast" id="toast"></div>

<script>
    const PAZIENTE_ID   = {{ $paziente->id }};
    const DISPOSITIVO_ID = {{ $dispositivo->id }};
    const CSRF          = document.querySelector('meta[name="csrf-token"]').content;
    const BASE_URL      = `/medico/pazienti/${PAZIENTE_ID}/dispositivi/${DISPOSITIVO_ID}`;

    async function inviaComando(azione, extra = {}) {
        if (['eroga_ora','attiva_allarme'].includes(azione)) {
            if (!confirm(`Confermi il comando: ${azione.replace(/_/g,' ')}?`)) return;
        }
        try {
            const r = await fetch(`${BASE_URL}/comando`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ azione, payload_extra: extra }),
            });
            const d = await r.json();
            if (d.success) {
                showToast(`Comando "${azione}" inviato`, 'success');
                aggiungiEventoLog(azione, 'medico_web', 'warning');
            } else {
                showToast('Errore: ' + (d.error ?? 'Sconosciuto'), 'error');
            }
        } catch(e) {
            showToast('Errore di rete', 'error');
        }
    }

    function impostaSveglia() {
        const ora = document.getElementById('input-sveglia').value;
        if (!ora) return;
        inviaComando('imposta_sveglia', { ora });
        document.getElementById('val-sveglia').innerHTML = `<strong>${ora}</strong>`;
    }

    function iconSvg(name) {
        const icons = {
            critico: '<i data-lucide="circle-alert"></i>',
            warning: '<i data-lucide="triangle-alert"></i>',
            info: '<i data-lucide="info"></i>'
        };
        return icons[name] || icons.info;
    }

    function aggiungiEventoLog(azione, metodo, severita) {
        const log = document.getElementById('log-eventi');
        const now = new Date().toLocaleString('it-IT');
        const el  = document.createElement('div');
        el.className = `evento-row ${severita}`;
        el.innerHTML = `
        <div class="evento-icon ${severita}">
            ${iconSvg(severita)}
        </div>
        <div>
            <div class="evento-azione">${azione.replace(/_/g,' ')}</div>
            <span class="evento-metodo">${metodo}</span>
            <div class="evento-ts">${now} (nuovo)</div>
        </div>`;

        log.prepend(el);
        document.getElementById('badge-nuovi').style.display = 'inline';

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function pollTelemetria() {
        fetch(`/medico/pazienti/${PAZIENTE_ID}/dispositivi/${DISPOSITIVO_ID}/telemetria-live`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
            .then(r => r.ok ? r.json() : null)
            .then(d => {
                if (!d) return;
                if (d.temperatura !== undefined) document.getElementById('val-temp').textContent = d.temperatura + '°C';
                if (d.umidita !== undefined) document.getElementById('val-hum').textContent = d.umidita + '%';
                if (d.wifi_rssi !== undefined) document.getElementById('val-rssi').textContent = d.wifi_rssi + ' dBm';
                if (d.scomparto_attuale !== undefined) document.getElementById('val-scomp').textContent = 'N° ' + d.scomparto_attuale;
                if (d.sveglia_impostata) document.getElementById('val-sveglia').innerHTML = `<strong>${d.sveglia_impostata.substring(0,5)}</strong>`;
                document.getElementById('live-dot').textContent = d.online ? '●' : '○';
                document.getElementById('last-update').textContent = 'Adesso';
            })
            .catch(() => {});
    }

    setInterval(pollTelemetria, 15000);

    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    }
</script>
</body>
</html>
