<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet"/>
    <style>
        /* ─── Variables ─────────────────────────────────── */
        :root {
            --bg:       #0b0f1a;
            --surface:  #111827;
            --surface2: #1a2236;
            --border:   #1f2d45;
            --accent:   #3b82f6;
            --accent2:  #06b6d4;
            --green:    #10b981;
            --orange:   #f59e0b;
            --red:      #ef4444;
            --text:     #e2e8f0;
            --muted:    #64748b;
            --card-glow: 0 0 40px rgba(59,130,246,.12);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ─── Sidebar ───────────────────────────────────── */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            z-index: 100;
            transition: width .3s;
        }

        .sidebar-logo {
            padding: 28px 24px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .logo-text {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff, var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-section {
            padding: 16px 12px 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            margin: 2px 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s, color .2s;
            font-size: 14px;
            font-weight: 400;
            color: var(--muted);
            text-decoration: none;
        }

        .nav-item:hover { background: var(--surface2); color: var(--text); }
        .nav-item.active {
            background: rgba(59,130,246,.15);
            color: var(--accent);
            font-weight: 500;
        }

        .nav-icon { font-size: 17px; width: 20px; text-align: center; flex-shrink: 0; }

        .nav-badge {
            margin-left: auto;
            background: var(--accent);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 1px 7px;
            border-radius: 20px;
        }

        .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid var(--border);
            padding: 16px;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            background: var(--surface2);
            cursor: pointer;
        }

        .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 500; }
        .user-role {
            font-size: 11px;
            color: var(--muted);
            text-transform: capitalize;
        }

        /* ─── Main ──────────────────────────────────────── */
        .main {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ─── Topbar ────────────────────────────────────── */
        .topbar {
            height: 64px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 32px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 700;
        }

        .topbar-search {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 14px;
            width: 240px;
        }

        .topbar-search input {
            background: none;
            border: none;
            outline: none;
            color: var(--text);
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            width: 100%;
        }
        .topbar-search input::placeholder { color: var(--muted); }

        .topbar-actions { display: flex; align-items: center; gap: 10px; }

        .icon-btn {
            width: 36px; height: 36px;
            border-radius: 8px;
            background: var(--surface2);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 16px;
            position: relative;
            transition: background .2s;
        }
        .icon-btn:hover { background: var(--border); }

        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 7px; height: 7px;
            background: var(--accent);
            border-radius: 50%;
            border: 1px solid var(--surface);
        }

        /* ─── Page Content ──────────────────────────────── */
        .content { padding: 32px; flex: 1; }

        .page-header { margin-bottom: 28px; }
        .page-header h1 {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
        }
        .page-header p { color: var(--muted); font-size: 14px; margin-top: 4px; }

        /* ─── Stats Row ─────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: var(--card-glow);
            transition: transform .2s, box-shadow .2s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }
        .stat-card.blue::before  { background: linear-gradient(90deg, var(--accent), var(--accent2)); }
        .stat-card.green::before { background: linear-gradient(90deg, var(--green), #34d399); }
        .stat-card.orange::before { background: linear-gradient(90deg, var(--orange), #fbbf24); }
        .stat-card.red::before   { background: linear-gradient(90deg, var(--red), #f87171); }

        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 40px rgba(59,130,246,.2); }

        .stat-label { font-size: 12px; color: var(--muted); font-weight: 500; text-transform: uppercase; letter-spacing: .8px; }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 34px;
            font-weight: 800;
            margin: 6px 0 4px;
            line-height: 1;
        }
        .stat-card.blue  .stat-value { color: var(--accent); }
        .stat-card.green .stat-value { color: var(--green); }
        .stat-card.orange .stat-value { color: var(--orange); }
        .stat-card.red   .stat-value { color: var(--red); }

        .stat-sub { font-size: 12px; color: var(--muted); }
        .stat-icon { position: absolute; top: 18px; right: 20px; font-size: 26px; opacity: .15; }

        /* ─── Grid Layout ───────────────────────────────── */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 22px 14px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
        }

        .card-action {
            font-size: 12px;
            color: var(--accent);
            cursor: pointer;
            font-weight: 500;
        }
        .card-action:hover { text-decoration: underline; }

        /* ─── Timeline / Assunzioni ─────────────────────── */
        .timeline { padding: 8px 0; }

        .t-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 22px;
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }
        .t-item:last-child { border-bottom: none; }
        .t-item:hover { background: var(--surface2); }

        .t-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            margin-top: 4px;
            flex-shrink: 0;
        }
        .t-dot.green { background: var(--green); box-shadow: 0 0 8px var(--green); }
        .t-dot.orange { background: var(--orange); box-shadow: 0 0 8px var(--orange); }
        .t-dot.red    { background: var(--red); box-shadow: 0 0 8px var(--red); }
        .t-dot.muted  { background: var(--muted); }

        .t-info { flex: 1; }
        .t-name { font-size: 14px; font-weight: 500; }
        .t-sub  { font-size: 12px; color: var(--muted); margin-top: 2px; }

        .t-time { font-size: 12px; color: var(--muted); white-space: nowrap; }

        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-green  { background: rgba(16,185,129,.15); color: var(--green); }
        .badge-orange { background: rgba(245,158,11,.15); color: var(--orange); }
        .badge-red    { background: rgba(239,68,68,.15);  color: var(--red); }
        .badge-blue   { background: rgba(59,130,246,.15); color: var(--accent); }
        .badge-muted  { background: rgba(100,116,139,.15); color: var(--muted); }

        /* ─── Dispositivi ───────────────────────────────── */
        .device-list { padding: 8px 0; }

        .device-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 22px;
            border-bottom: 1px solid var(--border);
            transition: background .15s;
            cursor: pointer;
        }
        .device-item:last-child { border-bottom: none; }
        .device-item:hover { background: var(--surface2); }

        .device-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: var(--surface2);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .device-info { flex: 1; }
        .device-name  { font-size: 13px; font-weight: 500; }
        .device-meta  { font-size: 11px; color: var(--muted); margin-top: 2px; }

        .battery-bar {
            width: 52px; height: 5px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 3px;
        }
        .battery-fill { height: 100%; border-radius: 3px; }

        /* ─── Status indicator ──────────────────────────── */
        .status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .status-dot.online  { background: var(--green); box-shadow: 0 0 6px var(--green); }
        .status-dot.offline { background: var(--muted); }
        .status-dot.warn    { background: var(--orange); box-shadow: 0 0 6px var(--orange); }

        /* ─── Notifiche ─────────────────────────────────── */
        .notif-list { padding: 8px 0; }
        .notif-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 22px;
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: var(--surface2); }
        .notif-item.unread { background: rgba(59,130,246,.05); }

        .notif-ico { font-size: 18px; margin-top: 2px; flex-shrink: 0; }
        .notif-text { font-size: 13px; line-height: 1.45; }
        .notif-sub  { font-size: 11px; color: var(--muted); margin-top: 3px; }

        /* ─── Progress bar generico ─────────────────────── */
        .progress-bar {
            height: 6px; background: var(--border); border-radius: 4px; overflow: hidden; margin-top: 6px;
        }
        .progress-fill { height: 100%; border-radius: 4px; }

        /* ─── Bottom row ────────────────────────────────── */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        /* ─── Pazienti list ─────────────────────────────── */
        .patient-row {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 22px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: background .15s;
        }
        .patient-row:last-child { border-bottom: none; }
        .patient-row:hover { background: var(--surface2); }

        .patient-av {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .patient-name { font-size: 13px; font-weight: 500; flex: 1; }

        /* ─── Terapie progress ──────────────────────────── */
        .terapia-row {
            padding: 12px 22px;
            border-bottom: 1px solid var(--border);
        }
        .terapia-row:last-child { border-bottom: none; }
        .terapia-top { display: flex; justify-content: space-between; font-size: 13px; }
        .terapia-farmaco { font-weight: 500; }
        .terapia-pct { color: var(--accent); font-weight: 600; }

        /* ─── Scrollbar ─────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        /* ─── Animations ────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stat-card { animation: fadeUp .4s ease both; }
        .stat-card:nth-child(1) { animation-delay: .05s; }
        .stat-card:nth-child(2) { animation-delay: .1s; }
        .stat-card:nth-child(3) { animation-delay: .15s; }
        .stat-card:nth-child(4) { animation-delay: .2s; }

        .card { animation: fadeUp .5s ease both; animation-delay: .25s; }

        /* ─── Responsive hint ───────────────────────────── */
        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
            .grid-3 { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════ SIDEBAR ════ -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">💊</div>
        <span class="logo-text">PillMate</span>
    </div>

    <!-- MEDICO nav -->
    <div class="sidebar-section">Medico</div>
    <a href="#" class="nav-item active">
        <span class="nav-icon">🏠</span> Dashboard
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">👥</span> Pazienti
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">💊</span> Farmaci
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">📋</span> Terapie
        <span class="nav-badge">{{ $terapieAttiveCount }}</span>
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">📅</span> Somministrazioni
    </a>

    <div class="sidebar-section">Monitoraggio</div>
    <a href="#" class="nav-item">
        <span class="nav-icon">📡</span> Dispositivi
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">✅</span> Assunzioni
        <span class="nav-badge" style="background:var(--red)">{{ $dosiSaltateCount }}</span>
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">💬</span> Feedback
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">🔔</span> Notifiche
        <span class="nav-badge">{{ $notificheNonLetteCount }}</span>
    </a>

    <div class="sidebar-section">Sistema</div>
    <a href="#" class="nav-item">
        <span class="nav-icon">⚙️</span> Impostazioni
    </a>
    <a href="#" class="nav-item">
        <span class="nav-icon">🔒</span> Logout
    </a>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="avatar">{{ strtoupper(substr($utente->nome, 0, 1) . substr($utente->cognome, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ $utente->nome }} {{ $utente->cognome }}</div>
                <div class="user-role">{{ $utente->ruolo }}</div>
            </div>
        </div>
    </div>
</aside>

<!-- ═══════════════════════════════ MAIN ═══════ -->
<div class="main">

    <!-- Topbar -->
    <header class="topbar">
        <span class="topbar-title">Dashboard</span>
        <div class="topbar-search">
            <span>🔍</span>
            <input type="text" placeholder="Cerca paziente, farmaco…" />
        </div>
        <div class="topbar-actions">
            <div class="icon-btn">
                🔔
                <div class="notif-dot"></div>
            </div>
            <div class="icon-btn">⚙️</div>
        </div>
    </header>

    <!-- Content -->
    <div class="content">

        <div class="page-header">
            <h1>Benvenuto, {{ $utente->nome }} 👋</h1>
            <p>{{ now()->isoFormat('dddd D MMMM YYYY') }} — Panoramica delle terapie attive</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">Pazienti attivi</div>
                <div class="stat-value">{{ $pazientiAttiviCount }}</div>
                <div class="stat-sub">Totale in cura</div>
                <div class="stat-icon">👥</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Assunzioni oggi</div>
                <div class="stat-value">{{ $assunzioniOggiCount }}</div>
                <div class="stat-sub">{{ $assunzioniConfermate }} confermate</div>
                <div class="stat-icon">✅</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">In ritardo</div>
                <div class="stat-value">{{ $inRitardoCount }}</div>
                <div class="stat-sub">Ultimi 30 min</div>
                <div class="stat-icon">⏳</div>
            </div>
            <div class="stat-card red">
                <div class="stat-label">Dosi saltate</div>
                <div class="stat-value">{{ $dosiSaltateCount }}</div>
                <div class="stat-sub">Richiede attenzione</div>
                <div class="stat-icon">⚠️</div>
            </div>
        </div>

        <!-- Main grid -->
        <div class="grid-2">

            <!-- Assunzioni recenti -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Assunzioni recenti</span>
                    <span class="card-action">Vedi tutte →</span>
                </div>
                <div class="timeline">
                    @forelse($assunzioniRecenti as $a)
                        @php
                            $dotClass = match($a->stato) {
                              'assunta'    => 'green',
                              'in_attesa'  => 'orange',
                              'saltata'    => 'red',
                              'ritardo'    => 'orange',
                              'erogata'    => 'orange',
                              default      => 'muted',
                            };
                            $badgeClass = match($a->stato) {
                              'assunta'    => 'badge-green',
                              'saltata'    => 'badge-red',
                              'ritardo'    => 'badge-orange',
                              'in_attesa'  => 'badge-orange',
                              'erogata'    => 'badge-blue',
                              default      => 'badge-muted',
                            };
                        @endphp
                        <div class="t-item">
                            <div class="t-dot {{ $dotClass }}"></div>
                            <div class="t-info">
                                <div class="t-name">
                                    {{ $a->terapia->paziente->utente->nome }} {{ $a->terapia->paziente->utente->cognome }}
                                    — <strong>{{ $a->terapia->farmaco->nome }} {{ $a->terapia->farmaco->dose }}</strong>
                                </div>
                                <div class="t-sub">
                                    Confermata da {{ $a->confermata_da }}
                                    <span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', $a->stato) }}</span>
                                </div>
                            </div>
                            <div class="t-time">{{ \Carbon\Carbon::parse($a->data_prevista)->format('H:i') }}</div>
                        </div>
                    @empty
                        <div class="t-item">
                            <div class="t-info" style="color:var(--muted);font-size:13px;padding:8px 0">Nessuna assunzione oggi.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Dispositivi -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Dispositivi IoT</span>
                    <span class="card-action">Gestisci →</span>
                </div>
                <div class="device-list">
                    @forelse($dispositivi as $d)
                        @php
                            $statusClass = match($d->stato) {
                              'attivo'       => 'online',
                              'offline'      => 'offline',
                              'manutenzione' => 'warn',
                              default        => 'offline',
                            };
                            $battColor = $d->batteria >= 50 ? 'var(--green)' : ($d->batteria >= 20 ? 'var(--orange)' : 'var(--red)');
                        @endphp
                        <div class="device-item">
                            <div class="device-icon">📡</div>
                            <div class="device-info">
                                <div class="device-name">{{ $d->nome_dispositivo ?? $d->codice_seriale }}</div>
                                <div class="device-meta">
                                    @if($d->temperatura) T: {{ $d->temperatura }}°C · @endif
                                    @if($d->umidita) U: {{ $d->umidita }}% @endif
                                    @if(!$d->temperatura && !$d->umidita && $d->ultima_connessione)
                                        Ultima conn: {{ \Carbon\Carbon::parse($d->ultima_connessione)->diffForHumans() }}
                                    @endif
                                </div>
                                @if($d->batteria !== null)
                                    <div class="battery-bar">
                                        <div class="battery-fill" style="width:{{ $d->batteria }}%;background:{{ $battColor }}"></div>
                                    </div>
                                @endif
                            </div>
                            <div class="status-dot {{ $statusClass }}"></div>
                        </div>
                    @empty
                        <div class="device-item" style="color:var(--muted);font-size:13px">Nessun dispositivo registrato.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Bottom grid -->
        <div class="grid-3">

            <!-- Pazienti -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Ultimi pazienti</span>
                    <span class="card-action">Vedi tutti →</span>
                </div>
                <div>
                    @forelse($ultimiPazienti as $p)
                        <div class="patient-row">
                            <div class="patient-av">
                                {{ strtoupper(substr($p->utente->nome, 0, 1) . substr($p->utente->cognome, 0, 1)) }}
                            </div>
                            <span class="patient-name">{{ $p->utente->nome }} {{ $p->utente->cognome }}</span>
                            @if($p->dispositivo && $p->dispositivo->stato === 'offline')
                                <span class="badge badge-red">offline</span>
                            @elseif($p->assunzioniInRitardo > 0)
                                <span class="badge badge-orange">ritardo</span>
                            @else
                                <span class="badge badge-green">attivo</span>
                            @endif
                        </div>
                    @empty
                        <div class="patient-row" style="color:var(--muted);font-size:13px">Nessun paziente.</div>
                    @endforelse
                </div>
            </div>

            <!-- Terapie aderenza -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Aderenza terapie</span>
                    <span class="card-action">Dettagli →</span>
                </div>
                <div>
                    @forelse($terapieAderenza as $t)
                        @php
                            $pct = $t->percentuale_aderenza;
                            $barColor = $pct >= 80 ? 'var(--green)' : ($pct >= 60 ? 'var(--accent)' : ($pct >= 40 ? 'var(--orange)' : 'var(--red)'));
                        @endphp
                        <div class="terapia-row">
                            <div class="terapia-top">
                                <span class="terapia-farmaco">{{ $t->farmaco->nome }} {{ $t->farmaco->dose }}</span>
                                <span class="terapia-pct">{{ $pct }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                            </div>
                        </div>
                    @empty
                        <div class="terapia-row" style="color:var(--muted);font-size:13px">Nessuna terapia attiva.</div>
                    @endforelse
                </div>
            </div>

            <!-- Notifiche -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Notifiche recenti</span>
                    <span class="card-action">Tutte →</span>
                </div>
                <div class="notif-list">
                    @forelse($notificheRecenti as $n)
                        @php
                            $ico = match($n->tipo) {
                              'allarme'    => '🚨',
                              'promemoria' => '🔔',
                              'errore'     => '⚠️',
                              'info'       => 'ℹ️',
                              default      => '🔔',
                            };
                        @endphp
                        <div class="notif-item {{ !$n->letta ? 'unread' : '' }}">
                            <div class="notif-ico">{{ $ico }}</div>
                            <div>
                                <div class="notif-text">{{ $n->messaggio }}</div>
                                <div class="notif-sub">{{ $n->tipo }} · {{ \Carbon\Carbon::parse($n->data_invio)->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="notif-item" style="color:var(--muted);font-size:13px">Nessuna notifica.</div>
                    @endforelse
                </div>
            </div>

        </div><!-- /grid-3 -->
    </div><!-- /content -->
</div><!-- /main -->

</body>
</html>
