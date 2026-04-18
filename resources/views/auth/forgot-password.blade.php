{{-- resources/views/auth/forgot-password.blade.php --}}
    <!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Password Dimenticata</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --bg:      #0b0f1a;
            --surface: #111827;
            --border:  #1f2d45;
            --accent:  #3b82f6;
            --accent2: #06b6d4;
            --green:   #10b981;
            --red:     #ef4444;
            --text:    #e2e8f0;
            --muted:   #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* ── Left panel ──────────────────────────────── */
        .left {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            overflow: hidden;
            background: var(--surface);
            border-right: 1px solid var(--border);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .22;
            animation: drift 9s ease-in-out infinite alternate;
        }
        .orb-1 { width: 360px; height: 360px; background: var(--accent2); top: -60px; left: -80px; }
        .orb-2 { width: 280px; height: 280px; background: var(--accent);  bottom: 60px; right: -50px; animation-delay: 3s; }
        .orb-3 { width: 180px; height: 180px; background: var(--green);   top: 45%; left: 60px; animation-delay: 1.5s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(18px, 22px) scale(1.07); }
        }

        .left-content { position: relative; z-index: 1; }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 56px;
        }

        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .left h1 {
            font-family: 'Syne', sans-serif;
            font-size: 38px;
            font-weight: 800;
            line-height: 1.18;
            margin-bottom: 18px;
        }

        .left h1 span {
            background: linear-gradient(90deg, var(--accent2), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .left p {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 360px;
            margin-bottom: 48px;
        }

        .steps { display: flex; flex-direction: column; gap: 14px; }

        .step {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 18px;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 12px;
            backdrop-filter: blur(8px);
            animation: fadeUp .5s ease both;
        }
        .step:nth-child(1) { animation-delay: .1s; }
        .step:nth-child(2) { animation-delay: .2s; }
        .step:nth-child(3) { animation-delay: .3s; }

        .step-ico {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .step-ico.cyan  { background: rgba(6,182,212,.15); }
        .step-ico.blue  { background: rgba(59,130,246,.15); }
        .step-ico.green { background: rgba(16,185,129,.15); }

        .step-text { font-size: 13px; color: var(--muted); }
        .step-text strong { display: block; color: var(--text); font-size: 13px; margin-bottom: 2px; }

        /* ── Right panel ─────────────────────────────── */
        .right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
        }

        .form-box {
            width: 100%;
            max-width: 400px;
            animation: fadeUp .5s ease both;
        }

        .form-box h2 {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .form-box .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 36px;
            line-height: 1.6;
        }

        /* Alert */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 10px;
            padding: 13px 15px;
            margin-bottom: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .alert-success {
            background: rgba(16,185,129,.1);
            border: 1px solid rgba(16,185,129,.3);
            color: #6ee7b7;
        }
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            color: #fca5a5;
            animation: shake .35s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-5px); }
            75%      { transform: translateX(5px); }
        }

        /* Form fields */
        .field { margin-bottom: 22px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .input-wrap { position: relative; }

        .input-wrap .ico {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            pointer-events: none;
            opacity: .5;
        }

        .input-wrap input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px 12px 42px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .input-wrap input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }

        .input-wrap input.is-invalid {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(239,68,68,.1);
        }

        .input-wrap input::placeholder { color: var(--muted); }

        .field-error {
            font-size: 11px;
            color: var(--red);
            margin-top: 5px;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(59,130,246,.35);
            letter-spacing: .3px;
            margin-bottom: 24px;
        }
        .btn-submit:hover  { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(59,130,246,.45); }
        .btn-submit:active { transform: translateY(0); }

        .back-link {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }
        .back-link a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }
        .back-link a:hover { text-decoration: underline; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            body { grid-template-columns: 1fr; }
            .left { display: none; }
            .right { padding: 40px 24px; }
        }
    </style>
</head>
<body>

{{-- ═══ LEFT PANEL ═══ --}}
<div class="left">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="left-content">
        <div class="brand">
            <span class="brand-name">PillMate</span>
        </div>

        <h1>Recupera il tuo<br><span>accesso</span></h1>
        <p>Inserisci la tua email e riceverai un link sicuro per reimpostare la password in pochi istanti.</p>

        <div class="steps">
            <div class="step">
                <div class="step-ico cyan">✉️</div>
                <div class="step-text">
                    <strong>Inserisci la tua email</strong>
                    <span>Quella con cui ti sei registrato</span>
                </div>
            </div>
            <div class="step">
                <div class="step-ico blue">🔗</div>
                <div class="step-text">
                    <strong>Controlla la casella</strong>
                    <span>Riceverai un link valido per 60 minuti</span>
                </div>
            </div>
            <div class="step">
                <div class="step-ico green">🔒</div>
                <div class="step-text">
                    <strong>Imposta la nuova password</strong>
                    <span>Torna ad accedere a PillMate</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ RIGHT PANEL ═══ --}}
<div class="right">
    <div class="form-box">
        <h2>Password dimenticata?</h2>
        <p class="subtitle">Nessun problema. Inserisci la tua email e<br>ti invieremo un link per reimpostarla.</p>

        {{-- Successo --}}
        @if (session('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Errore --}}
        @if ($errors->has('email'))
            <div class="alert alert-error">
                <span>⚠️</span>
                <span>{{ $errors->first('email') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <span class="ico">✉️</span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="la-tua-email@esempio.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                    />
                </div>
                @error('email')
                <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                📧 Invia link di recupero
            </button>
        </form>

        <div class="back-link">
            Ricordi la password? <a href="{{ route('login') }}">← Torna al login</a>
        </div>
    </div>
</div>

</body>
</html>
