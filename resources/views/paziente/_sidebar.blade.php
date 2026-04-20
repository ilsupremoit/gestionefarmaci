@php
$nonLette = \Illuminate\Support\Facades\DB::table('notifiche')
    ->where('id_utente', auth()->id())->where('letta', false)->count();
@endphp
<aside class="sidebar">
    <div class="brand"><span class="brand-name">PillMate</span></div>
    <div class="nav-label">Menu</div>
    <a class="nav-item {{ ($active??'')=='dashboard' ? 'active':'' }}" href="{{ route('paziente.dashboard') }}"><span class="ico">🏠</span> Dashboard</a>
    <a class="nav-item {{ ($active??'')=='terapie' ? 'active':'' }}" href="{{ route('paziente.terapie') }}"><span class="ico">💊</span> Le mie terapie</a>
    <a class="nav-item {{ ($active??'')=='storico' ? 'active':'' }}" href="{{ route('paziente.storico') }}"><span class="ico">📋</span> Storico assunzioni</a>
    <a class="nav-item {{ ($active??'')=='dispositivi' ? 'active':'' }}" href="{{ route('paziente.dispositivi') }}"><span class="ico">📡</span> Dispositivi</a>
    <a class="nav-item {{ ($active??'')=='notifiche' ? 'active':'' }}" href="{{ route('paziente.notifiche') }}">
        <span class="ico">🔔</span> Notifiche
        @if($nonLette > 0)<span class="nav-badge">{{ $nonLette }}</span>@endif
    </a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($utente->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ $utente->nome }} {{ $utente->cognome }}</div>
                <div class="user-role">🧑 Paziente</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>
