<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Messaggi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root{--bg:#0b0f1a;--surface:#111827;--border:#1f2d45;--accent:#3b82f6;--accent2:#06b6d4;--green:#10b981;--red:#ef4444;--yellow:#f59e0b;--text:#e2e8f0;--muted:#64748b;}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
        .sidebar{width:240px;flex-shrink:0;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:28px 0;position:fixed;top:0;left:0;height:100vh}
        .brand{padding:0 24px 28px;border-bottom:1px solid var(--border);margin-bottom:20px}
        .brand-name{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;background:linear-gradient(135deg,#fff,var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .nav-item{display:flex;align-items:center;gap:12px;padding:10px 24px;font-size:14px;color:var(--muted);text-decoration:none;transition:all .2s}
        .nav-item:hover{color:var(--text);background:rgba(255,255,255,.04)}
        .nav-item.active{color:var(--accent);background:rgba(59,130,246,.08);border-right:2px solid var(--accent)}
        .sidebar-footer{margin-top:auto;padding:20px 24px 0;border-top:1px solid var(--border)}
        .user-info{display:flex;align-items:center;gap:10px;margin-bottom:14px}
        .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700}
        .user-name{font-size:13px;font-weight:600}.user-role{font-size:11px;color:var(--muted)}
        .btn-logout{width:100%;padding:9px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;color:#fca5a5;font-size:13px;cursor:pointer;font-family:inherit}
        .main{margin-left:240px;flex:1;padding:36px 40px}
        .page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px}
        .page-header h1{font-family:'Syne',sans-serif;font-size:26px;font-weight:700}
        .page-header p{color:var(--muted);font-size:14px;margin-top:3px}
        .alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px}
        .alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#6ee7b7}
        .alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:20px}
        .card-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:16px}
        .field{margin-bottom:12px}
        .field label{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);font-weight:700;display:block;margin-bottom:5px}
        .field input,.field select,.field textarea{width:100%;background:#0f172a;border:1px solid var(--border);color:var(--text);padding:10px 13px;border-radius:9px;font:inherit;font-size:13px;outline:none;transition:border-color .2s}
        .field input:focus,.field select:focus,.field textarea:focus{border-color:var(--accent)}
        .field select option{background:#111827}
        .field textarea{min-height:90px;resize:vertical}
        .btn-primary{display:block;width:100%;padding:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;text-align:center}
        .msg-row{padding:12px 0;border-bottom:1px solid rgba(31,45,69,.5);display:flex;gap:12px}
        .msg-row:last-child{border-bottom:none}
        .msg-ico{font-size:20px;flex-shrink:0;margin-top:2px}
        .msg-title{font-weight:700;font-size:13px;margin-bottom:3px}
        .msg-meta{font-size:12px;color:var(--muted);margin-bottom:5px}
        .msg-body{font-size:13px;color:rgba(226,232,240,.75);background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:8px 10px;line-height:1.5}
        .msg-stato{font-size:11px;margin-top:5px}
        .letto{color:#6ee7b7}.non-letto{color:var(--yellow)}
        .unread-border{border-left:3px solid var(--accent);padding-left:10px}
        @media(max-width:768px){.sidebar{display:none}.main{margin-left:0;padding:20px 16px}}
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand"><span class="brand-name">PillMate</span></div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">🏠 Dashboard</a>
    <a class="nav-item" href="{{ route('medico.pazienti.index') }}">👥 I miei pazienti</a>
    <a class="nav-item active" href="{{ route('medico.notifiche') }}">🔔 Notifiche</a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($medico->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ $medico->nome }} {{ $medico->cognome }}</div>
                <div class="user-role">👨‍⚕️ Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1>📨 Messaggi</h1>
            <p>Invia e ricevi comunicazioni con pazienti, colleghi e amministratori</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <div style="display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;">

        {{-- FORM INVIO --}}
        <div class="card" style="position:sticky;top:24px;">
            <div class="card-title">✉️ Invia messaggio</div>
            <form method="POST" action="{{ route('medico.notifiche.invia') }}">
                @csrf
                <div class="field">
                    <label>Destinatario *</label>
                    <select name="id_utente" required>
                        <option value="">— Seleziona —</option>
                        @if($pazienti->count())
                        <optgroup label="🧑 I miei pazienti">
                            @foreach($pazienti as $p)
                                <option value="{{ $p->utente->id }}">{{ $p->utente->cognome }} {{ $p->utente->nome }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if($medici->count())
                        <optgroup label="👨‍⚕️ Altri medici">
                            @foreach($medici as $m)
                                <option value="{{ $m->id }}">Dr. {{ $m->cognome }} {{ $m->nome }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if($adminList->count())
                        <optgroup label="🛡️ Amministratori">
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
                        <option value="messaggio">💬 Messaggio</option>
                        <option value="info">ℹ️ Info</option>
                        <option value="promemoria">⏰ Promemoria</option>
                        <option value="allarme">🚨 Urgente</option>
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
                <button type="submit" class="btn-primary">📤 Invia</button>
            </form>
        </div>

        {{-- MESSAGGI --}}
        <div>
            {{-- RICEVUTI --}}
            <div class="card">
                <div class="card-title">📥 Ricevuti ({{ $ricevuti->total() }})</div>
                @forelse($ricevuti as $n)
                @php
                    $mitt = \App\Models\User::find($n->id_mittente);
                    $ico  = match($n->tipo ?? 'info') { 'allarme'=>'🚨','promemoria'=>'⏰','messaggio'=>'💬',default=>'ℹ️' };
                @endphp
                <div class="msg-row {{ $n->letta ? '' : 'unread-border' }}">
                    <div class="msg-ico">{{ $ico }}</div>
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px">
                            <div class="msg-title">{{ $n->titolo }}</div>
                            <div style="font-size:11px;color:var(--muted)">{{ \Carbon\Carbon::parse($n->data_invio)->format('d/m H:i') }}</div>
                        </div>
                        <div class="msg-meta">Da: <strong>{{ $mitt ? $mitt->cognome.' '.$mitt->nome.' ('.ucfirst($mitt->ruolo).')' : 'Sistema' }}</strong></div>
                        <div class="msg-body">{{ $n->messaggio }}</div>
                        <div class="msg-stato">
                            <span class="{{ $n->letta ? 'letto' : 'non-letto' }}">{{ $n->letta ? '✓ Letto' : '● Nuovo' }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;color:var(--muted);padding:24px;font-size:13px;">Nessun messaggio ricevuto.</div>
                @endforelse
                <div style="margin-top:12px;">{{ $ricevuti->links('pagination::simple-tailwind') }}</div>
            </div>

            {{-- INVIATI --}}
            <div class="card">
                <div class="card-title">📤 Inviati ({{ $inviati->total() }})</div>
                @forelse($inviati as $n)
                @php
                    $dest = \App\Models\User::find($n->id_utente);
                    $ico  = match($n->tipo ?? 'info') { 'allarme'=>'🚨','promemoria'=>'⏰','messaggio'=>'💬',default=>'ℹ️' };
                @endphp
                <div class="msg-row">
                    <div class="msg-ico">{{ $ico }}</div>
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px">
                            <div class="msg-title">{{ $n->titolo }}</div>
                            <div style="font-size:11px;color:var(--muted)">{{ \Carbon\Carbon::parse($n->data_invio)->format('d/m H:i') }}</div>
                        </div>
                        <div class="msg-meta">A: <strong>{{ $dest ? $dest->cognome.' '.$dest->nome.' ('.ucfirst($dest->ruolo).')' : '?' }}</strong></div>
                        <div class="msg-body" style="color:rgba(226,232,240,.6)">{{ \Illuminate\Support\Str::limit($n->messaggio,120) }}</div>
                        <div class="msg-stato">
                            <span class="{{ $n->letta ? 'letto' : 'non-letto' }}">{{ $n->letta ? '✓ Letto' : '● In attesa di lettura' }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;color:var(--muted);padding:24px;font-size:13px;">Nessun messaggio inviato.</div>
                @endforelse
                <div style="margin-top:12px;">{{ $inviati->links('pagination::simple-tailwind') }}</div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
