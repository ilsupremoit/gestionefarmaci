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
                {{-- Eroga subito: ora apre il selettore scomparto --}}
                <button class="cmd-btn success" onclick="apriModaleEroga()">
                    <span class="cmd-icon"><i data-lucide="pill"></i></span>
                    Eroga forzata
                </button>

                @if($dispositivo->allarme_attivo)
                    <button class="cmd-btn danger" onclick="inviaComandoAllarme('disattiva')">
                        <span class="cmd-icon"><i data-lucide="bell-off"></i></span>
                        Disattiva allarme
                    </button>
                @else
                    <button class="cmd-btn danger" onclick="apriModaleAllarme()">
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

{{-- ══════════════════════════════════════════════════════════
     WIDGET SCOMPARTI CAROSELLO
══════════════════════════════════════════════════════════ --}}

<style>
/* Scomparti widget */
.scomparti-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-top:16px; }
.scomp-card { border:2px solid var(--border); border-radius:14px; padding:14px; text-align:center; transition:all .2s; position:relative; background:rgba(255,255,255,.02); }
.scomp-card:hover { border-color:var(--accent); }
.scomp-card.pieno { border-color:rgba(16,185,129,.4); background:rgba(16,185,129,.06); }
.scomp-card.vuoto { border-color:var(--border); opacity:.7; }
.scomp-numero { font-family:'Syne',sans-serif; font-size:22px; font-weight:800; color:var(--accent); margin-bottom:6px; }
.scomp-farmaco { font-size:13px; font-weight:600; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.scomp-dose { font-size:11px; color:var(--muted); }
.scomp-stato { margin-top:8px; display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:2px 8px; border-radius:10px; }
.scomp-stato.pieno { background:rgba(16,185,129,.12); color:#6ee7b7; border:1px solid rgba(16,185,129,.25); }
.scomp-stato.vuoto { background:rgba(100,116,139,.1); color:var(--muted); border:1px solid var(--border); }
.scomp-terapia { font-size:10px; color:var(--accent2); margin-top:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.scomp-edit-btn { margin-top:10px; padding:5px 10px; background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.25); border-radius:7px; color:var(--accent); font-size:11px; cursor:pointer; font-family:inherit; transition:all .2s; width:100%; }
.scomp-edit-btn:hover { background:rgba(59,130,246,.2); }
/* Modale */
.modale-overlay { position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9998; display:none; align-items:center; justify-content:center; }
.modale-overlay.open { display:flex; }
.modale-box { background:#1a2535; border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,.4); }
.modale-title { font-family:'Syne',sans-serif; font-size:17px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
.modale-label { font-size:11px; text-transform:uppercase; letter-spacing:.6px; color:var(--muted); font-weight:700; display:block; margin-bottom:6px; }
.modale-select { width:100%; background:#0f172a; border:1px solid var(--border); color:var(--text); padding:10px 13px; border-radius:9px; font:inherit; font-size:13px; outline:none; margin-bottom:14px; }
.modale-select:focus { border-color:var(--accent); }
.modale-select option { background:#111827; }
.modale-row { display:flex; gap:10px; margin-top:6px; }
.modale-btn { flex:1; padding:11px; border-radius:9px; font-family:inherit; font-size:13px; font-weight:700; cursor:pointer; border:none; transition:all .2s; }
.modale-btn.primary { background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; }
.modale-btn.cancel  { background:rgba(255,255,255,.05); color:var(--muted); border:1px solid var(--border); }
.modale-info { font-size:11px; color:var(--muted); background:rgba(59,130,246,.06); border:1px solid rgba(59,130,246,.15); border-radius:8px; padding:8px 10px; margin-bottom:14px; }
</style>

{{-- Widget Scomparti (fuori main, in posizione fissa non serve, rimane nel flusso) --}}
<div id="sectionScomparti" style="margin-left:240px;padding:0 40px 40px;">
    <div style="background:#111827;border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:8px;font-family:'Syne',sans-serif;font-size:15px;font-weight:700;">
                <i data-lucide="layout-grid"></i> Gestione scomparti carosello (8 slot)
            </div>
            <div style="display:flex;gap:8px;">
                <button onclick="apriModaleConfigura()" style="padding:8px 14px;background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;border-radius:8px;color:#fff;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px;">
                    <i data-lucide="save"></i> Salva config &amp; invia MQTT
                </button>
            </div>
        </div>
        <div style="font-size:12px;color:var(--muted);margin-bottom:16px;">Assegna un farmaco ad ogni scomparto. Il firmware ESP32 userà queste info per sapere dove si trova ogni pillola.</div>

        {{-- Griglia 8 scomparti --}}
        <div class="scomparti-grid" id="scompartiGrid">
            @for($n = 1; $n <= 8; $n++)
            @php $s = $scomparti[$n]; @endphp
            <div class="scomp-card {{ $s['pieno'] ? 'pieno' : 'vuoto' }}" id="scomp-card-{{ $n }}">
                <div class="scomp-numero">{{ $n }}</div>
                @if($s['nome_farmaco'])
                    <div class="scomp-farmaco">{{ $s['nome_farmaco'] }}</div>
                    @if($s['dose_farmaco'])<div class="scomp-dose">{{ $s['dose_farmaco'] }}</div>@endif
                    <span class="scomp-stato {{ $s['pieno'] ? 'pieno' : 'vuoto' }}">{{ $s['pieno'] ? '● Pieno' : '○ Vuoto' }}</span>
                    @if($s['terapia_info'])<div class="scomp-terapia" title="{{ $s['terapia_info'] }}">📋 {{ Str::limit($s['terapia_info'], 30) }}</div>@endif
                @else
                    <div style="font-size:12px;color:var(--muted);margin:8px 0;">— Vuoto —</div>
                @endif
                <button class="scomp-edit-btn" onclick="apriEditor({{ $n }})">
                    <i data-lucide="pencil" style="width:11px;height:11px;"></i>
                    Modifica
                </button>
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- MODALE: Editor singolo scomparto --}}
<div class="modale-overlay" id="modaleEditor">
    <div class="modale-box">
        <div class="modale-title"><i data-lucide="package-2"></i> Configura scomparto <span id="modaleEditorNum" style="color:var(--accent);"></span></div>

        <div class="modale-info">ℹ️ Seleziona il farmaco contenuto in questo scomparto e la terapia associata. Clicca "Salva config &amp; invia MQTT" per trasmettere tutto al dispositivo.</div>

        <label class="modale-label">Farmaco</label>
        <select id="editorFarmaco" class="modale-select">
            <option value="">— Nessun farmaco (slot vuoto) —</option>
            @foreach($farmaci as $f)
            <option value="{{ $f->id }}" data-nome="{{ $f->nome }}">{{ $f->nome }}{{ $f->dose ? ' ('.$f->dose.')' : '' }}</option>
            @endforeach
        </select>

        <label class="modale-label">Terapia associata <span style="font-weight:400;color:var(--muted);font-size:10px;">(opzionale — collega ai promemoria)</span></label>
        <select id="editorTerapia" class="modale-select">
            <option value="">— Nessuna terapia —</option>
            @foreach($terapieAttive as $t)
            <option value="{{ $t->id }}" data-farmaco="{{ $t->id_farmaco }}">
                {{ $t->farmaco?->nome ?? 'Farmaco' }} —
                {{ $t->somministrazioni->map(fn($x) => substr($x->ora,0,5).' '.$x->giorno_settimana)->join(', ') }}
            </option>
            @endforeach
        </select>

        <label class="modale-label">Stato scomparto</label>
        <select id="editorPieno" class="modale-select">
            <option value="1">● Pieno (farmaco presente)</option>
            <option value="0">○ Vuoto</option>
        </select>

        <div class="modale-row">
            <button class="modale-btn primary" onclick="salvaEditor()">💾 Applica</button>
            <button class="modale-btn cancel" onclick="chiudiModale('modaleEditor')">Annulla</button>
        </div>
    </div>
</div>

{{-- MODALE: Erogazione forzata (seleziona scomparto) --}}
<div class="modale-overlay" id="modaleEroga">
    <div class="modale-box">
        <div class="modale-title"><i data-lucide="pill"></i> Erogazione forzata</div>
        <div class="modale-info">⚠️ Il dispositivo ruoterà il carosello allo scomparto selezionato ed erogherà la pillola immediatamente.</div>
        <form id="formErogazioneForzata" method="POST" action="{{ route('medico.pazienti.dispositivi.eroga_forzata', [$paziente->id, $dispositivo->id]) }}">
            @csrf
            <label class="modale-label">Scomparto da erogare</label>
            <select name="numero_scomparto" id="erogazioneScomparto" class="modale-select" required>
                @for($n = 1; $n <= 8; $n++)
                @php $s = $scomparti[$n]; @endphp
                <option value="{{ $n }}" {{ !$s['nome_farmaco'] ? 'disabled' : '' }}>
                    Scomparto {{ $n }}{{ $s['nome_farmaco'] ? ' — '.$s['nome_farmaco'] : ' (vuoto)' }}
                </option>
                @endfor
            </select>
            <div class="modale-row">
                <button type="submit" class="modale-btn primary" onclick="return confirm('Confermi erogazione forzata?')">💊 Eroga ora</button>
                <button type="button" class="modale-btn cancel" onclick="chiudiModale('modaleEroga')">Annulla</button>
            </div>
        </form>
    </div>
</div>

{{-- MODALE: Attiva allarme (seleziona scomparto) --}}
<div class="modale-overlay" id="modaleAllarme">
    <div class="modale-box">
        <div class="modale-title"><i data-lucide="bell-ring"></i> Attiva allarme</div>
        <div class="modale-info">🔔 L'ESP32 suonerà ogni 5 minuti finché il paziente non preme il bottone fisico o il sensore PIR rileva movimento.</div>
        <label class="modale-label">Scomparto/farmaco per l'allarme</label>
        <select id="allarmeScomparto" class="modale-select">
            @for($n = 1; $n <= 8; $n++)
            @php $s = $scomparti[$n]; @endphp
            <option value="{{ $n }}" {{ !$s['nome_farmaco'] ? 'disabled' : '' }}>
                Scomparto {{ $n }}{{ $s['nome_farmaco'] ? ' — '.$s['nome_farmaco'] : ' (vuoto)' }}
            </option>
            @endfor
        </select>
        <div class="modale-row">
            <button class="modale-btn primary" onclick="inviaComandoAllarme('attiva')">🔔 Attiva</button>
            <button class="modale-btn cancel" onclick="chiudiModale('modaleAllarme')">Annulla</button>
        </div>
    </div>
</div>

{{-- FORM nascosto per salvataggio scomparti --}}
<form id="formScomparti" method="POST" action="{{ route('medico.pazienti.dispositivi.scomparti', [$paziente->id, $dispositivo->id]) }}" style="display:none;">
    @csrf
    @for($n = 1; $n <= 8; $n++)
    @php $s = $scomparti[$n]; @endphp
    <input type="hidden" name="scomparti[{{ $n - 1 }}][numero_scomparto]" id="s{{ $n }}_num" value="{{ $n }}"/>
    <input type="hidden" name="scomparti[{{ $n - 1 }}][id_farmaco]"       id="s{{ $n }}_farm" value="{{ $s['id_farmaco'] ?? '' }}"/>
    <input type="hidden" name="scomparti[{{ $n - 1 }}][id_terapia]"       id="s{{ $n }}_ter" value="{{ $s['id_terapia'] ?? '' }}"/>
    <input type="hidden" name="scomparti[{{ $n - 1 }}][pieno]"            id="s{{ $n }}_pieno" value="{{ $s['pieno'] ? '1' : '0' }}"/>
    @endfor
</form>

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

    // ── Scomparti: stato locale in JS (specchio del PHP) ──────────
    const SCOMPARTI_STATE = @json($scomparti);
    // SCOMPARTI_STATE è un oggetto {1:{...},2:{...},...,8:{...}}

    let editingNum = null;

    function apriEditor(num) {
        editingNum = num;
        const s = SCOMPARTI_STATE[num];
        document.getElementById('modaleEditorNum').textContent = '#' + num;
        document.getElementById('editorFarmaco').value = s.id_farmaco || '';
        document.getElementById('editorTerapia').value = s.id_terapia || '';
        document.getElementById('editorPieno').value   = s.pieno ? '1' : '0';
        document.getElementById('modaleEditor').classList.add('open');
    }

    function salvaEditor() {
        const num     = editingNum;
        const farmId  = document.getElementById('editorFarmaco').value;
        const terId   = document.getElementById('editorTerapia').value;
        const pieno   = document.getElementById('editorPieno').value === '1';
        const farmOpt = document.getElementById('editorFarmaco').selectedOptions[0];
        const farmNome= farmOpt?.dataset?.nome || null;

        // Aggiorna stato locale
        SCOMPARTI_STATE[num].id_farmaco   = farmId || null;
        SCOMPARTI_STATE[num].nome_farmaco = farmNome;
        SCOMPARTI_STATE[num].id_terapia   = terId || null;
        SCOMPARTI_STATE[num].pieno        = pieno;

        // Aggiorna hidden form
        document.getElementById('s' + num + '_farm').value  = farmId;
        document.getElementById('s' + num + '_ter').value   = terId;
        document.getElementById('s' + num + '_pieno').value = pieno ? '1' : '0';

        // Aggiorna visualmente la card
        const card = document.getElementById('scomp-card-' + num);
        if (farmNome) {
            card.className = `scomp-card ${pieno ? 'pieno' : 'vuoto'}`;
            card.querySelector('.scomp-farmaco') && (card.querySelector('.scomp-farmaco').textContent = farmNome);
            // Ricostruisce la card
            const innerHtml = `
                <div class="scomp-numero">${num}</div>
                <div class="scomp-farmaco">${farmNome}</div>
                <span class="scomp-stato ${pieno ? 'pieno':'vuoto'}">${pieno ? '● Pieno' : '○ Vuoto'}</span>
                <button class="scomp-edit-btn" onclick="apriEditor(${num})">✏ Modifica</button>`;
            card.innerHTML = innerHtml;
        } else {
            card.className = 'scomp-card vuoto';
            card.innerHTML = `
                <div class="scomp-numero">${num}</div>
                <div style="font-size:12px;color:var(--muted);margin:8px 0;">— Vuoto —</div>
                <button class="scomp-edit-btn" onclick="apriEditor(${num})">✏ Modifica</button>`;
        }

        chiudiModale('modaleEditor');
        showToast(`Scomparto ${num} aggiornato. Clicca "Salva config" per inviare all'ESP32.`, 'success');
    }

    function apriModaleConfigura() {
        if (confirm('Salvare la configurazione scomparti e inviare il comando configura_scomparti all\'ESP32?')) {
            document.getElementById('formScomparti').submit();
        }
    }

    function apriModaleEroga() { document.getElementById('modaleEroga').classList.add('open'); }
    function apriModaleAllarme() { document.getElementById('modaleAllarme').classList.add('open'); }

    function chiudiModale(id) { document.getElementById(id).classList.remove('open'); }

    // Chiudi modale cliccando fuori
    document.querySelectorAll('.modale-overlay').forEach(el => {
        el.addEventListener('click', function(e) { if (e.target === this) chiudiModale(this.id); });
    });

    async function inviaComandoAllarme(tipo) {
        chiudiModale('modaleAllarme');
        if (tipo === 'attiva') {
            const num = parseInt(document.getElementById('allarmeScomparto').value);
            if (!num) return;
            try {
                const r = await fetch(`${BASE_URL}/allarme/attiva`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ numero_scomparto: num }),
                });
                const d = await r.json();
                d.success ? showToast('🔔 Allarme attivato scomparto ' + num, 'success')
                           : showToast('Errore: ' + (d.error ?? '?'), 'error');
            } catch(e) { showToast('Errore di rete', 'error'); }
        } else {
            // Disattiva
            try {
                const r = await fetch(`${BASE_URL}/allarme/disattiva`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                const d = await r.json();
                d.success ? showToast('🔕 Allarme disattivato', 'success')
                           : showToast('Errore: ' + (d.error ?? '?'), 'error');
            } catch(e) { showToast('Errore di rete', 'error'); }
        }
    }
</script>
</body>
</html>
