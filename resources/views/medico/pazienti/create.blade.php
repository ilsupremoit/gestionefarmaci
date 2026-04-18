<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Nuovo paziente</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --bg: #0b0f1a; --surface: #111827; --border: #1f2d45;
            --accent: #3b82f6; --accent2: #06b6d4;
            --text: #e2e8f0; --muted: #64748b;
            --red: #ef4444; --green: #10b981;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar { width: 240px; flex-shrink: 0; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 28px 0; position: fixed; top: 0; left: 0; height: 100vh; }
        .brand { padding: 0 24px 28px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
        .brand-name { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; background: linear-gradient(135deg, #fff, var(--accent2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); padding: 0 24px; margin-bottom: 8px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 24px; font-size: 14px; color: var(--muted); text-decoration: none; transition: all .2s; }
        .nav-item:hover { color: var(--text); background: rgba(255,255,255,.04); }
        .nav-item.active { color: var(--accent); background: rgba(59,130,246,.08); border-right: 2px solid var(--accent); }
        .sidebar-footer { margin-top: auto; padding: 20px 24px 0; border-top: 1px solid var(--border); }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent2)); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; }
        .user-name { font-size: 13px; font-weight: 600; }
        .user-role { font-size: 11px; color: var(--muted); }
        .btn-logout { width: 100%; padding: 9px; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.2); border-radius: 8px; color: #fca5a5; font-size: 13px; cursor: pointer; transition: all .2s; font-family: inherit; }
        .btn-logout:hover { background: rgba(239,68,68,.2); }

        /* Main */
        .main { margin-left: 240px; flex: 1; padding: 36px 40px; }
        .topbar { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
        .title h1 { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 700; margin-bottom: 4px; }
        .title p { color: var(--muted); font-size: 14px; }

        .btn-back { text-decoration: none; color: var(--text); border: 1px solid var(--border); background: var(--surface); padding: 11px 16px; border-radius: 12px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all .2s; }
        .btn-back:hover { border-color: rgba(255,255,255,.2); }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 18px; padding: 28px; }

        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .field { display: flex; flex-direction: column; gap: 8px; }
        .field.full { grid-column: 1 / -1; }

        label { font-size: 11px; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); font-weight: 700; }
        .optional { font-weight: 400; text-transform: none; letter-spacing: 0; color: var(--muted); opacity: .6; font-size: 10px; margin-left: 4px; }

        input, textarea, select {
            width: 100%; background: #0f172a; border: 1px solid var(--border);
            color: var(--text); padding: 13px 15px; border-radius: 11px;
            font: inherit; font-size: 14px; outline: none; transition: border-color .2s;
        }
        input:focus, textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
        input::placeholder { color: var(--muted); }
        textarea { min-height: 90px; resize: vertical; }

        .section-label { font-family: 'Syne', sans-serif; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); grid-column: 1 / -1; margin-top: 8px; padding-top: 18px; border-top: 1px solid var(--border); }

        .hint { font-size: 11px; color: var(--muted); margin-top: -4px; }

        .actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); }
        .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent2)); border: none; color: white; padding: 13px 22px; border-radius: 12px; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; transition: opacity .2s; }
        .btn-primary:hover { opacity: .9; }

        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 20px; border: 1px solid; font-size: 13px; }
        .alert-error { background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.3); color: #fca5a5; }
        .alert-success { background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.3); color: #6ee7b7; }
        ul { margin: 6px 0 0; padding-left: 18px; }

        @media (max-width: 768px) { .sidebar { display:none; } .main { margin-left:0; padding:24px 16px; } }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } .field.full { grid-column: 1; } }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand"><span class="brand-name">PillMate</span></div>
    <div class="nav-label">Menu</div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">🏠 Dashboard</a>
    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">👥 I miei pazienti</a>
    <a class="nav-item" href="#">🔔 Notifiche</a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->nome }} {{ auth()->user()->cognome }}</div>
                <div class="user-role">👨‍⚕️ Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="title">
            <h1>Nuovo paziente</h1>
            <p>Crea un account paziente con credenziali provvisorie.</p>
        </div>
        <a class="btn-back" href="{{ route('medico.pazienti.index') }}">← Torna ai pazienti</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Controlla questi campi:</strong>
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('medico.pazienti.store') }}">
            @csrf

            <div class="grid">
                {{-- Dati anagrafici --}}
                <div class="section-label">📋 Dati anagrafici</div>

                <div class="field">
                    <label for="nome">Nome *</label>
                    <input id="nome" type="text" name="nome" value="{{ old('nome') }}" placeholder="es. Mario" required>
                </div>

                <div class="field">
                    <label for="cognome">Cognome *</label>
                    <input id="cognome" type="text" name="cognome" value="{{ old('cognome') }}" placeholder="es. Rossi" required>
                </div>

                <div class="field">
                    <label for="data_nascita">Data di nascita <span class="optional">(opzionale)</span></label>
                    <input id="data_nascita" type="date" name="data_nascita" value="{{ old('data_nascita') }}">
                </div>

                <div class="field">
                    <label for="telefono">Telefono <span class="optional">(opzionale)</span></label>
                    <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}" placeholder="es. 3331234567">
                </div>

                <div class="field">
                    <label for="email">Email <span class="optional">(opzionale)</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="es. mario.rossi@email.it">
                </div>

                <div class="field">
                    <label for="indirizzo">Indirizzo <span class="optional">(opzionale)</span></label>
                    <input id="indirizzo" type="text" name="indirizzo" value="{{ old('indirizzo') }}" placeholder="es. Via Roma 1, Milano">
                </div>

                <div class="field full">
                    <label for="note_mediche">Note mediche <span class="optional">(opzionale)</span></label>
                    <textarea id="note_mediche" name="note_mediche" placeholder="Allergie, patologie note, annotazioni...">{{ old('note_mediche') }}</textarea>
                </div>

                {{-- Credenziali accesso --}}
                <div class="section-label">🔐 Credenziali di accesso</div>

                <div class="field">
                    <label for="username">Nome utente (login) *</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="es. mario.rossi" required autocomplete="off">
                    <span class="hint">Il paziente userà questo per accedere.</span>
                </div>

                <div class="field">
                    <label for="password_temp">Password provvisoria *</label>
                    <input id="password_temp" type="text" name="password_temp" value="{{ old('password_temp') }}" placeholder="min. 6 caratteri" required autocomplete="off">
                    <span class="hint">Il paziente dovrà cambiarla al primo accesso.</span>
                </div>
            </div>

            <div class="actions">
                <a class="btn-back" href="{{ route('medico.pazienti.index') }}">Annulla</a>
                <button class="btn-primary" type="submit">✅ Crea paziente</button>
            </div>
        </form>
    </div>
</main>

</body>
</html>
