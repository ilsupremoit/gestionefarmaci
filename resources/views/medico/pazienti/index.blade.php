<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — I miei pazienti</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/MieiPazienti.css')
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

    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>
        Dashboard
    </a>

    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">
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

    <div class="page-header">
        <div>
            <h1>I miei pazienti</h1>
            <p>Gestisci e monitora i pazienti assegnati al tuo profilo</p>
        </div>

        <a href="{{ route('medico.pazienti.create') }}" class="btn-primary">
            <i data-lucide="user-plus"></i>
            Aggiungi paziente
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="toolbar">
        <form method="GET" action="{{ route('medico.pazienti.index') }}" style="display:flex; gap:8px; flex:1; flex-wrap:wrap;">
            <div class="search-wrap">
                <span class="search-icon">
                    <i data-lucide="search"></i>
                </span>
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
                <a href="{{ route('medico.pazienti.index') }}" class="btn-search">Cancella</a>
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
                            <i data-lucide="mail"></i>
                            <span>{{ $utente->email }}</span>
                        </div>
                    @endif

                    @if($utente->telefono)
                        <div class="info-row">
                            <i data-lucide="phone"></i>
                            <span>{{ $utente->telefono }}</span>
                        </div>
                    @endif

                    @if($paziente->indirizzo)
                        <div class="info-row">
                            <i data-lucide="map-pin"></i>
                            <span>{{ $paziente->indirizzo }}</span>
                        </div>
                    @endif

                    @if($dispAttivo && $dispAttivo->ultima_connessione)
                        <div class="info-row">
                            <i data-lucide="clock-3"></i>
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
                    <i data-lucide="users"></i>
                </div>

                @if(request('q'))
                    <h3>Nessun risultato per "{{ request('q') }}"</h3>
                    <p>Prova con un altro termine di ricerca.</p>
                    <a href="{{ route('medico.pazienti.index') }}" class="btn-primary" style="display:inline-flex;">Mostra tutti</a>
                @else
                    <h3>Nessun paziente assegnato</h3>
                    <p>Aggiungi il primo paziente per iniziare a gestire le terapie.</p>
                    <a href="{{ route('medico.pazienti.create') }}" class="btn-primary" style="display:inline-flex;">
                        <i data-lucide="user-plus"></i>
                        Aggiungi paziente
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @if($pazienti->hasPages())
        <div class="pagination-wrap">
            @if($pazienti->onFirstPage())
                <span class="disabled">
            <i data-lucide="chevron-left"></i>
        </span>
            @else
                <a href="{{ $pazienti->previousPageUrl() }}">
                    <i data-lucide="chevron-left"></i>
                </a>
            @endif

            @foreach($pazienti->getUrlRange(1, $pazienti->lastPage()) as $page => $url)
                @if($page == $pazienti->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($pazienti->hasMorePages())
                <a href="{{ $pazienti->nextPageUrl() }}">
                    <i data-lucide="chevron-right"></i>
                </a>
            @else
                <span class="disabled">
            <i data-lucide="chevron-right"></i>
        </span>
            @endif
        </div>
    @endif

</main>

</body>
</html>
