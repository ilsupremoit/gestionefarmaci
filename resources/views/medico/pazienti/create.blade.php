<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Nuovo paziente</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --bg: #0b0f1a;
            --surface: #111827;
            --border: #1f2d45;
            --accent: #3b82f6;
            --accent2: #06b6d4;
            --text: #e2e8f0;
            --muted: #64748b;
            --red: #ef4444;
            --green: #10b981;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 36px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }
        .title h1 {
            font-family: 'Syne', sans-serif;
            margin: 0 0 8px;
            font-size: 30px;
        }
        .title p {
            margin: 0;
            color: var(--muted);
        }
        .btn-back {
            text-decoration: none;
            color: var(--text);
            border: 1px solid var(--border);
            background: var(--surface);
            padding: 12px 16px;
            border-radius: 12px;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 24px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .field { display: flex; flex-direction: column; gap: 8px; }
        .field.full { grid-column: 1 / -1; }
        label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            font-weight: 700;
        }
        input, textarea {
            width: 100%;
            background: #0f172a;
            border: 1px solid var(--border);
            color: var(--text);
            padding: 14px 15px;
            border-radius: 12px;
            font: inherit;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 22px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            color: white;
            padding: 14px 18px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 18px;
            border: 1px solid;
        }
        .alert-error {
            background: rgba(239,68,68,.1);
            border-color: rgba(239,68,68,.3);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(16,185,129,.1);
            border-color: rgba(16,185,129,.3);
            color: #6ee7b7;
        }
        ul { margin: 0; padding-left: 18px; }
        @media (max-width: 700px) {
            .grid { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <div class="title">
            <h1>Nuovo paziente</h1>
            <p>Crea un account paziente con credenziali provvisorie.</p>
        </div>
        <a class="btn-back" href="{{ route('medico.dashboard') }}">← Torna alla dashboard</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Controlla questi campi:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('medico.pazienti.store') }}">
            @csrf

            <div class="grid">
                <div class="field">
                    <label for="nome">Nome</label>
                    <input id="nome" type="text" name="nome" value="{{ old('nome') }}" required>
                </div>

                <div class="field">
                    <label for="cognome">Cognome</label>
                    <input id="cognome" type="text" name="cognome" value="{{ old('cognome') }}" required>
                </div>

                <div class="field">
                    <label for="username">Nome utente</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required>
                </div>

                <div class="field">
                    <label for="password_temp">Password provvisoria</label>
                    <input id="password_temp" type="text" name="password_temp" value="{{ old('password_temp') }}" required>
                </div>

                <div class="field">
                    <label for="telefono">Telefono</label>
                    <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}">
                </div>

                <div class="field">
                    <label for="data_nascita">Data di nascita</label>
                    <input id="data_nascita" type="date" name="data_nascita" value="{{ old('data_nascita') }}">
                </div>

                <div class="field full">
                    <label for="indirizzo">Indirizzo</label>
                    <input id="indirizzo" type="text" name="indirizzo" value="{{ old('indirizzo') }}">
                </div>
            </div>

            <div class="actions">
                <a class="btn-back" href="{{ route('medico.dashboard') }}">Annulla</a>
                <button class="btn-primary" type="submit">Crea paziente</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
