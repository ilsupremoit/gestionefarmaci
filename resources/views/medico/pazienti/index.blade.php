{{-- resources/views/medico/pazienti/index.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — I miei pazienti</title>
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
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* ── Sidebar ── */
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            box-shadow: 2px 0 12px rgba(0,0,0,.04);
            display: flex; flex-direction: column;
            padding: 28px 0;
            position: fixed; top: 0; left: 0; height: 100vh;
        }
        .brand { display: flex; align-items: center; gap: 10px; padding: 0 24px 24px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
        .brand-icon { width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; }
        .brand-name {
            font-family: 'Syne', sans-serif; font-size: 19px; font-weight: 800;
            color: var(--text);
        }
        .nav-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); padding: 0 24px; margin-bottom: 8px; }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 24px; font-size: 14px; color: var(--muted);
            text-decoration: none; transition: all .2s;
        }
        .nav-item:hover { color: var(--text); background: #f1f5f9; }
        .nav-item.active { color: var(--accent); background: #eff6ff; border-right: 3px solid var(--accent); font-weight: 600; }
        .sidebar-footer { margin-top: auto; padding: 20px 24px 0; border-top: 1px solid var(--border); }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;
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

        /* ── Main ── */
        .main { margin-left: 240px; flex: 1; padding: 36px 40px; }

        /* ── Page header ── */
        .page-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            margin-bottom: 28px; gap: 16px; flex-wrap: wrap;
        }
        .page-header h1 { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .page-header p { color: var(--muted); font-size: 14px; }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none; border-radius: 10px;
            color: #fff; font-family: 'Syne', sans-serif; font-size: 13px; font-weight: 700;
            text-decoration: none; white-space: nowrap; cursor: pointer;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 16px rgba(59,130,246,.3);
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }

        /* ── Alert ── */
        .alert-success {
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 10px; padding: 12px 16px; margin-bottom: 24px;
            font-size: 13px; color: #15803d;
        }

        /* ── Search + stats bar ── */
        .toolbar {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 24px; flex-wrap: wrap;
        }
        .search-wrap { position: relative; flex: 1; min-width: 220px; max-width: 380px; }
        .search-wrap svg {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--muted); pointer-events: none;
        }
        .search-input {
            width: 100%;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 10px; padding: 10px 12px 10px 38px;
            color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 14px;
            outline: none; transition: border-color .2s;
        }
        .search-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .search-input::placeholder { color: var(--muted); }
        .btn-search {
            padding: 10px 16px; background: var(--surface);
            border: 1px solid var(--border); border-radius: 10px;
            color: var(--text); font-size: 13px; cursor: pointer;
            transition: border-color .2s; font-family: inherit;
        }
        .btn-search:hover { border-color: var(--accent); color: var(--accent); }

        .count-badge {
            margin-left: auto; font-size: 13px; color: var(--muted);
            white-space: nowrap;
        }
        .count-badge strong { color: var(--text); }

        /* ── Grid ── */
        .patients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }

        /* ── Patient card ── */
        .patient-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 20px;
            transition: border-color .2s, transform .2s;
            display: flex; flex-direction: column; gap: 16px;
        }
        .patient-card:hover { border-color: rgba(59,130,246,.4); transform: translateY(-2px); }

        .card-top { display: flex; align-items: center; gap: 14px; }
        .patient-avatar {
            width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
            background: linear-gradient(135deg, #1e3a5f, #0e4d6e);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800;
            color: var(--accent2);
        }
        .patient-meta { flex: 1; min-width: 0; }
        .patient-name {
            font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .patient-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }

        .status-dot {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
        }
        .status-dot.active  { background: var(--green); box-shadow: 0 0 6px rgba(16,185,129,.6); }
        .status-dot.offline { background: var(--muted); }
        .status-dot.alarm   { background: var(--red); box-shadow: 0 0 6px rgba(239,68,68,.6); }

        /* Stats row */
        .card-stats {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;
        }
        .stat-pill {
            background: rgba(255,255,255,.04); border: 1px solid var(--border);
            border-radius: 8px; padding: 8px 10px; text-align: center;
        }
        .stat-pill .val {
            font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700;
            color: var(--text);
        }
        .stat-pill .lbl { font-size: 10px; color: var(--muted); margin-top: 2px; text-transform: uppercase; letter-spacing: .5px; }
        .stat-pill.highlight .val { color: var(--accent2); }

        /* Info rows */
        .card-info { display: flex; flex-direction: column; gap: 6px; }
        .info-row {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; color: var(--muted);
        }
        .info-row svg { flex-shrink: 0; color: var(--muted); }
        .info-row span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* Card actions */
        .card-actions { display: flex; gap: 8px; margin-top: auto; padding-top: 4px; }
        .btn-card {
            flex: 1; padding: 9px 0; border-radius: 8px; font-size: 13px;
            font-family: inherit; cursor: pointer; text-align: center;
            text-decoration: none; display: inline-block; transition: all .2s;
            border: 1px solid transparent;
        }
        .btn-card.primary {
            background: rgba(59,130,246,.15); color: var(--accent); border-color: rgba(59,130,246,.25);
        }
        .btn-card.primary:hover { background: rgba(59,130,246,.25); }
        .btn-card.secondary {
            background: rgba(255,255,255,.04); color: var(--muted); border-color: var(--border);
        }
        .btn-card.secondary:hover { color: var(--text); border-color: rgba(255,255,255,.15); }

        /* ── Empty state ── */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center; padding: 60px 20px;
            color: var(--muted);
        }
        .empty-icon {
            width: 64px; height: 64px; border-radius: 18px;
            background: rgba(255,255,255,.04); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }
        .empty-state h3 { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .empty-state p { font-size: 13px; margin-bottom: 20px; }

        /* ── Pagination ── */
        .pagination-wrap {
            display: flex; justify-content: center; align-items: center;
            gap: 6px; margin-top: 32px; flex-wrap: wrap;
        }
        .pagination-wrap a, .pagination-wrap span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 10px;
            border-radius: 8px; font-size: 13px; text-decoration: none;
            border: 1px solid var(--border); color: var(--muted);
            background: var(--surface); transition: all .2s;
        }
        .pagination-wrap a:hover { border-color: var(--accent); color: var(--accent); }
        .pagination-wrap span.active { background: var(--accent); border-color: var(--accent); color: #fff; font-weight: 700; }
        .pagination-wrap span.disabled { opacity: .4; pointer-events: none; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 24px 16px; }
            .patients-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <span class="brand-name">PillMate</span>
    </div>
    <div class="nav-label">Menu</div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Dashboard
    </a>
    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        I miei pazienti
    </a>
    <a class="nav-item" href="#">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        Notifiche
    </a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($medico->nome, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ $medico->nome }} {{ $medico->cognome }}</div>
                <div class="user-role">Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Esci</button>
        </form>
    </div>
</aside>

<main class="main">

    <div class="page-header">
        <div>
            <h1>I miei pazienti</h1>
            <p>Gestisci e monitora i pazienti assegnati al tuo profilo</p>
        </div>
        <a href="{{ route('medico.pazienti.create') }}" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Aggiungi paziente
        </a>
    </div>

    @if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="toolbar">
        <form method="GET" action="{{ route('medico.pazienti.index') }}" style="display:flex; gap:8px; flex:1; flex-wrap:wrap;">
            <div class="search-wrap">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input
                    type="text"
                    name="q"
                    class="search-input"
                    placeholder="Cerca per nome, cognome o email..."
                    value="{{ request('q') }}"
                />
            </div>
            <button type="submit" class="btn-search">Cerca</button>
            @if(request('q'))
            <a href="{{ route('medico.pazienti.index') }}" class="btn-search" style="text-decoration:none; display:inline-flex; align-items:center;">Cancella</a>
            @endif
        </form>
        <span class="count-badge">
            <strong>{{ $pazienti->total() }}</strong> {{ $pazienti->total() === 1 ? 'paziente' : 'pazienti' }}
        </span>
    </div>

    <div class="patients-grid">
        @forelse($pazienti as $paziente)
        @php
        $utente        = $paziente->utente;
        $terapieAttive = $paziente->terapie->count();
        $dispositivi   = $paziente->dispositivi;
        $dispCount     = $dispositivi->count();
        $dispAttivo    = $dispositivi->where('stato', 'attivo')->first();
        $hasAlarm      = $dispositivi->where('allarme_attivo', true)->isNotEmpty();
        $eta           = $paziente->data_nascita
        ? \Carbon\Carbon::parse($paziente->data_nascita)->age
        : null;

        $dotClass = $hasAlarm ? 'alarm' : ($dispAttivo ? 'active' : 'offline');
        @endphp
        <div class="patient-card">

            <div class="card-top">
                <div class="patient-avatar">
                    {{ strtoupper(substr($utente->nome, 0, 1)) }}{{ strtoupper(substr($utente->cognome, 0, 1)) }}
                </div>
                <div class="patient-meta">
                    <div class="patient-name">{{ $utente->cognome }} {{ $utente->nome }}</div>
                    <div class="patient-sub">
                        @if($eta) {{ $eta }} anni &middot; @endif
                        @if($paziente->data_nascita) nato il {{ $paziente->data_nascita->format('d/m/Y') }} @endif
                    </div>
                </div>
                <span class="status-dot {{ $dotClass }}" title="{{ $hasAlarm ? 'Allarme attivo' : ($dispAttivo ? 'Dispositivo connesso' : 'Nessun dispositivo attivo') }}"></span>
            </div>

            <div class="card-stats">
                <div class="stat-pill highlight">
                    <div class="val">{{ $terapieAttive }}</div>
                    <div class="lbl">Terapie</div>
                </div>
                <div class="stat-pill">
                    <div class="val">{{ $dispCount }}</div>
                    <div class="lbl">Dispositivi</div>
                </div>
                <div class="stat-pill">
                    <div class="val">
                        @if($dispAttivo && $dispAttivo->batteria !== null)
                        {{ $dispAttivo->batteria }}%
                        @else
                        --
                        @endif
                    </div>
                    <div class="lbl">Batteria</div>
                </div>
            </div>

            <div class="card-info">
                @if($utente->email)
                <div class="info-row">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span>{{ $utente->email }}</span>
                </div>
                @endif
                @if($utente->telefono)
                <div class="info-row">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.63 19.79 19.79 0 01.22 2 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.08 6.08l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/></svg>
                    <span>{{ $utente->telefono }}</span>
                </div>
                @endif
                @if($paziente->indirizzo)
                <div class="info-row">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>{{ $paziente->indirizzo }}</span>
                </div>
                @endif
                @if($dispAttivo && $dispAttivo->ultima_connessione)
                <div class="info-row">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>Ultimo accesso {{ \Carbon\Carbon::parse($dispAttivo->ultima_connessione)->diffForHumans() }}</span>
                </div>
                @endif
            </div>

            <div class="card-actions">
                <a href="{{ route('medico.pazienti.show', $paziente->id) }}" class="btn-card primary">Visualizza</a>
                <a href="{{ route('medico.pazienti.show', $paziente->id) }}#terapie" class="btn-card secondary">Terapie</a>
            </div>

        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">
                <svg width="28" height="28" fill="none" stroke="#64748b" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            @if(request('q'))
            <h3>Nessun risultato per "{{ request('q') }}"</h3>
            <p>Prova con un altro termine di ricerca.</p>
            <a href="{{ route('medico.pazienti.index') }}" class="btn-primary" style="display:inline-flex;">Mostra tutti</a>
            @else
            <h3>Nessun paziente assegnato</h3>
            <p>Aggiungi il primo paziente per iniziare a gestire le terapie.</p>
            <a href="{{ route('medico.pazienti.create') }}" class="btn-primary" style="display:inline-flex;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Aggiungi paziente
            </a>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($pazienti->hasPages())
    <div class="pagination-wrap">
        {{-- Previous --}}
        @if($pazienti->onFirstPage())
        <span class="disabled">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
        @else
        <a href="{{ $pazienti->previousPageUrl() }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        @endif

        {{-- Pages --}}
        @foreach($pazienti->getUrlRange(1, $pazienti->lastPage()) as $page => $url)
        @if($page == $pazienti->currentPage())
        <span class="active">{{ $page }}</span>
        @else
        <a href="{{ $url }}">{{ $page }}</a>
        @endif
        @endforeach

        {{-- Next --}}
        @if($pazienti->hasMorePages())
        <a href="{{ $pazienti->nextPageUrl() }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        @else
        <span class="disabled">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
        @endif
    </div>
    @endif

</main>

</body>
</html>
