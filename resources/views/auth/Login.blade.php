{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Accedi</title>
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
            opacity: .25;
            animation: drift 8s ease-in-out infinite alternate;
        }
        .orb-1 { width: 380px; height: 380px; background: var(--accent);  top: -80px;  left: -80px; animation-delay: 0s; }
        .orb-2 { width: 300px; height: 300px; background: var(--accent2); bottom: 40px; right: -60px; animation-delay: 2s; }
        .orb-3 { width: 200px; height: 200px; background: var(--green);   bottom: 160px; left: 80px; animation-delay: 4s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(20px, 20px) scale(1.08); }
        }

        .left-content { position: relative; z-index: 1; }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 56px;
        }

        .brand-icon {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            box-shadow: 0 0 30px rgba(59,130,246,.4);
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
            font-size: 40px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 18px;
        }

        .left h1 span {
            background: linear-gradient(90deg, var(--accent), var(--accent2));
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

        .features { display: flex; flex-direction: column; gap: 14px; }

        .feat {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            backdrop-filter: blur(8px);
            animation: fadeUp .5s ease both;
        }
        .feat:nth-child(1) { animation-delay: .1s; }
        .feat:nth-child(2) { animation-delay: .2s; }
        .feat:nth-child(3) { animation-delay: .3s; }

        .feat-ico {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .feat-ico.blue  { background: rgba(59,130,246,.15); }
        .feat-ico.cyan  { background: rgba(6,182,212,.15); }
        .feat-ico.green { background: rgba(16,185,129,.15); }

        .feat-text strong { display: block; font-weight: 500; font-size: 13px; margin-bottom: 2px; }
        .feat-text span   { color: var(--muted); font-size: 12px; }

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
        }

        /* Alerts */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            color: #fca5a5;
            animation: shake .35s ease;
        }
        .alert-success {
            background: rgba(16,185,129,.1);
            border: 1px solid rgba(16,185,129,.3);
            color: #6ee7b7;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-5px); }
            75%      { transform: translateX(5px); }
        }

        /* Form fields */
        .field { margin-bottom: 20px; }

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

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--text); }

        .field-error {
            font-size: 11px;
            color: var(--red);
            margin-top: 5px;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--muted);
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: var(--accent);
            cursor: pointer;
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
        }
        .btn-submit:hover  { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(59,130,246,.45); }
        .btn-submit:active { transform: translateY(0); }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0 16px;
            color: var(--muted);
            font-size: 12px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .register-link {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
        }

        .register-link a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover { text-decoration: underline; }

        .roles {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .role-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 12px;
            color: var(--muted);
            background: rgba(255,255,255,.03);
        }
        .role-chip span { font-size: 14px; }

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
            <div class="brand-icon">💊</div>
            <span class="brand-name">PillMate</span>
        </div>

        <h1>Gestione terapie<br><span>intelligente e sicura</span></h1>
        <p>Monitoraggio in tempo reale delle somministrazioni, dispositivi IoT e comunicazione continua tra medici, pazienti e familiari.</p>

        <div class="features">
            <div class="feat">
                <div class="feat-ico blue">📡</div>
                <div class="feat-text">
                    <strong>Dispositivi IoT connessi</strong>
                    <span>Temperatura, umidità e batteria in tempo reale</span>
                </div>
            </div>
            <div class="feat">
                <div class="feat-ico cyan">📋</div>
                <div class="feat-text">
                    <strong>Terapie personalizzate</strong>
                    <span>Il medico imposta, il paziente viene guidato</span>
                </div>
            </div>
            <div class="feat">
                <div class="feat-ico green">🔔</div>
                <div class="feat-text">
                    <strong>Alert e notifiche</strong>
                    <span>Familiari sempre informati sulle assunzioni</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ RIGHT PANEL ═══ --}}
<div class="right">
    <div class="form-box">
        <h2>Bentornato</h2>
        <p class="subtitle">Accedi con le tue credenziali per continuare</p>

        {{-- Successo (es. dopo registrazione o logout) --}}
        @if (session('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Errore credenziali --}}
        @if ($errors->has('email') || session('error'))
            <div class="alert alert-error">
                <span>⚠️</span>
                <span>
                    @if (session('error'))
                        {{ session('error') }}
                    @else
                        {{ $errors->first('email') }}
                    @endif
                </span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="field">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <span class="ico">✉️</span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="nome@esempio.com"
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

            {{-- Password --}}
            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="ico">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw()" id="toggleBtn" title="Mostra/nascondi password">
                        👁
                    </button>
                </div>
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ricordami --}}
            <div class="form-row">
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Ricordami
                </label>
            </div>

            <button type="submit" class="btn-submit">Accedi a PillMate</button>
        </form>

        <div class="divider">sei nuovo?</div>

        <div class="register-link">
            Non hai un account? <a href="{{ route('register') }}">Registrati</a>
        </div>

        <div class="roles">
            <div class="role-chip"><span>👨‍⚕️</span> Medico</div>
            <div class="role-chip"><span>🧑‍🦯</span> Paziente</div>
            <div class="role-chip"><span>👨‍👩‍👧</span> Familiare</div>
            <div class="role-chip"><span>🛡️</span> Admin</div>
        </div>
    </div>
</div>

<script>
    function togglePw() {
        const input = document.getElementById('password');
        const btn   = document.getElementById('toggleBtn');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
    }
</script>

</body>
</html>
