{{-- resources/views/medico/dashboard.blade.php --}}
    <!DOCTYPE html>
<html lang="it">
<head>
        @vite('resources/js/app.js')
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>PillMate — Dashboard Medico</title>
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
        @vite('resources/css/dashboard.css')
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="pill"></i>
        </div>
        <span class="brand-name">PillMate</span>
    </div>

    <div class="nav-label">Menu</div>

    <a class="nav-item active" href="{{ route('medico.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>
        Dashboard
    </a>

    <a class="nav-item" href="{{ route('medico.pazienti.index') }}">
        <span class="ico"><i data-lucide="users"></i></span>
        I miei pazienti
    </a>

    <a class="nav-item" href="{{ route('medico.notifiche') }}">
        <span class="ico"><i data-lucide="bell"></i></span>
        Notifiche
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($medico->nome, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ $medico->nome }} {{ $medico->cognome }}</div>
                <div class="user-role">
                    <i data-lucide="stethoscope"></i>
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
    <div class="page-header">
        <h1>Buongiorno, Dr. {{ $medico->cognome }}</h1>
        <p>Ecco il riepilogo di oggi — {{ now()->translatedFormat('l d F Y') }}</p>
    </div>

    @if(session('success'))
        <div class="alert-success-banner">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div style="display:flex; justify-content:flex-end; margin-bottom:22px;">
        <a href="{{ route('medico.pazienti.create') }}" class="btn-add">
            <i data-lucide="user-plus"></i>
            Aggiungi paziente
        </a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pazienti seguiti</span>
                <div class="stat-ico blue">
                    <i data-lucide="users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $numPazienti }}</div>
            <div class="stat-sub">pazienti attivi</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Terapie attive</span>
                <div class="stat-ico green">
                    <i data-lucide="pill"></i>
                </div>
            </div>
            <div class="stat-value">{{ $terapieAttive }}</div>
            <div class="stat-sub">in corso</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Assunzioni oggi</span>
                <div class="stat-ico yellow">
                    <i data-lucide="clipboard-list"></i>
                </div>
            </div>
            <div class="stat-value">{{ $assunzioniPreviste }}</div>
            <div class="stat-sub">previste</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Dosi saltate</span>
                <div class="stat-ico red">
                    <i data-lucide="alert-triangle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $dosiSaltate }}</div>
            <div class="stat-sub">ultime 24h</div>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <div class="card-title">
                <i data-lucide="users"></i>
                Ultimi pazienti
            </div>

            @forelse($ultimiPazienti as $paz)
                @php
                    $u = $paz->utente;
                    $hasAlarm = $paz->dispositivi->where('allarme_attivo', true)->isNotEmpty();
                @endphp

                <div class="paziente-row">
                    <div class="paz-avatar">
                        {{ strtoupper(substr($u->nome,0,1)) }}{{ strtoupper(substr($u->cognome,0,1)) }}
                    </div>

                    <div style="flex:1; min-width:0;">
                        <div class="paz-name">{{ $u->cognome }} {{ $u->nome }}</div>
                        <div class="paz-meta">{{ $paz->terapie->where('attiva',true)->count() }} terapie attive</div>
                    </div>

                    @if($hasAlarm)
                        <span class="alarm-badge">
                            <i data-lucide="alert-triangle"></i>
                            Allarme
                        </span>
                    @endif

                    <a href="{{ route('medico.pazienti.show', $paz->id) }}" class="link-inline">
                        Dettaglio
                        <i data-lucide="arrow-right"></i>
                    </a>
                </div>
            @empty
                <div class="empty-state">Nessun paziente ancora assegnato.</div>
            @endforelse
        </div>

        <div class="card">
            <div class="card-title">
                <i data-lucide="bell"></i>
                Notifiche recenti
            </div>

            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="bell-off"></i>
                </div>

                Nessuna notifica recente.

                <div style="margin-top:12px;">
                    <a href="{{ route('medico.notifiche') }}" class="link-inline">
                        Vai alle notifiche
                        <i data-lucide="arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
