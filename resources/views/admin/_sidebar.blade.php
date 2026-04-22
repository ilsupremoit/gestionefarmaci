<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">🛡️</div>
        <div><div class="brand-name">PillMate</div></div>
    </div>
    <div class="nav-label">Amministrazione</div>
    <a class="nav-item {{ ($active??'')==='dashboard'  ? 'active':'' }}" href="{{ route('admin.dashboard') }}"><span class="ico">🏠</span> Dashboard</a>
    <a class="nav-item {{ ($active??'')==='utenti'     ? 'active':'' }}" href="{{ route('admin.utenti') }}"><span class="ico">👥</span> Utenti</a>
    <a class="nav-item {{ ($active??'')==='pazienti'   ? 'active':'' }}" href="{{ route('admin.pazienti') }}"><span class="ico">🧑‍🦯</span> Pazienti</a>
    <a class="nav-item {{ ($active??'')==='terapie'    ? 'active':'' }}" href="{{ route('admin.terapie') }}"><span class="ico">💊</span> Terapie</a>
    <a class="nav-item {{ ($active??'')==='dispositivi'? 'active':'' }}" href="{{ route('admin.dispositivi') }}"><span class="ico">📡</span> Dispositivi</a>
    <a class="nav-item {{ ($active??'')==='farmaci'    ? 'active':'' }}" href="{{ route('admin.farmaci') }}"><span class="ico">🧪</span> Farmaci</a>
    <a class="nav-item {{ ($active??'')==='notifiche'  ? 'active':'' }}" href="{{ route('admin.notifiche') }}">
        <span class="ico">📨</span> Messaggi
        @php $nonLette = \Illuminate\Support\Facades\DB::table('notifiche')->where('id_utente', $admin->id)->where('letta', false)->count(); @endphp
        @if($nonLette > 0)<span style="margin-left:auto;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;">{{ $nonLette }}</span>@endif
    </a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($admin->nome, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ $admin->nome }} {{ $admin->cognome }}</div>
                <div class="user-role">🛡️ Amministratore</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>
