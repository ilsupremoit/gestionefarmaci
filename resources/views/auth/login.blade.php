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
            --bg:      #f0f7ff;
            --surface: #ffffff;
            --border:  #dde8f5;
            --accent:  #2563eb;
            --accent2: #0891b2;
            --green:   #059669;
            --red:     #dc2626;
            --text:    #1e293b;
            --muted:   #64748b;
            --shadow:  0 2px 16px rgba(37,99,235,.10);
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
            background: linear-gradient(145deg, #1d4ed8 0%, #0891b2 100%);
            color: #fff;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: .18;
            animation: drift 8s ease-in-out infinite alternate;
        }
        .orb-1 { width: 360px; height: 360px; background: #fff; top: -80px; left: -80px; }
        .orb-2 { width: 280px; height: 280px; background: #bfdbfe; bottom: 40px; right: -60px; animation-delay: 2s; }
        .orb-3 { width: 180px; height: 180px; background: #67e8f9; bottom: 200px; left: 80px; animation-delay: 4s; }

        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(18px,18px) scale(1.07); }
        }

        .left-content { position: relative; z-index: 1; }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 52px;
        }

        .brand-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            backdrop-filter: blur(8px);
        }

        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
        }

        .left h1 {
            font-family: 'Syne', sans-serif;
            font-size: 38px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            color: #fff;
        }

        .left p {
            font-size: 15px;
            color: rgba(255,255,255,.8);
            line-height: 1.7;
            max-width: 360px;
            margin-bottom: 44px;
        }

        .features { display: flex; flex-direction: column; gap: 12px; }

        .feat {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            font-size: 13px;
            backdrop-filter: blur(8px);
            animation: fadeUp .5s ease both;
            color: #fff;
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
            background: rgba(255,255,255,.2);
        }

        .feat-text strong { display: block; font-weight: 600; font-size: 13px; margin-bottom: 2px; }
        .feat-text span   { color: rgba(255,255,255,.7); font-size: 12px; }

        /* ── Right panel ─────────────────────────────── */
        .right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            background: var(--bg);
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
            margin-bottom: 8px;
            color: var(--text);
        }

        .form-box .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 32px;
        }

        /* Alerts */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            animation: shake .35s ease;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-4px); }
            75%      { transform: translateX(4px); }
        }

        /* Form fields */
        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--muted);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-wrap input {
            width: 100%;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            box-shadow: var(--shadow);
        }

        .input-wrap input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        .input-wrap input.is-invalid {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(220,38,38,.08);
        }

        .input-wrap input::placeholder { color: #cbd5e1; }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 15px;
            padding: 4px;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--text); }

        .field-error {
            font-size: 11px;
            color: var(--red);
            margin-top: 4px;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
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
            box-shadow: 0 4px 16px rgba(37,99,235,.3);
            letter-spacing: .3px;
        }
        .btn-submit:hover  { opacity: .92; transform: translateY(-1px); box-shadow: 0 6px 22px rgba(37,99,235,.35); }
        .btn-submit:active { transform: translateY(0); }

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

        <h1>Gestione terapie<br>semplice e sicura</h1>
        <p>Monitoraggio in tempo reale delle somministrazioni, dispositivi IoT e comunicazione continua tra medici, pazienti e familiari.</p>

        <div class="features">
            <div class="feat">
                <div class="feat-ico">📡</div>
                <div class="feat-text">
                    <strong>Dispositivi IoT connessi</strong>
                    <span>Temperatura, umidità e batteria in tempo reale</span>
                </div>
            </div>
            <div class="feat">
                <div class="feat-ico">📋</div>
                <div class="feat-text">
                    <strong>Terapie personalizzate</strong>
                    <span>Il medico imposta, il paziente viene guidato</span>
                </div>
            </div>
            <div class="feat">
                <div class="feat-ico">🔔</div>
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
        <h2>Bentornato 👋</h2>
        <p class="subtitle">Accedi al tuo account PillMate</p>

        @if (session('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->has('email') || $errors->has('login') || session('error'))
            <div class="alert alert-error">
                <span>⚠️</span>
                <span>
                    @if (session('error'))
                        {{ session('error') }}
                    @elseif($errors->has('login'))
                        {{ $errors->first('login') }}
                    @else
                        {{ $errors->first('email') }}
                    @endif
                </span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="login">Nome utente o email</label>
                <div class="input-wrap">
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="mario.rossi o mario@email.it"
                        autocomplete="username"
                        required
                    />
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw()" id="toggleBtn" title="Mostra/nascondi">👁</button>
                </div>
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Ricordami
                </label>
                <a href="{{ route('password.request') }}" style="font-size:13px; color:var(--accent); text-decoration:none; font-weight:600;">
                    Password dimenticata?
                </a>
            </div>

            <button type="submit" class="btn-submit">Accedi a PillMate →</button>
        </form>
    </div>
</div>

<script>
    function togglePw() {
        const input = document.getElementById('password');
        const btn   = document.getElementById('toggleBtn');
        input.type  = input.type === 'password' ? 'text' : 'password';
        btn.textContent = input.type === 'password' ? '👁' : '🙈';
    }
</script>

</body>
</html>
