<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')
</head>
<body>
@include('admin._sidebar', ['active' => 'dashboard'])

<main class="main">
    <div class="page-header">
        <div>
            <h1>Pannello Amministratore</h1>
            <p>Panoramica del sistema — {{ now()->translatedFormat('d/m/Y') }}</p>
        </div>

        <a href="{{ route('admin.utenti.create') }}" class="btn btn-primary">
            <i data-lucide="user-plus"></i>
            Nuovo utente
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="stats">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Utenti totali</span>
                <div class="stat-ico purple">
                    <i data-lucide="users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['utenti'] }}</div>
            <div class="stat-sub">nel sistema</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Medici</span>
                <div class="stat-ico blue">
                    <i data-lucide="stethoscope"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['medici'] }}</div>
            <div class="stat-sub">attivi</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Pazienti</span>
                <div class="stat-ico green">
                    <i data-lucide="user-round"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['pazienti'] }}</div>
            <div class="stat-sub">registrati</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Dispositivi</span>
                <div class="stat-ico yellow">
                    <i data-lucide="radio"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['dispositivi'] }}</div>
            <div class="stat-sub">collegati</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        <div class="card">
            <div class="card-title">
                <i data-lucide="users"></i>
                Ultimi utenti registrati
                <a href="{{ route('admin.utenti') }}" style="margin-left:auto;font-size:12px;color:var(--accent);font-weight:400;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                    Vedi tutti
                    <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                </a>
            </div>

            @forelse($ultimiUtenti as $u)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;">
                        {{ strtoupper(substr($u->nome,0,1)) }}
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:13px;">{{ $u->cognome }} {{ $u->nome }}</div>
                        <div style="font-size:11px;color:var(--muted);">{{ $u->email ?? $u->username }}</div>
                    </div>

                    <span class="ruolo-badge ruolo-{{ $u->ruolo }}">{{ ucfirst($u->ruolo) }}</span>
                </div>
            @empty
                <div class="empty-state">Nessun utente registrato.</div>
            @endforelse
        </div>

        <div class="card">
            <div class="card-title">
                <i data-lucide="radio"></i>
                Dispositivi recenti
                <a href="{{ route('admin.dispositivi') }}" style="margin-left:auto;font-size:12px;color:var(--accent);font-weight:400;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                    Vedi tutti
                    <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                </a>
            </div>

            @forelse($dispositiviRecenti as $d)
                @php $paz = $d->paziente?->utente; @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#ede9fe,#dbeafe);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
                        <i data-lucide="pill"></i>
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:13px;">{{ $d->nome_dispositivo ?? $d->codice_seriale }}</div>
                        <div style="font-size:11px;color:var(--muted);">{{ $paz ? $paz->cognome.' '.$paz->nome : '—' }}</div>
                    </div>

                    @if($d->allarme_attivo)
                        <span class="dev-allarme">Allarme</span>
                    @elseif($d->stato === 'attivo')
                        <span class="dev-attivo">Online</span>
                    @else
                        <span class="dev-offline">{{ ucfirst($d->stato) }}</span>
                    @endif
                </div>
            @empty
                <div class="empty-state">Nessun dispositivo.</div>
            @endforelse
        </div>
    </div>
</main>
</body>
</html>
