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
            --bg:      #f0f7ff;
            --surface: #ffffff;
            --border:  #dde8f5;
            --accent:  #2563eb;
            --accent2: #0891b2;
            --green:   #059669;
            --red:     #dc2626;
            --yellow:  #d97706;
            --text:    #1e293b;
            --muted:   #64748b;
            --shadow:  0 2px 12px rgba(37,99,235,.08);
            --shadow-md: 0 4px 20px rgba(37,99,235,.12);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            box-shadow: 2px 0 12px rgba(0,0,0,.04);
            display: flex; flex-direction: column;
            padding: 28px 0;
            position: fixed; top: 0; left: 0; height: 100vh;
        }
        .brand {
            display: flex; align-items: center; gap: 10px;
            padding: 0 24px 24px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
        }
        .brand-name {
            font-family: 'Syne', sans-serif; font-size: 19px; font-weight: 800;
            color: var(--text);
        }
        .nav-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: #94a3b8;
            padding: 0 24px; margin-bottom: 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 24px; font-size: 14px; color: var(--muted);
            text-decoration: none; transition: all .18s;
        }
        .nav-item:hover { color: var(--text); background: #f1f5f9; }
        .nav-item.active {
            color: var(--accent); background: #eff6ff;
            border-right: 3px solid var(--accent);
            font-weight: 600;
        }
        .nav-item .ico { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer {
            margin-top: auto; padding: 20px 24px 0;
            border-top: 1px solid var(--border);
        }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff;
        }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text); }
        .user-role { font-size: 11px; color: var(--muted); }
        .btn-logout {
            width: 100%; padding: 9px;
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 8px; color: #b91c1c; font-size: 13px;
            cursor: pointer; transition: all .2s; font-family: inherit; font-weight: 600;
        }
        .btn-logout:hover { background: #fee2e2; }

        /* Main */
        .main { margin-left: 240px; flex: 1; padding: 36px 40px; }
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 700; margin-bottom: 4px; color: var(--text); }
        .page-header p { color: var(--muted); font-size: 14px; }

        /* Stats cards */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 20px;
            box-shadow: var(--shadow);
            transition: box-shadow .2s;
        }
        .stat-card:hover { box-shadow: var(--shadow-md); }
        .stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .stat-label { font-size: 12px; color: var(--muted); font-weight: 500; }
        .stat-ico {
            width: 36px; height: 36px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .stat-ico.blue   { background: #dbeafe; }
        .stat-ico.green  { background: #d1fae5; }
        .stat-ico.yellow { background: #fef3c7; }
        .stat-ico.red    { background: #fee2e2; }
        .stat-value { font-family: 'Syne', sans-serif; font-size: 30px; font-weight: 700; color: var(--text); }
        .stat-sub { font-size: 11px; color: var(--muted); margin-top: 2px; }

        /* Content grid */
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 14px; padding: 22px;
            box-shadow: var(--shadow);
        }
        .card-title {
            font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
            color: var(--text);
        }
        .empty-state { text-align: center; color: var(--muted); font-size: 13px; padding: 24px 0; }

        .alert-success-banner {
            margin-bottom: 20px; padding: 14px 16px; border-radius: 12px;
            background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d;
            font-size: 14px;
        }

        .btn-add {
            text-decoration: none;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff; padding: 11px 18px;
            border-radius: 10px; font-weight: 700; font-size: 14px;
            box-shadow: 0 4px 14px rgba(37,99,235,.25);
            transition: opacity .2s, transform .15s;
        }
        .btn-add:hover { opacity: .92; transform: translateY(-1px); }

        .paziente-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid var(--border);
        }
        .paziente-row:last-child { border-bottom: none; }

        .paz-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe, #cffafe);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px; color: var(--accent);
            flex-shrink: 0;
        }
        .paz-name { font-weight: 600; font-size: 13px; color: var(--text); }
        .paz-meta { font-size: 11px; color: var(--muted); }

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
        <div class="brand-icon">💊</div>
        <span class="brand-name">PillMate</span>
    </div>

    <div class="nav-label">Menu</div>
    <a class="nav-item active" href="{{ route('medico.dashboard') }}">
        <span class="ico">🏠</span> Dashboard
    </a>
    <a class="nav-item" href="{{ route('medico.pazienti.index') }}">
        <span class="ico">👥</span> I miei pazienti
    </a>
    <a class="nav-item" href="{{ route('medico.notifiche') }}">
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
        <h1>Buongiorno, Dr. {{ $medico->cognome }} 👋</h1>
        <p>Ecco il riepilogo di oggi — {{ now()->translatedFormat('l d F Y') }}</p>
    </div>

    @if(session('success'))
        <div class="alert-success-banner">✅ {{ session('success') }}</div>
    @endif

    <div style="display:flex; justify-content:flex-end; margin-bottom:22px;">
        <a href="{{ route('medico.pazienti.create') }}" class="btn-add">+ Aggiungi paziente</a>
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
                <div class="paziente-row">
                    <div class="paz-avatar">
                        {{ strtoupper(substr($u->nome,0,1)) }}{{ strtoupper(substr($u->cognome,0,1)) }}
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="paz-name">{{ $u->cognome }} {{ $u->nome }}</div>
                        <div class="paz-meta">{{ $paz->terapie->where('attiva',true)->count() }} terapie attive</div>
                    </div>
                    @if($hasAlarm)
                        <span style="color:var(--red); font-size:11px; font-weight:600; background:#fee2e2; padding:3px 8px; border-radius:20px;">⚠ Allarme</span>
                    @endif
                    <a href="{{ route('medico.pazienti.show', $paz->id) }}" style="font-size:12px; color:var(--accent); text-decoration:none; font-weight:600; white-space:nowrap;">Dettaglio →</a>
                </div>
            @empty
                <div class="empty-state">Nessun paziente ancora assegnato.</div>
            @endforelse
        </div>
        <div class="card">
            <div class="card-title">🔔 Notifiche recenti</div>
            <div class="empty-state">
                <div style="font-size:32px; margin-bottom:8px;">🔔</div>
                Nessuna notifica recente.
                <div style="margin-top:12px;">
                    <a href="{{ route('medico.notifiche') }}" style="color:var(--accent); font-size:13px; font-weight:600; text-decoration:none;">Vai alle notifiche →</a>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
