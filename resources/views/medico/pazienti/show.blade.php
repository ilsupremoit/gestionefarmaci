{{-- resources/views/medico/pazienti/show.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>PillMate — {{ $paziente->utente->cognome }} {{ $paziente->utente->nome }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --bg:      #0b0f1a;
            --surface: #111827;
            --border:  #1f2d45;
            --accent:  #3b82f6;
            --accent2: #06b6d4;
            --green:   #10b981;
            --red:     #ef4444;
            --yellow:  #f59e0b;
            --text:    #e2e8f0;
            --muted:   #64748b;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar { width: 240px; flex-shrink: 0; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 28px 0; position: fixed; top: 0; left: 0; height: 100vh; }
        .brand { padding: 0 24px 28px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
        .brand-name { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; background: linear-gradient(135deg, #fff, var(--accent2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); padding: 0 24px; margin-bottom: 8px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 24px; font-size: 14px; color: var(--muted); text-decoration: none; transition: all .2s; }
        .nav-item:hover { color: var(--text); background: rgba(255,255,255,.04); }
        .nav-item.active { color: var(--accent); background: rgba(59,130,246,.08); border-right: 2px solid var(--accent); }
        .sidebar-footer { margin-top: auto; padding: 20px 24px 0; border-top: 1px solid var(--border); }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent2)); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; }
        .user-name { font-size: 13px; font-weight: 600; }
        .user-role { font-size: 11px; color: var(--muted); }
        .btn-logout { width: 100%; padding: 9px; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); border-radius: 8px; color: #fca5a5; font-size: 13px; cursor: pointer; transition: all .2s; font-family: inherit; }
        .btn-logout:hover { background: rgba(239,68,68,.2); }

        /* Main */
        .main { margin-left: 240px; flex: 1; padding: 36px 40px; }

        /* Breadcrumb */
        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); margin-bottom: 24px; }
        .breadcrumb a { color: var(--muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--accent); }
        .breadcrumb .sep { opacity: .4; }
        .breadcrumb .current { color: var(--text); }

        /* Patient hero */
        .patient-hero {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 24px 28px;
            display: flex; align-items: center; gap: 24px;
            margin-bottom: 24px; flex-wrap: wrap;
        }
        .hero-avatar {
            width: 70px; height: 70px; border-radius: 18px; flex-shrink: 0;
            background: linear-gradient(135deg, #1e3a5f, #0e4d6e);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; color: var(--accent2);
        }
        .hero-info { flex: 1; }
        .hero-name { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .hero-meta { font-size: 13px; color: var(--muted); display: flex; flex-wrap: wrap; gap: 16px; }
        .hero-meta span { display: flex; align-items: center; gap: 5px; }
        .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

        /* IoT Buttons */
        .btn-iot {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 18px; border-radius: 10px; font-size: 13px; font-weight: 700;
            font-family: inherit; cursor: pointer; border: none; transition: all .2s;
            text-decoration: none;
        }
        .btn-eroga { background: linear-gradient(135deg, #1d4ed8, var(--accent)); color: #fff; box-shadow: 0 4px 14px rgba(59,130,246,.35); }
        .btn-eroga:hover { opacity: .9; transform: translateY(-1px); }
        .btn-alarm-on  { background: linear-gradient(135deg, #991b1b, var(--red)); color: #fff; box-shadow: 0 4px 14px rgba(239,68,68,.35); }
        .btn-alarm-on:hover  { opacity: .9; transform: translateY(-1px); }
        .btn-alarm-off { background: rgba(239,68,68,.12); color: #fca5a5; border: 1px solid rgba(239,68,68,.25); }
        .btn-alarm-off:hover { background: rgba(239,68,68,.2); }
        .btn-back { background: rgba(255,255,255,.05); color: var(--muted); border: 1px solid var(--border); padding: 11px 16px; border-radius: 10px; font-size: 13px; font-family: inherit; cursor: pointer; text-decoration: none; transition: all .2s; display: inline-flex; align-items: center; gap: 6px; }
        .btn-back:hover { color: var(--text); border-color: rgba(255,255,255,.2); }

        /* Dispositivo stato */
        .device-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .device-badge.attivo  { background: rgba(16,185,129,.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,.25); }
        .device-badge.offline { background: rgba(100,116,139,.12); color: var(--muted); border: 1px solid var(--border); }
        .device-badge.alarm   { background: rgba(239,68,68,.12); color: #fca5a5; border: 1px solid rgba(239,68,68,.25); animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{ opacity:1; } 50%{ opacity:.6; } }
        .dot { width: 7px; height: 7px; border-radius: 50%; }
        .dot.green  { background: var(--green); }
        .dot.gray   { background: var(--muted); }
        .dot.red    { background: var(--red); }

        /* Alert */
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; }
        .alert-success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); color: #6ee7b7; }
        .alert-error   { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }

        /* Grid 2 col */
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }

        /* Cards */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 22px; }
        .card-title { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }

        /* Stat pills */
        .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
        .stat-pill { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; text-align: center; }
        .stat-pill .val { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; }
        .stat-pill .lbl { font-size: 11px; color: var(--muted); margin-top: 3px; text-transform: uppercase; letter-spacing: .5px; }
        .stat-pill.blue .val   { color: var(--accent); }
        .stat-pill.green .val  { color: var(--green); }
        .stat-pill.red .val    { color: var(--red); }
        .stat-pill.yellow .val { color: var(--yellow); }

        /* Assunzioni table */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); border-bottom: 1px solid var(--border); font-weight: 600; }
        td { padding: 11px 12px; border-bottom: 1px solid rgba(31,45,69,.5); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,.02); }

        /* Stato badge */
        .stato-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
            cursor: pointer; transition: opacity .2s;
        }
        .stato-badge:hover { opacity: .8; }
        .stato-assunta    { background: rgba(16,185,129,.15); color: #6ee7b7; border: 1px solid rgba(16,185,129,.25); }
        .stato-erogata    { background: rgba(6,182,212,.15); color: #67e8f9; border: 1px solid rgba(6,182,212,.25); }
        .stato-saltata    { background: rgba(239,68,68,.15); color: #fca5a5; border: 1px solid rgba(239,68,68,.25); }
        .stato-non_ritirata { background: rgba(239,68,68,.1); color: #fca5a5; border: 1px solid rgba(239,68,68,.2); }
        .stato-in_attesa  { background: rgba(245,158,11,.12); color: #fcd34d; border: 1px solid rgba(245,158,11,.2); }
        .stato-ritardo    { background: rgba(245,158,11,.12); color: #fcd34d; border: 1px solid rgba(245,158,11,.2); }
        .stato-allarme_attivo { background: rgba(239,68,68,.2); color: #fca5a5; border: 1px solid rgba(239,68,68,.4); animation: pulse 1.5s infinite; }
        .stato-apertura_forzata { background: rgba(59,130,246,.15); color: #93c5fd; border: 1px solid rgba(59,130,246,.25); }

        /* Confermata da badge */
        .conf-badge { font-size: 10px; color: var(--muted); background: rgba(255,255,255,.04); padding: 2px 7px; border-radius: 10px; }

        /* Terapia list */
        .terapia-item { padding: 14px; border: 1px solid var(--border); border-radius: 10px; margin-bottom: 10px; transition: border-color .2s; }
        .terapia-item:hover { border-color: rgba(59,130,246,.3); }
        .terapia-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        .terapia-name { font-weight: 700; font-size: 14px; }
        .terapia-meta { font-size: 12px; color: var(--muted); }
        .badge-attiva { background: rgba(16,185,129,.12); color: #6ee7b7; border: 1px solid rgba(16,185,129,.25); padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
        .badge-inattiva { background: rgba(100,116,139,.1); color: var(--muted); border: 1px solid var(--border); padding: 2px 8px; border-radius: 10px; font-size: 11px; }

        /* Form aggiunta terapia */
        .form-add { display: none; margin-top: 16px; padding: 18px; border: 1px solid var(--border); border-radius: 12px; background: rgba(255,255,255,.02); }
        .form-add.open { display: block; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .form-row.full { grid-template-columns: 1fr; }
        label { font-size: 12px; color: var(--muted); display: block; margin-bottom: 5px; }
        input, select, textarea {
            width: 100%; background: var(--surface); border: 1px solid var(--border);
            border-radius: 8px; padding: 9px 12px;
            color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 13px;
            outline: none; transition: border-color .2s;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--accent); }
        .giorni-check { display: flex; flex-wrap: wrap; gap: 8px; }
        .giorni-check label { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text); cursor: pointer; margin: 0; }
        .giorni-check input[type=checkbox] { width: auto; }
        .btn-submit { padding: 10px 20px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border: none; border-radius: 8px; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; }
        .btn-add-terapia { padding: 8px 14px; background: rgba(59,130,246,.1); border: 1px solid rgba(59,130,246,.25); border-radius: 8px; color: var(--accent); font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all .2s; }
        .btn-add-terapia:hover { background: rgba(59,130,246,.2); }

        /* Toast */
        .toast {
            position: fixed; bottom: 24px; right: 24px;
            padding: 14px 20px; border-radius: 12px; font-size: 13px; font-weight: 500;
            z-index: 9999; opacity: 0; transform: translateY(10px);
            transition: all .3s; pointer-events: none;
        }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.success { background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.3); color: #6ee7b7; }
        .toast.error   { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }

        @media (max-width: 1024px) { .grid2 { grid-template-columns: 1fr; } .stat-row { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 768px)  { .sidebar { display:none; } .main { margin-left:0; padding:20px 16px; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

@php
$utente     = $paziente->utente;
$dispositivo = $paziente->dispositivi->where('stato','attivo')->first()
              ?? $paziente->dispositivi->first();
$hasAlarm   = $paziente->dispositivi->where('allarme_attivo', true)->isNotEmpty();
$eta        = $paziente->data_nascita ? \Carbon\Carbon::parse($paziente->data_nascita)->age : null;
@endphp

<aside class="sidebar">
    <div class="brand"><span class="brand-name">PillMate</span></div>
    <div class="nav-label">Menu</div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">🏠 Dashboard</a>
    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">👥 I miei pazienti</a>
    <a class="nav-item" href="#">🔔 Notifiche</a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->nome }} {{ auth()->user()->cognome }}</div>
                <div class="user-role">👨‍⚕️ Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>

<main class="main">

    {{-- Breadcrumb --}}
    <div class="breadcrumb">
        <a href="{{ route('medico.dashboard') }}">Dashboard</a>
        <span class="sep">›</span>
        <a href="{{ route('medico.pazienti.index') }}">Pazienti</a>
        <span class="sep">›</span>
        <span class="current">{{ $utente->cognome }} {{ $utente->nome }}</span>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- Hero paziente --}}
    <div class="patient-hero">
        <div class="hero-avatar">
            {{ strtoupper(substr($utente->nome,0,1)) }}{{ strtoupper(substr($utente->cognome,0,1)) }}
        </div>
        <div class="hero-info">
            <div class="hero-name">{{ $utente->cognome }} {{ $utente->nome }}</div>
            <div class="hero-meta">
                @if($eta)<span>🎂 {{ $eta }} anni</span>@endif
                @if($paziente->data_nascita)<span>📅 {{ $paziente->data_nascita->format('d/m/Y') }}</span>@endif
                @if($utente->email)<span>✉️ {{ $utente->email }}</span>@endif
                @if($utente->telefono)<span>📞 {{ $utente->telefono }}</span>@endif
                @if($paziente->indirizzo)<span>📍 {{ $paziente->indirizzo }}</span>@endif
            </div>
            @if($paziente->note_mediche)
            <div style="margin-top:8px; font-size:12px; color:var(--muted); background:rgba(255,255,255,.03); padding:8px 12px; border-radius:8px; border:1px solid var(--border);">
                📝 {{ $paziente->note_mediche }}
            </div>
            @endif
        </div>

        {{-- Stato dispositivo + bottoni IoT --}}
        <div class="hero-actions">
            @if($dispositivo)
                @if($hasAlarm)
                    <span class="device-badge alarm"><span class="dot red"></span>ALLARME ATTIVO</span>
                @elseif($dispositivo->stato === 'attivo')
                    <span class="device-badge attivo"><span class="dot green"></span>Dispositivo online</span>
                @else
                    <span class="device-badge offline"><span class="dot gray"></span>{{ ucfirst($dispositivo->stato) }}</span>
                @endif

                {{-- Bottone EROGAZIONE FORZATA --}}
                <form method="POST" action="{{ route('medico.pazienti.eroga', $paziente->id) }}" style="display:inline;" onsubmit="return confirm('Confermi l\'erogazione forzata della pillola?')">
                    @csrf
                    <button type="submit" class="btn-iot btn-eroga">
                        💊 Eroga ora
                    </button>
                </form>

                {{-- Bottone ALLARME --}}
                @if($hasAlarm)
                <form method="POST" action="{{ route('medico.pazienti.allarme', $paziente->id) }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="attiva" value="0"/>
                    <button type="submit" class="btn-iot btn-alarm-off">🔕 Disattiva allarme</button>
                </form>
                @else
                <form method="POST" action="{{ route('medico.pazienti.allarme', $paziente->id) }}" style="display:inline;" onsubmit="return confirm('Attivare l\'allarme sul dispositivo del paziente?')">
                    @csrf
                    <input type="hidden" name="attiva" value="1"/>
                    <button type="submit" class="btn-iot btn-alarm-on">🔔 Attiva allarme</button>
                </form>
                @endif

            @else
                <span class="device-badge offline"><span class="dot gray"></span>Nessun dispositivo</span>
            @endif

            <a href="{{ route('medico.pazienti.index') }}" class="btn-back">← Torna alla lista</a>
        </div>
    </div>

    {{-- Stats assunzioni oggi --}}
    <div class="stat-row">
        <div class="stat-pill blue">
            <div class="val">{{ $stats['oggi_totali'] }}</div>
            <div class="lbl">Previste oggi</div>
        </div>
        <div class="stat-pill green">
            <div class="val">{{ $stats['oggi_prese'] }}</div>
            <div class="lbl">Prese</div>
        </div>
        <div class="stat-pill red">
            <div class="val">{{ $stats['oggi_saltate'] }}</div>
            <div class="lbl">Saltate</div>
        </div>
        <div class="stat-pill yellow">
            <div class="val">{{ $stats['oggi_attesa'] }}</div>
            <div class="lbl">In attesa</div>
        </div>
    </div>

    {{-- Grid: Assunzioni + Terapie --}}
    <div class="grid2">

        {{-- Assunzioni ultimi 7 giorni --}}
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-title">
                📋 Assunzioni — ultimi 7 giorni
                <span style="font-size:11px; color:var(--muted); font-weight:400; margin-left:auto;">Clicca sullo stato per modificarlo</span>
            </div>
            @if($assunzioni->isEmpty())
                <div style="text-align:center; color:var(--muted); padding:30px; font-size:13px;">Nessuna assunzione registrata negli ultimi 7 giorni.</div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Data prevista</th>
                            <th>Farmaco</th>
                            <th>Orario</th>
                            <th>Stato</th>
                            <th>Confermata da</th>
                            <th>Conferma</th>
                            <th>Dispositivo</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($assunzioni as $a)
                    @php
                        $farmaco = $a->somministrazione->terapia->farmaco ?? null;
                        $ora     = $a->somministrazione->ora ?? '--';
                    @endphp
                    <tr data-id="{{ $a->id }}">
                        <td>{{ \Carbon\Carbon::parse($a->data_prevista)->format('d/m/Y') }}</td>
                        <td style="font-weight:600;">{{ $farmaco->nome ?? 'N/A' }}
                            @if($farmaco && $farmaco->dose)<span style="font-size:11px; color:var(--muted); margin-left:4px;">{{ $farmaco->dose }}</span>@endif
                        </td>
                        <td>{{ substr($ora, 0, 5) }}</td>
                        <td>
                            <span class="stato-badge stato-{{ $a->stato }}" onclick="cambiaStato({{ $a->id }}, '{{ $a->stato }}', this)">
                                {{ statoLabel($a->stato) }}
                            </span>
                        </td>
                        <td><span class="conf-badge">{{ $a->confermata_da }}</span></td>
                        <td style="font-size:12px; color:var(--muted);">
                            {{ $a->data_conferma ? \Carbon\Carbon::parse($a->data_conferma)->format('d/m H:i') : '—' }}
                        </td>
                        <td style="font-size:12px; color:var(--muted);">
                            {{ $a->dispositivo?->nome_dispositivo ?? $a->dispositivo?->codice_seriale ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>

    {{-- Terapie --}}
    <div class="card" id="terapie">
        <div class="card-title">
            💊 Terapie
            <button class="btn-add-terapia" onclick="toggleForm()" style="margin-left:auto;">+ Aggiungi terapia</button>
        </div>

        {{-- Form aggiunta terapia --}}
        <div class="form-add" id="formTerapia">
            <form method="POST" action="{{ route('medico.pazienti.terapie.store', $paziente->id) }}">
                @csrf
                <div class="form-row">
                    <div>
                        <label>Farmaco *</label>
                        <select name="id_farmaco" required>
                            <option value="">— Seleziona farmaco —</option>
                            @foreach(\App\Models\Farmaco::orderBy('nome')->get() as $farmaco)
                            <option value="{{ $farmaco->id }}">{{ $farmaco->nome }}{{ $farmaco->dose ? ' ('.$farmaco->dose.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Quantità (pillole) *</label>
                        <input type="number" name="quantita" min="1" value="1" required/>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Data inizio *</label>
                        <input type="date" name="data_inizio" value="{{ date('Y-m-d') }}" required/>
                    </div>
                    <div>
                        <label>Data fine</label>
                        <input type="date" name="data_fine"/>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Orario somministrazione *</label>
                        <input type="time" name="ora" required/>
                    </div>
                    <div>
                        <label>Frequenza (es. ogni 8h)</label>
                        <input type="text" name="frequenza" placeholder="es. ogni 8 ore"/>
                    </div>
                </div>
                <div class="form-row full" style="margin-bottom:12px;">
                    <div>
                        <label>Giorni somministrazione *</label>
                        <div class="giorni-check">
                            @foreach(['Tutti','Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $g)
                            <label><input type="checkbox" name="giorni[]" value="{{ $g }}" {{ $g==='Tutti'?'checked':'' }}/> {{ $g }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-row full">
                    <div>
                        <label>Istruzioni</label>
                        <textarea name="istruzioni" rows="2" placeholder="es. da prendere a stomaco pieno..."></textarea>
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-submit">💾 Salva terapia</button>
                    <button type="button" onclick="toggleForm()" style="padding:10px 16px; background:transparent; border:1px solid var(--border); border-radius:8px; color:var(--muted); font-size:13px; cursor:pointer; font-family:inherit;">Annulla</button>
                </div>
            </form>
        </div>

        {{-- Lista terapie --}}
        @forelse($paziente->terapie as $terapia)
        <div class="terapia-item">
            <div class="terapia-header">
                <div class="terapia-name">💊 {{ $terapia->farmaco->nome ?? 'Farmaco sconosciuto' }}
                    @if($terapia->farmaco?->dose)
                        <span style="font-size:12px; color:var(--muted); font-weight:400;"> — {{ $terapia->farmaco->dose }}</span>
                    @endif
                </div>
                <span class="{{ $terapia->attiva ? 'badge-attiva' : 'badge-inattiva' }}">{{ $terapia->attiva ? 'Attiva' : 'Conclusa' }}</span>
            </div>
            <div class="terapia-meta">
                📅 {{ $terapia->data_inizio->format('d/m/Y') }}
                @if($terapia->data_fine) → {{ $terapia->data_fine->format('d/m/Y') }} @endif
                &nbsp;·&nbsp;
                💊 {{ $terapia->quantita }} pillola/e
                @if($terapia->frequenza)&nbsp;·&nbsp; ⏱ {{ $terapia->frequenza }}@endif
            </div>
            @if($terapia->istruzioni)
            <div style="margin-top:6px; font-size:12px; color:var(--muted);">📝 {{ $terapia->istruzioni }}</div>
            @endif
            @if($terapia->somministrazioni->count())
            <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:6px;">
                @foreach($terapia->somministrazioni as $somm)
                <span style="font-size:11px; background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.2); color:#93c5fd; padding:2px 8px; border-radius:10px;">
                    {{ $somm->giorno_settimana }} {{ substr($somm->ora,0,5) }}
                </span>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div style="text-align:center; color:var(--muted); padding:24px; font-size:13px;">Nessuna terapia registrata. Usa il pulsante per aggiungerne una.</div>
        @endforelse
    </div>

</main>

{{-- Toast notifica --}}
<div class="toast" id="toast"></div>

<script>
// ── Helper statoLabel (lato JS) ──────────────────────────
const STATI_LABELS = {
    'assunta':         '✅ Assunta',
    'erogata':         '💊 Erogata',
    'saltata':         '❌ Saltata',
    'non_ritirata':    '⏸ Non ritirata',
    'in_attesa':       '⏳ In attesa',
    'ritardo':         '⚠️ Ritardo',
    'allarme_attivo':  '🔔 Allarme',
    'apertura_forzata':'🔓 Forzata',
};

const STATI_SEQUENZA = ['in_attesa', 'assunta', 'saltata', 'non_ritirata'];

function cambiaStato(id, statoAttuale, el) {
    const idx = STATI_SEQUENZA.indexOf(statoAttuale);
    const prossimo = STATI_SEQUENZA[(idx + 1) % STATI_SEQUENZA.length];

    if (!confirm(`Cambia stato: "${STATI_LABELS[statoAttuale] ?? statoAttuale}" → "${STATI_LABELS[prossimo] ?? prossimo}"?`)) return;

    fetch(`/medico/assunzioni/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ stato: prossimo }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            el.className = `stato-badge stato-${data.stato}`;
            el.textContent = STATI_LABELS[data.stato] ?? data.stato;
            el.setAttribute('onclick', `cambiaStato(${id}, '${data.stato}', this)`);
            showToast('✅ Stato aggiornato: ' + (STATI_LABELS[data.stato] ?? data.stato), 'success');

            // Aggiorna i contatori live
            aggiornaContatori();
        } else {
            showToast('Errore aggiornamento', 'error');
        }
    })
    .catch(() => showToast('Errore di rete', 'error'));
}

function aggiornaContatori() {
    // Ricalcola contatori senza ricaricare la pagina
    const righe = document.querySelectorAll('tbody tr');
    let prese=0, saltate=0, attesa=0;
    righe.forEach(r => {
        const badge = r.querySelector('.stato-badge');
        if (!badge) return;
        const cl = [...badge.classList].find(c => c.startsWith('stato-'))?.replace('stato-','');
        if (['assunta','erogata'].includes(cl)) prese++;
        else if (['saltata','non_ritirata'].includes(cl)) saltate++;
        else if (cl === 'in_attesa') attesa++;
    });
    // Aggiorna le pill cards (se presenti con data-type)
    document.querySelectorAll('.stat-pill').forEach(p => {
        const lbl = p.querySelector('.lbl')?.textContent.toLowerCase();
        if (lbl?.includes('prese'))   p.querySelector('.val').textContent = prese;
        if (lbl?.includes('saltate')) p.querySelector('.val').textContent = saltate;
        if (lbl?.includes('attesa'))  p.querySelector('.val').textContent = attesa;
    });
}

function showToast(msg, type='success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `toast ${type} show`;
    setTimeout(() => t.classList.remove('show'), 3000);
}

function toggleForm() {
    document.getElementById('formTerapia').classList.toggle('open');
}

// Auto-chiudi giorni "Tutti" se si selezionano altri
document.querySelectorAll('.giorni-check input').forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.value === 'Tutti' && this.checked) {
            document.querySelectorAll('.giorni-check input:not([value=Tutti])').forEach(x => x.checked = false);
        } else if (this.value !== 'Tutti' && this.checked) {
            const tutti = document.querySelector('.giorni-check input[value=Tutti]');
            if (tutti) tutti.checked = false;
        }
    });
});
</script>

@php
function statoLabel(string $stato): string {
    return match($stato) {
        'assunta'         => '✅ Assunta',
        'erogata'         => '💊 Erogata',
        'saltata'         => '❌ Saltata',
        'non_ritirata'    => '⏸ Non ritirata',
        'in_attesa'       => '⏳ In attesa',
        'ritardo'         => '⚠️ Ritardo',
        'allarme_attivo'  => '🔔 Allarme attivo',
        'apertura_forzata'=> '🔓 Erogazione forzata',
        default           => ucfirst($stato),
    };
}
@endphp

</body>
</html>
