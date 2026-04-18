{{-- resources/views/medico/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Dashboard Medico</title>
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
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            padding: 28px 0;
            position: fixed; top: 0; left: 0; height: 100vh;
        }
        .brand {
            display: flex; align-items: center; gap: 12px;
            padding: 0 24px 28px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .brand-name {
            font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .nav-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: var(--muted);
            padding: 0 24px; margin-bottom: 8px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 24px; font-size: 14px; color: var(--muted);
            text-decoration: none; transition: all .2s; border-radius: 0;
            cursor: pointer;
        }
        .nav-item:hover { color: var(--text); background: rgba(255,255,255,.04); }
        .nav-item.active { color: var(--accent); background: rgba(59,130,246,.08); border-right: 2px solid var(--accent); }
        .nav-item .ico { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer {
            margin-top: auto; padding: 0 24px 0;
            border-top: 1px solid var(--border); padding-top: 20px;
        }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700;
        }
        .user-name { font-size: 13px; font-weight: 600; }
        .user-role { font-size: 11px; color: var(--muted); }
        .btn-logout {
            width: 100%; padding: 9px;
            background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2);
            border-radius: 8px; color: #fca5a5; font-size: 13px;
            cursor: pointer; transition: all .2s; font-family: inherit;
        }
        .btn-logout:hover { background: rgba(239,68,68,.2); }

        /* Main */
        .main { margin-left: 240px; flex: 1; padding: 36px 40px; }
        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .page-header p { color: var(--muted); font-size: 14px; }

        /* Stats cards */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 20px;
        }
        .stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .stat-label { font-size: 12px; color: var(--muted); font-weight: 500; }
        .stat-ico {
            width: 36px; height: 36px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .stat-ico.blue  { background: rgba(59,130,246,.15); }
        .stat-ico.green { background: rgba(16,185,129,.15); }
        .stat-ico.yellow{ background: rgba(245,158,11,.15); }
        .stat-ico.red   { background: rgba(239,68,68,.15); }
        .stat-value { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 700; }
        .stat-sub { font-size: 11px; color: var(--muted); margin-top: 2px; }

        /* Content grid */
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 22px;
        }
        .card-title {
            font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700;
            margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
        }
        .empty-state { text-align: center; color: var(--muted); font-size: 13px; padding: 20px 0; }

        @media (max-width: 1024px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .content-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 24px 20px; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <span class="brand-name">PillMate</span>
    </div>

    <div class="nav-label">Menu</div>
    <a class="nav-item active" href="#">
        <span class="ico">🏠</span> Dashboard
    </a>
    <a class="nav-item" href="{{ route('medico.pazienti.index') }}">
        <span class="ico">👥</span> I miei pazienti
    </a>
    <a class="nav-item" href="#">
        <span class="ico">🔔</span> Notifiche
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($medico->nome, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ $medico->nome }} {{ $medico->cognome }}</div>
                <div class="user-role">👨‍⚕️ Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <h1>Benvenuto, Dr. {{ $medico->cognome }} 👋</h1>
        <p>Ecco il riepilogo di oggi — {{ now()->format('d/m/Y') }}</p>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 14px 16px; border-radius: 12px; background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); color: #6ee7b7;">{{ session('success') }}</div>
    @endif

    <div style="display:flex; justify-content:flex-end; margin-bottom:24px;">
        <a href="{{ route('medico.pazienti.create') }}" style="text-decoration:none; background: linear-gradient(135deg, var(--accent), var(--accent2)); color:#fff; padding:12px 18px; border-radius:12px; font-weight:700;">Aggiungi paziente</a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pazienti seguiti</span>
                <div class="stat-ico blue">👥</div>
            </div>
            <div class="stat-value">{{ $numPazienti }}</div>
            <div class="stat-sub">pazienti attivi</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Terapie attive</span>
                <div class="stat-ico green">💊</div>
            </div>
            <div class="stat-value">{{ $terapieAttive }}</div>
            <div class="stat-sub">in corso</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Assunzioni oggi</span>
                <div class="stat-ico yellow">📋</div>
            </div>
            <div class="stat-value">{{ $assunzioniPreviste }}</div>
            <div class="stat-sub">previste</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Dosi saltate</span>
                <div class="stat-ico red">⚠️</div>
            </div>
            <div class="stat-value">{{ $dosiSaltate }}</div>
            <div class="stat-sub">ultime 24h</div>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <div class="card-title">👥 Ultimi pazienti</div>
            @forelse($ultimiPazienti as $paz)
            @php $u = $paz->utente; $hasAlarm = $paz->dispositivi->where('allarme_attivo', true)->isNotEmpty(); @endphp
            <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid var(--border);">
                <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#1e3a5f,#0e4d6e); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; color:var(--accent2); flex-shrink:0;">
                    {{ strtoupper(substr($u->nome,0,1)) }}{{ strtoupper(substr($u->cognome,0,1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:13px;">{{ $u->cognome }} {{ $u->nome }}</div>
                    <div style="font-size:11px; color:var(--muted);">{{ $paz->terapie->where('attiva',true)->count() }} terapie attive</div>
                </div>
                @if($hasAlarm)<span style="color:var(--red); font-size:11px;">⚠ Allarme</span>@endif
                <a href="{{ route('medico.pazienti.show', $paz->id) }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Dettaglio →</a>
            </div>
            @empty
            <div class="empty-state">Nessun paziente ancora assegnato.</div>
            @endforelse
        </div>
        <div class="card">
            <div class="card-title">🔔 Notifiche recenti</div>
            <div class="empty-state">Nessuna notifica.</div>
        </div>
    </div>
</main>

</body>
</html>
