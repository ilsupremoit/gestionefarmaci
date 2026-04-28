<nav class="mobile-nav" aria-label="Navigazione medico">
    <a class="mobile-nav-item {{ ($active??'')==='dashboard' ? 'active':'' }}" href="{{ route('medico.dashboard') }}">
        <i data-lucide="layout-dashboard"></i>
        <span>Home</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')==='pazienti' ? 'active':'' }}" href="{{ route('medico.pazienti.index') }}">
        <i data-lucide="users"></i>
        <span>Pazienti</span>
    </a>
    <a class="mobile-nav-item {{ ($active??'')==='notifiche' ? 'active':'' }}" href="{{ route('medico.notifiche') }}">
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
