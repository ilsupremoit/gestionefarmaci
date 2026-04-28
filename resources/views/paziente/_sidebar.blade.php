@php
    $nonLette = \Illuminate\Support\Facades\DB::table('notifiche')
        ->where('id_utente', auth()->id())
        ->where('letta', false)
        ->count();
@endphp

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="pill"></i>
        </div>
        <span class="brand-name">PillMate</span>
    </div>

    <div class="nav-label">Menu</div>

    <a class="nav-item {{ ($active??'')=='dashboard' ? 'active':'' }}" href="{{ route('paziente.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>
        Dashboard
    </a>

    <a class="nav-item {{ ($active??'')=='terapie' ? 'active':'' }}" href="{{ route('paziente.terapie') }}">
        <span class="ico"><i data-lucide="pill"></i></span>
        Le mie terapie
    </a>

    <a class="nav-item {{ ($active??'')=='storico' ? 'active':'' }}" href="{{ route('paziente.storico') }}">
        <span class="ico"><i data-lucide="clipboard-list"></i></span>
        Storico assunzioni
    </a>

    <a class="nav-item {{ ($active??'')=='dispositivi' ? 'active':'' }}" href="{{ route('paziente.dispositivi') }}">
        <span class="ico"><i data-lucide="radio"></i></span>
        Dispositivi
    </a>

    <a class="nav-item {{ ($active??'')=='notifiche' ? 'active':'' }}" href="{{ route('paziente.notifiche') }}">
        <span class="ico"><i data-lucide="bell"></i></span>
        Notifiche
        @if($nonLette > 0)
            <span class="nav-badge">{{ $nonLette }}</span>
        @endif
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($utente->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ $utente->nome }} {{ $utente->cognome }}</div>
                <div class="user-role">
                    <i data-lucide="user-round"></i>
                    Paziente
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

<nav class="mobile-nav" aria-label="Navigazione paziente">
    <a class="mobile-nav-item {{ ($active??'')=='dashboard' ? 'active':'' }}" href="{{ route('paziente.dashboard') }}">
        <i data-lucide="layout-dashboard"></i>
        <span>Home</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')=='terapie' ? 'active':'' }}" href="{{ route('paziente.terapie') }}">
        <i data-lucide="pill"></i>
        <span>Terapie</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')=='storico' ? 'active':'' }}" href="{{ route('paziente.storico') }}">
        <i data-lucide="clipboard-list"></i>
        <span>Storico</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')=='notifiche' ? 'active':'' }}" href="{{ route('paziente.notifiche') }}">
        <i data-lucide="bell"></i>
        <span>Avvisi</span>
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="mobile-nav-item mobile-nav-logout">
            <i data-lucide="log-out"></i>
            <span>Esci</span>
        </button>
    </form>
</nav>
