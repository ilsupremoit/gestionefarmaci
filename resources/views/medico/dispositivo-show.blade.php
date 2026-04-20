{{-- resources/views/medico/dispositivo-show.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>PillMate — Dispositivo {{ $dispositivo->codice_seriale }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root{--bg:#0b0f1a;--surface:#111827;--border:#1f2d45;--accent:#3b82f6;--accent2:#06b6d4;--green:#10b981;--red:#ef4444;--yellow:#f59e0b;--text:#e2e8f0;--muted:#64748b;}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
        .sidebar{width:240px;flex-shrink:0;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:28px 0;position:fixed;top:0;left:0;height:100vh}
        .brand{padding:0 24px 28px;border-bottom:1px solid var(--border);margin-bottom:20px}
        .brand-name{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;background:linear-gradient(135deg,#fff,var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .nav-item{display:flex;align-items:center;gap:12px;padding:10px 24px;font-size:14px;color:var(--muted);text-decoration:none;transition:all .2s}
        .nav-item:hover{color:var(--text);background:rgba(255,255,255,.04)}
        .nav-item.active{color:var(--accent);background:rgba(59,130,246,.08);border-right:2px solid var(--accent)}
        .sidebar-footer{margin-top:auto;padding:20px 24px 0;border-top:1px solid var(--border)}
        .user-info{display:flex;align-items:center;gap:10px;margin-bottom:14px}
        .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700}
        .user-name{font-size:13px;font-weight:600}.user-role{font-size:11px;color:var(--muted)}
        .btn-logout{width:100%;padding:9px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;color:#fca5a5;font-size:13px;cursor:pointer;font-family:inherit}
        .main{margin-left:240px;flex:1;padding:36px 40px}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:20px}
        .breadcrumb a{color:var(--muted);text-decoration:none}.breadcrumb a:hover{color:var(--accent)}
        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
        .page-header h1{font-family:'Syne',sans-serif;font-size:24px;font-weight:700}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:20px}
        .card-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
        .metric-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:center}
        .metric-val{font-family:'Syne',sans-serif;font-size:26px;font-weight:700;margin:6px 0 2px}
        .metric-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px}
        .metric-icon{font-size:20px;margin-bottom:4px}
        .status-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:6px}
        .dot-green{background:var(--green)}.dot-gray{background:var(--muted)}.dot-red{background:var(--red)}.dot-yellow{background:var(--yellow)}
        .device-status-badge{display:inline-flex;align-items:center;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:600}
        .status-attivo{background:rgba(16,185,129,.12);color:#6ee7b7;border:1px solid rgba(16,185,129,.25)}
        .status-offline{background:rgba(100,116,139,.1);color:var(--muted);border:1px solid var(--border)}
        .status-allarme{background:rgba(239,68,68,.2);color:#fca5a5;border:1px solid rgba(239,68,68,.4);animation:pulse 1.5s infinite}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}

        /* Comandi MQTT */
        .cmd-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
        .cmd-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px;border-radius:12px;border:1px solid var(--border);background:rgba(255,255,255,.03);cursor:pointer;font-family:inherit;color:var(--text);transition:all .2s;font-size:13px;font-weight:600}
        .cmd-btn:hover{border-color:var(--accent);background:rgba(59,130,246,.08)}
        .cmd-btn .cmd-icon{font-size:24px}
        .cmd-btn.danger{border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.05)}
        .cmd-btn.danger:hover{background:rgba(239,68,68,.12)}
        .cmd-btn.success{border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.05)}
        .cmd-btn.success:hover{background:rgba(16,185,129,.12)}

        /* Log eventi */
        .evento-row{padding:10px 14px;border-radius:8px;margin-bottom:6px;display:flex;align-items:flex-start;gap:12px;font-size:12px}
        .evento-row.info{background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.15)}
        .evento-row.warning{background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.15)}
        .evento-row.critico{background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.15)}
        .evento-azione{font-weight:700;font-size:12px;font-family:'Syne',sans-serif}
        .evento-ts{font-size:10px;color:var(--muted);margin-top:2px}
        .evento-metodo{font-size:10px;background:rgba(255,255,255,.05);padding:1px 6px;border-radius:6px;color:var(--muted)}

        /* Sveglia form */
        .inline-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:10px}
        .inline-form input{background:#0f172a;border:1px solid var(--border);color:var(--text);padding:8px 12px;border-radius:8px;font:inherit;font-size:13px;outline:none}
        .inline-form input:focus{border-color:var(--accent)}

        /* Alert */
        .alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px}
        .alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#6ee7b7}
        .alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}

        /* Toast */
        .toast{position:fixed;bottom:24px;right:24px;padding:14px 20px;border-radius:12px;font-size:13px;font-weight:500;z-index:9999;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none}
        .toast.show{opacity:1;transform:translateY(0)}
        .toast.success{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#6ee7b7}
        .toast.error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}

        table{width:100%;border-collapse:collapse;font-size:12px}
        th{text-align:left;padding:8px 10px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);border-bottom:1px solid var(--border)}
        td{padding:9px 10px;border-bottom:1px solid rgba(31,45,69,.4);vertical-align:top}
        tr:last-child td{border-bottom:none}
        code{background:rgba(255,255,255,.06);padding:1px 5px;border-radius:4px;font-size:11px;color:var(--accent2)}
        @media(max-width:768px){.sidebar{display:none}.main{margin-left:0;padding:20px 16px}.grid2{grid-template-columns:1fr}.grid4{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
@php
$utente   = $paziente->utente;
$isOnline = $dispositivo->stato === 'attivo' && $dispositivo->ultimo_payload_at
            && \Carbon\Carbon::parse($dispositivo->ultimo_payload_at)->diffInMinutes(now()) <= 5;
@endphp

<aside class="sidebar">
    <div class="brand"><span class="brand-name">PillMate</span></div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">🏠 Dashboard</a>
    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">👥 Pazienti</a>
    <a class="nav-item" href="{{ route('medico.notifiche') }}">🔔 Notifiche</a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nome,0,1)) }}</div>
            <div><div class="user-name">{{ auth()->user()->nome }} {{ auth()->user()->cognome }}</div><div class="user-role">👨‍⚕️ Medico</div></div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn-logout">🚪 Esci</button></form>
    </div>
</aside>

<main class="main">
    <div class="breadcrumb">
        <a href="{{ route('medico.pazienti.index') }}">Pazienti</a> <span>›</span>
        <a href="{{ route('medico.pazienti.show', $paziente->id) }}">{{ $utente->cognome }} {{ $utente->nome }}</a> <span>›</span>
        <span style="color:var(--text);">{{ $dispositivo->codice_seriale }}</span>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <div class="page-header">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#1e3a5f,#0e4d6e);display:flex;align-items:center;justify-content:center;font-size:26px;">💊</div>
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
                <span class="device-status-badge status-allarme">🔔 ALLARME ATTIVO</span>
            @elseif($isOnline)
                <span class="device-status-badge status-attivo"><span class="status-dot dot-green"></span>Online</span>
            @else
                <span class="device-status-badge status-offline"><span class="status-dot dot-gray"></span>{{ ucfirst($dispositivo->stato) }}</span>
            @endif
            <a href="{{ route('medico.pazienti.show', $paziente->id) }}" style="padding:9px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:9px;color:var(--muted);font-size:13px;text-decoration:none;">← Torna al paziente</a>
        </div>
    </div>

    {{-- METRICHE LIVE --}}
    <div class="grid4">
        <div class="metric-card">
            <div class="metric-icon">🌡️</div>
            <div class="metric-val" id="val-temp" style="color:{{ ($dispositivo->temperatura ?? 0) > 35 ? 'var(--red)' : 'var(--text)' }}">
                {{ $dispositivo->temperatura !== null ? $dispositivo->temperatura.'°C' : '—' }}
            </div>
            <div class="metric-lbl">Temperatura</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">💧</div>
            <div class="metric-val" id="val-hum">{{ $dispositivo->umidita !== null ? $dispositivo->umidita.'%' : '—' }}</div>
            <div class="metric-lbl">Umidità</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">📶</div>
            <div class="metric-val" id="val-rssi" style="color:{{ ($dispositivo->wifi_rssi ?? 0) < -80 ? 'var(--red)' : (($dispositivo->wifi_rssi ?? 0) < -60 ? 'var(--yellow)' : 'var(--green)') }}">
                {{ $dispositivo->wifi_rssi !== null ? $dispositivo->wifi_rssi.' dBm' : '—' }}
            </div>
            <div class="metric-lbl">WiFi RSSI</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">💊</div>
            <div class="metric-val" id="val-scomp">{{ $dispositivo->scomparto_attuale !== null ? 'N° '.$dispositivo->scomparto_attuale : '—' }}</div>
            <div class="metric-lbl">Scomparto</div>
        </div>
    </div>

    <div style="font-size:11px;color:var(--muted);margin-bottom:20px;text-align:right;">
        ⏱ Ultimo aggiornamento: <span id="last-update">{{ $dispositivo->ultimo_payload_at ? \Carbon\Carbon::parse($dispositivo->ultimo_payload_at)->diffForHumans() : 'Mai' }}</span>
        &nbsp;·&nbsp; Sveglia impostata: <span id="val-sveglia"><strong>{{ $dispositivo->sveglia_impostata ? substr($dispositivo->sveglia_impostata,0,5) : 'N/D' }}</strong></span>
        <span id="live-dot" style="margin-left:8px;">{{ $isOnline ? '🟢' : '⚫' }}</span>
    </div>

    <div class="grid2">

        {{-- COMANDI MQTT --}}
        <div class="card">
            <div class="card-title">⚙️ Comandi dispositivo</div>
            <div class="cmd-grid">

                <button class="cmd-btn success" onclick="inviaComando('eroga_ora')">
                    <span class="cmd-icon">💊</span>
                    Eroga subito
                </button>

                @if($dispositivo->allarme_attivo)
                <button class="cmd-btn danger" onclick="inviaComando('disattiva_allarme')">
                    <span class="cmd-icon">🔕</span>
                    Disattiva allarme
                </button>
                @else
                <button class="cmd-btn danger" onclick="inviaComando('attiva_allarme')">
                    <span class="cmd-icon">🔔</span>
                    Attiva allarme
                </button>
                @endif

                <button class="cmd-btn" onclick="inviaComando('reset')">
                    <span class="cmd-icon">🔄</span>
                    Reset dispositivo
                </button>

            </div>

            {{-- Imposta sveglia --}}
            <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;margin-bottom:8px;">⏰ Imposta sveglia</div>
                <div class="inline-form">
                    <input type="time" id="input-sveglia" value="{{ $dispositivo->sveglia_impostata ? substr($dispositivo->sveglia_impostata,0,5) : '08:00' }}"/>
                    <button class="cmd-btn success" style="flex-direction:row;padding:9px 14px;" onclick="impostaSveglia()">
                        Imposta
                    </button>
                </div>
            </div>

            {{-- Info MQTT --}}
            <div style="margin-top:18px;padding:12px;background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:8px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:8px;">📡 Topic MQTT</div>
                <div style="font-size:12px;line-height:1.8;color:var(--muted);">
                    <div>Subscribe: <code>pillmate/{{ $dispositivo->codice_seriale }}/+</code></div>
                    <div>Stato: <code>pillmate/{{ $dispositivo->codice_seriale }}/stato</code></div>
                    <div>Telemetria: <code>pillmate/{{ $dispositivo->codice_seriale }}/telemetria</code></div>
                    <div>Eventi: <code>pillmate/{{ $dispositivo->codice_seriale }}/eventi</code></div>
                    <div>Comandi: <code>pillmate/{{ $dispositivo->codice_seriale }}/comandi</code></div>
                </div>
            </div>
        </div>

        {{-- LOG EVENTI --}}
        <div class="card">
            <div class="card-title">📋 Log eventi recenti <span id="badge-nuovi" style="display:none;background:var(--red);color:#fff;font-size:10px;padding:1px 7px;border-radius:10px;font-weight:700;margin-left:auto;">LIVE</span></div>
            <div id="log-eventi" style="max-height:380px;overflow-y:auto;">
                @forelse($eventi as $e)
                <div class="evento-row {{ $e->severita }}">
                    <div style="font-size:18px;flex-shrink:0;">{{ $e->severita === 'critico' ? '🔴' : ($e->severita === 'warning' ? '⚠️' : '🔵') }}</div>
                    <div>
                        <div class="evento-azione">{{ str_replace('_', ' ', $e->azione) }}</div>
                        @if($e->metodo_attivazione)<span class="evento-metodo">{{ $e->metodo_attivazione }}</span>@endif
                        @if($e->messaggio)<div style="color:var(--muted);font-size:11px;margin-top:3px;">{{ $e->messaggio }}</div>@endif
                        <div class="evento-ts">{{ \Carbon\Carbon::parse($e->created_at)->format('d/m H:i:s') }}</div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;color:var(--muted);padding:20px;font-size:13px;">Nessun evento registrato.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- STORICO TELEMETRIA --}}
    @if($storicoTelemetria->count())
    <div class="card">
        <div class="card-title">📈 Storico telemetria (ultimi {{ $storicoTelemetria->count() }} punti)</div>
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
                    <td>{{ $t->allarme_attivo ? '🔔 Sì' : '—' }}</td>
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
            showToast(`✅ Comando "${azione}" inviato`, 'success');
            aggiungiEventoLog(azione, 'medico_web', 'warning');
        } else {
            showToast('❌ Errore: ' + (d.error ?? 'Sconosciuto'), 'error');
        }
    } catch(e) {
        showToast('❌ Errore di rete', 'error');
    }
}

function impostaSveglia() {
    const ora = document.getElementById('input-sveglia').value;
    if (!ora) return;
    inviaComando('imposta_sveglia', { ora });
    document.getElementById('val-sveglia').innerHTML = `<strong>${ora}</strong>`;
}

function aggiungiEventoLog(azione, metodo, severita) {
    const log = document.getElementById('log-eventi');
    const now = new Date().toLocaleString('it-IT');
    const ico = severita === 'critico' ? '🔴' : severita === 'warning' ? '⚠️' : '🔵';
    const el  = document.createElement('div');
    el.className = `evento-row ${severita}`;
    el.innerHTML = `<div style="font-size:18px;flex-shrink:0">${ico}</div>
        <div>
            <div class="evento-azione">${azione.replace(/_/g,' ')}</div>
            <span class="evento-metodo">${metodo}</span>
            <div class="evento-ts">${now} (nuovo)</div>
        </div>`;
    log.prepend(el);
    document.getElementById('badge-nuovi').style.display = 'inline';
}

// Polling ogni 15s per aggiornare metriche in tempo reale
function pollTelemetria() {
    fetch(`/medico/pazienti/${PAZIENTE_ID}/dispositivi/${DISPOSITIVO_ID}/telemetria-live`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.ok ? r.json() : null)
    .then(d => {
        if (!d) return;
        if (d.temperatura !== undefined) document.getElementById('val-temp').textContent = d.temperatura + '°C';
        if (d.umidita     !== undefined) document.getElementById('val-hum').textContent  = d.umidita + '%';
        if (d.wifi_rssi   !== undefined) document.getElementById('val-rssi').textContent = d.wifi_rssi + ' dBm';
        if (d.scomparto_attuale !== undefined) document.getElementById('val-scomp').textContent = 'N° ' + d.scomparto_attuale;
        if (d.sveglia_impostata) document.getElementById('val-sveglia').innerHTML = `<strong>${d.sveglia_impostata.substring(0,5)}</strong>`;
        document.getElementById('live-dot').textContent = d.online ? '🟢' : '⚫';
        document.getElementById('last-update').textContent = 'Adesso';
    })
    .catch(() => {});
}

setInterval(pollTelemetria, 15000);

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = `toast ${type} show`;
    setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>
