{{-- resources/views/medico/notifiche.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Notifiche</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --bg:      #f0f7ff;
            --surface: #ffffff;
            --border:  #dde8f5;
            --accent:  #2563eb;
            --accent2: #0891b2;
            --green:   #059669;
            --red:     #dc2626;
            --yellow:  #d97706;
            --text:    #1e293b;
            --muted:   #64748b;
            --shadow:  0 2px 12px rgba(37,99,235,.08);
            --shadow-md: 0 4px 20px rgba(37,99,235,.12);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* ── Sidebar ── */
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            box-shadow: 2px 0 12px rgba(0,0,0,.04);
            display: flex; flex-direction: column;
            padding: 28px 0;
            position: fixed; top: 0; left: 0; height: 100vh;
        }
        .brand { display: flex; align-items: center; gap: 10px; padding: 0 24px 24px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
        .brand-icon { width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), var(--accent2)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; }
        .brand-name { font-family: 'Syne', sans-serif; font-size: 19px; font-weight: 800; color: var(--text); }
        .nav-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; padding: 0 24px; margin-bottom: 6px; }
        .nav-item { display: flex; align-items: center; gap: 11px; padding: 10px 24px; font-size: 14px; color: var(--muted); text-decoration: none; transition: all .18s; }
        .nav-item:hover { color: var(--text); background: #f1f5f9; }
        .nav-item.active { color: var(--accent); background: #eff6ff; border-right: 3px solid var(--accent); font-weight: 600; }
        .nav-item .ico { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer { margin-top: auto; padding: 20px 24px 0; border-top: 1px solid var(--border); }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent2)); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #fff; }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text); }
        .user-role { font-size: 11px; color: var(--muted); }
        .btn-logout { width: 100%; padding: 9px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #b91c1c; font-size: 13px; cursor: pointer; transition: all .2s; font-family: inherit; font-weight: 600; }
        .btn-logout:hover { background: #fee2e2; }

        /* ── Main ── */
        .main { margin-left: 240px; flex: 1; padding: 36px 40px; }

        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .page-header p { color: var(--muted); font-size: 14px; }

        /* ── Alerts ── */
        .alert { padding: 13px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; border: 1px solid; }
        .alert-success { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .alert-error   { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

        /* ── Grid layout ── */
        .layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
        }
        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px; font-weight: 700;
            margin-bottom: 20px;
            color: var(--text);
            display: flex; align-items: center; gap: 8px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        /* ── Nuovo messaggio form ── */
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--muted); margin-bottom: 6px; }

        .field select,
        .field input,
        .field textarea {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 11px 13px;
            color: var(--text);
            font: inherit; font-size: 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .field select:focus,
        .field input:focus,
        .field textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            background: #fff;
        }
        .field select option { background: #fff; color: var(--text); }
        .field textarea { min-height: 100px; resize: vertical; }

        .badge-tipo {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .tipo-info      { background: #dbeafe; color: #1d4ed8; }
        .tipo-promemoria{ background: #fef3c7; color: #92400e; }
        .tipo-allarme   { background: #fee2e2; color: #991b1b; }
        .tipo-messaggio { background: #f3e8ff; color: #6b21a8; }
        .tipo-errore    { background: #ffe4e6; color: #9f1239; }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none; border-radius: 10px;
            color: #fff; font-family: 'Syne', sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 14px rgba(37,99,235,.25);
            margin-top: 4px;
        }
        .btn-submit:hover { opacity: .92; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        /* ── Messaggi list ── */
        .tabs { display: flex; gap: 4px; margin-bottom: 18px; }
        .tab {
            flex: 1; text-align: center;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: 1.5px solid var(--border);
            color: var(--muted); background: #f8fafc;
            transition: all .18s; text-decoration: none;
        }
        .tab.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .tab:hover:not(.active) { background: #f1f5f9; color: var(--text); }

        .msg-list { display: flex; flex-direction: column; gap: 10px; }

        .msg-item {
            padding: 14px 16px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: #fafcff;
            transition: box-shadow .18s, border-color .18s;
        }
        .msg-item:hover { box-shadow: var(--shadow); border-color: #bdd3f5; }
        .msg-item.non-letta { border-left: 4px solid var(--accent); background: #f0f7ff; }

        .msg-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 6px; }
        .msg-titolo { font-size: 14px; font-weight: 600; color: var(--text); }
        .msg-meta   { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .msg-data   { font-size: 11px; color: var(--muted); }
        .msg-body   { font-size: 13px; color: var(--muted); line-height: 1.6; }
        .msg-from   { font-size: 11px; color: var(--muted); margin-top: 6px; display: flex; align-items: center; gap: 5px; }

        .empty-msg {
            text-align: center; padding: 32px 20px;
            color: var(--muted); font-size: 13px;
        }
        .empty-msg .empty-icon { font-size: 36px; margin-bottom: 10px; }

        /* Pagination */
        .pagi { display: flex; gap: 6px; margin-top: 16px; flex-wrap: wrap; }
        .pagi a, .pagi span {
            padding: 6px 12px; border-radius: 8px; font-size: 13px;
            border: 1.5px solid var(--border); text-decoration: none;
            color: var(--muted); background: var(--surface);
            transition: all .18s;
        }
        .pagi a:hover { border-color: var(--accent); color: var(--accent); }
        .pagi .current { background: var(--accent); color: #fff; border-color: var(--accent); font-weight: 600; }
        .pagi .disabled { opacity: .4; cursor: default; }

        @media (max-width: 1024px) { .layout { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { display: none; } .main { margin-left: 0; padding: 24px 16px; } }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">💊</div>
        <span class="brand-name">PillMate</span>
    </div>

    <div class="nav-label">Menu</div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <span class="ico">🏠</span> Dashboard
    </a>
    <a class="nav-item" href="{{ route('medico.pazienti.index') }}">
        <span class="ico">👥</span> I miei pazienti
    </a>
    <a class="nav-item active" href="{{ route('medico.notifiche') }}">
        <span class="ico">🔔</span> Notifiche
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($medico->nome, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ $medico->nome }} {{ $medico->cognome }}</div>
                <div class="user-role">👨‍⚕️ Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>

<main class="main">

    <div class="page-header">
        <h1>🔔 Notifiche e Messaggi</h1>
        <p>Invia comunicazioni ai pazienti e visualizza i messaggi ricevuti.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">⚠️ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $e) <div>⚠️ {{ $e }}</div> @endforeach
        </div>
    @endif

    <div class="layout">

        {{-- ══ COLONNA SINISTRA: form invio + messaggi inviati ══ --}}
        <div>
            {{-- Form nuovo messaggio --}}
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-title">✉️ Invia messaggio a un paziente</div>

                <form method="POST" action="{{ route('medico.notifiche.invia') }}">
                    @csrf

                    <div class="field">
                        <label for="id_utente">Paziente destinatario *</label>
                        <select id="id_utente" name="id_utente" required>
                            <option value="">— Seleziona un paziente —</option>
                            @foreach($pazienti as $paz)
                                <option value="{{ $paz->utente->id }}" {{ old('id_utente') == $paz->utente->id ? 'selected' : '' }}>
                                    {{ $paz->utente->cognome }} {{ $paz->utente->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="tipo">Tipo notifica *</label>
                        <select id="tipo" name="tipo" required>
                            <option value="info"       {{ old('tipo','info') === 'info'       ? 'selected' : '' }}>ℹ️ Informazione</option>
                            <option value="promemoria" {{ old('tipo') === 'promemoria' ? 'selected' : '' }}>📅 Promemoria</option>
                            <option value="allarme"    {{ old('tipo') === 'allarme'    ? 'selected' : '' }}>🚨 Allarme</option>
                            <option value="messaggio"  {{ old('tipo') === 'messaggio'  ? 'selected' : '' }}>💬 Messaggio</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="titolo">Oggetto *</label>
                        <input
                            type="text"
                            id="titolo"
                            name="titolo"
                            value="{{ old('titolo') }}"
                            placeholder="es. Promemoria visita di controllo"
                            maxlength="100"
                            required
                        />
                    </div>

                    <div class="field">
                        <label for="messaggio">Testo del messaggio *</label>
                        <textarea
                            id="messaggio"
                            name="messaggio"
                            placeholder="Scrivi il messaggio per il paziente..."
                            required
                        >{{ old('messaggio') }}</textarea>
                    </div>

                    <button type="submit" class="btn-submit">📤 Invia messaggio</button>
                </form>
            </div>

            {{-- Messaggi inviati --}}
            <div class="card">
                <div class="card-title">📤 Messaggi inviati <span style="font-size:12px; font-weight:400; color:var(--muted); margin-left:auto;">({{ $inviati->total() }} totali)</span></div>

                @if($inviati->isEmpty())
                    <div class="empty-msg">
                        <div class="empty-icon">📭</div>
                        Nessun messaggio inviato ancora.
                    </div>
                @else
                    <div class="msg-list">
                        @foreach($inviati as $msg)
                            <div class="msg-item">
                                <div class="msg-header">
                                    <span class="msg-titolo">{{ $msg->titolo }}</span>
                                    <div class="msg-meta">
                                        <span class="badge-tipo tipo-{{ $msg->tipo }}">{{ $msg->tipo }}</span>
                                        <span class="msg-data">{{ \Carbon\Carbon::parse($msg->data_invio)->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                                <div class="msg-body">{{ Str::limit($msg->messaggio, 120) }}</div>
                                <div class="msg-from">
                                    📬 A: utente #{{ $msg->id_utente }}
                                    &nbsp;·&nbsp;
                                    {{ $msg->letta ? '✅ Letto' : '⏳ Non ancora letto' }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($inviati->hasPages())
                        <div class="pagi">
                            @if(!$inviati->onFirstPage())
                                <a href="{{ $inviati->appends(request()->except('inviati'))->previousPageUrl() }}">‹</a>
                            @endif
                            @foreach($inviati->getUrlRange(1, $inviati->lastPage()) as $page => $url)
                                @if($page == $inviati->currentPage())
                                    <span class="current">{{ $page }}</span>
                                @else
                                    <a href="{{ $inviati->appends(request()->except('inviati'))->url($page) }}">{{ $page }}</a>
                                @endif
                            @endforeach
                            @if($inviati->hasMorePages())
                                <a href="{{ $inviati->appends(request()->except('inviati'))->nextPageUrl() }}">›</a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- ══ COLONNA DESTRA: messaggi ricevuti ══ --}}
        <div class="card">
            <div class="card-title">
                📥 Messaggi ricevuti
                <span style="font-size:12px; font-weight:400; color:var(--muted); margin-left:auto;">({{ $ricevuti->total() }} totali)</span>
            </div>

            @if($ricevuti->isEmpty())
                <div class="empty-msg">
                    <div class="empty-icon">📬</div>
                    Nessun messaggio ricevuto.
                    <div style="margin-top: 8px; font-size: 12px;">Le notifiche dei tuoi pazienti appariranno qui.</div>
                </div>
            @else
                <div class="msg-list">
                    @foreach($ricevuti as $msg)
                        <div class="msg-item {{ !$msg->letta ? 'non-letta' : '' }}">
                            <div class="msg-header">
                                <span class="msg-titolo">
                                    @if(!$msg->letta)
                                        <span style="display:inline-block; width:8px; height:8px; background:var(--accent); border-radius:50%; margin-right:6px;"></span>
                                    @endif
                                    {{ $msg->titolo }}
                                </span>
                                <div class="msg-meta">
                                    <span class="badge-tipo tipo-{{ $msg->tipo }}">{{ $msg->tipo }}</span>
                                    <span class="msg-data">{{ \Carbon\Carbon::parse($msg->data_invio)->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            <div class="msg-body">{{ Str::limit($msg->messaggio, 150) }}</div>
                            @if($msg->id_mittente)
                                <div class="msg-from">👤 Da: utente #{{ $msg->id_mittente }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($ricevuti->hasPages())
                    <div class="pagi">
                        @if(!$ricevuti->onFirstPage())
                            <a href="{{ $ricevuti->appends(request()->except('ricevuti'))->previousPageUrl() }}">‹</a>
                        @endif
                        @foreach($ricevuti->getUrlRange(1, $ricevuti->lastPage()) as $page => $url)
                            @if($page == $ricevuti->currentPage())
                                <span class="current">{{ $page }}</span>
                            @else
                                <a href="{{ $ricevuti->appends(request()->except('ricevuti'))->url($page) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if($ricevuti->hasMorePages())
                            <a href="{{ $ricevuti->appends(request()->except('ricevuti'))->nextPageUrl() }}">›</a>
                        @endif
                    </div>
                @endif
            @endif
        </div>

    </div>{{-- /layout --}}
</main>

</body>
</html>
