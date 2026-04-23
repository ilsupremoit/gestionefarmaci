<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Messaggi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/medico/notificheMedico.css')
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="pill"></i>
        </div>
        <span class="brand-name">PillMate</span>
    </div>

    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>
        Dashboard
    </a>

    <a class="nav-item" href="{{ route('medico.pazienti.index') }}">
        <span class="ico"><i data-lucide="users"></i></span>
        I miei pazienti
    </a>

    <a class="nav-item active" href="{{ route('medico.notifiche') }}">
        <span class="ico"><i data-lucide="bell"></i></span>
        Notifiche
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($medico->nome,0,1)) }}</div>
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
            <h1>
                <i data-lucide="mail"></i>
                Messaggi
            </h1>
            <p>Invia e ricevi comunicazioni con pazienti, colleghi e amministratori</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i data-lucide="circle-alert"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="layout-grid" style="display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;">

        <div class="card" style="position:sticky;top:24px;">
            <div class="card-title">
                <i data-lucide="send-horizontal"></i>
                Invia messaggio
            </div>

            <form method="POST" action="{{ route('medico.notifiche.invia') }}">
                @csrf

                <div class="field">
                    <label>Destinatario *</label>
                    <select name="id_utente" required>
                        <option value="">— Seleziona —</option>

                        @if($pazienti->count())
                            <optgroup label="Pazienti">
                                @foreach($pazienti as $p)
                                    <option value="{{ $p->utente->id }}">{{ $p->utente->cognome }} {{ $p->utente->nome }}</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if($medici->count())
                            <optgroup label="Medici">
                                @foreach($medici as $m)
                                    <option value="{{ $m->id }}">Dr. {{ $m->cognome }} {{ $m->nome }}</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if($adminList->count())
                            <optgroup label="Amministratori">
                                @foreach($adminList as $a)
                                    <option value="{{ $a->id }}">{{ $a->cognome }} {{ $a->nome }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div class="field">
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="messaggio">Messaggio</option>
                        <option value="info">Info</option>
                        <option value="promemoria">Promemoria</option>
                        <option value="allarme">Urgente</option>
                    </select>
                </div>

                <div class="field">
                    <label>Oggetto *</label>
                    <input type="text" name="titolo" required maxlength="100" placeholder="Oggetto del messaggio"/>
                </div>

                <div class="field">
                    <label>Messaggio *</label>
                    <textarea name="messaggio" required placeholder="Scrivi qui..."></textarea>
                </div>

                <button type="submit" class="btn-primary">
                    <i data-lucide="send"></i>
                    Invia
                </button>
            </form>
        </div>

        <div>
            <div class="card">
                <div class="card-title">
                    <i data-lucide="inbox"></i>
                    Ricevuti ({{ $ricevuti->total() }})
                </div>

                @forelse($ricevuti as $n)
                    @php
                        $mitt = \App\Models\User::find($n->id_mittente);

                        $iconName = match($n->tipo ?? 'info') {
                            'allarme' => 'triangle-alert',
                            'promemoria' => 'clock-3',
                            'messaggio' => 'message-square',
                            default => 'info'
                        };
                    @endphp

                    <div class="msg-row {{ $n->letta ? '' : 'unread-border' }}">
                        <div class="msg-ico">
                            <i data-lucide="{{ $iconName }}"></i>
                        </div>

                        <div style="flex:1;min-width:0">
                            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px">
                                <div class="msg-title">{{ $n->titolo }}</div>
                                <div style="font-size:11px;color:var(--muted)">{{ \Carbon\Carbon::parse($n->data_invio)->format('d/m H:i') }}</div>
                            </div>

                            <div class="msg-meta">
                                Da: <strong>{{ $mitt ? $mitt->cognome.' '.$mitt->nome.' ('.ucfirst($mitt->ruolo).')' : 'Sistema' }}</strong>
                            </div>

                            <div class="msg-body">{{ $n->messaggio }}</div>

                            <div class="msg-stato">
                                <span class="status-dot" style="background:{{ $n->letta ? '#10b981' : '#f59e0b' }}"></span>
                                <span class="{{ $n->letta ? 'letto' : 'non-letto' }}">{{ $n->letta ? 'Letto' : 'Nuovo' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Nessun messaggio ricevuto.</div>
                @endforelse

                <div style="margin-top:12px;">{{ $ricevuti->links('pagination::simple-tailwind') }}</div>
            </div>

            <div class="card">
                <div class="card-title">
                    <i data-lucide="send"></i>
                    Inviati ({{ $inviati->total() }})
                </div>

                @forelse($inviati as $n)
                    @php
                        $dest = \App\Models\User::find($n->id_utente);

                        $iconName = match($n->tipo ?? 'info') {
                            'allarme' => 'triangle-alert',
                            'promemoria' => 'clock-3',
                            'messaggio' => 'message-square',
                            default => 'info'
                        };
                    @endphp

                    <div class="msg-row">
                        <div class="msg-ico">
                            <i data-lucide="{{ $iconName }}"></i>
                        </div>

                        <div style="flex:1;min-width:0">
                            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px">
                                <div class="msg-title">{{ $n->titolo }}</div>
                                <div style="font-size:11px;color:var(--muted)">{{ \Carbon\Carbon::parse($n->data_invio)->format('d/m H:i') }}</div>
                            </div>

                            <div class="msg-meta">
                                A: <strong>{{ $dest ? $dest->cognome.' '.$dest->nome.' ('.ucfirst($dest->ruolo).')' : '?' }}</strong>
                            </div>

                            <div class="msg-body" style="color:rgba(30,41,59,.68)">{{ \Illuminate\Support\Str::limit($n->messaggio,120) }}</div>

                            <div class="msg-stato">
                                <span class="status-dot" style="background:{{ $n->letta ? '#10b981' : '#f59e0b' }}"></span>
                                <span class="{{ $n->letta ? 'letto' : 'non-letto' }}">{{ $n->letta ? 'Letto' : 'In attesa di lettura' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Nessun messaggio inviato.</div>
                @endforelse

                <div style="margin-top:12px;">{{ $inviati->links('pagination::simple-tailwind') }}</div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
