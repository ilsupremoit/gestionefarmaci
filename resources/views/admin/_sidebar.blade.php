<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="shield"></i>
        </div>
        <div>
            <div class="brand-name">PillMate</div>
        </div>
    </div>

    <div class="nav-label">Amministrazione</div>

    <a class="nav-item {{ ($active??'')==='dashboard' ? 'active':'' }}" href="{{ route('admin.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>
        Dashboard
    </a>

    <a class="nav-item {{ ($active??'')==='utenti' ? 'active':'' }}" href="{{ route('admin.utenti') }}">
        <span class="ico"><i data-lucide="users"></i></span>
        Utenti
    </a>

    <a class="nav-item {{ ($active??'')==='pazienti' ? 'active':'' }}" href="{{ route('admin.pazienti') }}">
        <span class="ico"><i data-lucide="user-round"></i></span>
        Pazienti
    </a>

    <a class="nav-item {{ ($active??'')==='dispositivi' ? 'active':'' }}" href="{{ route('admin.dispositivi') }}">
        <span class="ico"><i data-lucide="radio"></i></span>
        Dispositivi
    </a>

    <a class="nav-item {{ ($active??'')==='farmaci' ? 'active':'' }}" href="{{ route('admin.farmaci') }}">
        <span class="ico"><i data-lucide="flask-conical"></i></span>
        Farmaci
    </a>

    <a class="nav-item {{ ($active??'')==='notifiche' ? 'active':'' }}" href="{{ route('admin.notifiche') }}">
        <span class="ico"><i data-lucide="mail"></i></span>
        Messaggi
        @php
            $nonLette = \Illuminate\Support\Facades\DB::table('notifiche')
                ->where('id_utente', $admin->id)
                ->where('letta', false)
                ->count();
        @endphp

        @if($nonLette > 0)
            <span style="margin-left:auto;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;">
                {{ $nonLette }}
            </span>
        @endif
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($admin->nome, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ $admin->nome }} {{ $admin->cognome }}</div>
                <div class="user-role">
                    <i data-lucide="shield-check"></i>
                    Amministratore
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

<nav class="mobile-nav" aria-label="Navigazione amministratore">
    <a class="mobile-nav-item {{ ($active??'')==='dashboard' ? 'active':'' }}" href="{{ route('admin.dashboard') }}">
        <i data-lucide="layout-dashboard"></i>
        <span>Home</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')==='utenti' ? 'active':'' }}" href="{{ route('admin.utenti') }}">
        <i data-lucide="users"></i>
        <span>Utenti</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')==='pazienti' ? 'active':'' }}" href="{{ route('admin.pazienti') }}">
        <i data-lucide="user-round"></i>
        <span>Pazienti</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')==='notifiche' ? 'active':'' }}" href="{{ route('admin.notifiche') }}">
        <i data-lucide="mail"></i>
        <span>Msg</span>
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="mobile-nav-item mobile-nav-logout">
            <i data-lucide="log-out"></i>
            <span>Esci</span>
        </button>
    </form>
</nav>
