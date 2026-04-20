{{-- resources/views/auth/first-access.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Primo accesso</title>
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
            background: linear-gradient(145deg, #059669 0%, #0891b2 100%);
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
        .orb-2 { width: 280px; height: 280px; background: #a7f3d0; bottom: 40px; right: -60px; animation-delay: 2s; }
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
            margin-bottom: 48px;
        }
        .brand-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
        }

        .left h1 {
            font-family: 'Syne', sans-serif;
            font-size: 34px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .left p {
            font-size: 15px;
            color: rgba(255,255,255,.8);
            line-height: 1.7;
            max-width: 360px;
            margin-bottom: 40px;
        }

        .info-list { display: flex; flex-direction: column; gap: 12px; }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            backdrop-filter: blur(8px);
            animation: fadeUp .5s ease both;
        }
        .info-item:nth-child(1) { animation-delay: .1s; }
        .info-item:nth-child(2) { animation-delay: .2s; }
        .info-item:nth-child(3) { animation-delay: .3s; }

        .info-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .info-text strong { display: block; font-weight: 600; font-size: 13px; margin-bottom: 2px; color: #fff; }
        .info-text span   { color: rgba(255,255,255,.7); font-size: 12px; line-height: 1.5; }

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
            margin-bottom: 30px;
        }

        .alert {
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
        }

        .input-wrap input::placeholder { color: #cbd5e1; }

        .toggle-pw {
            position: absolute;
            right: 10px;
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

        /* Strength bar */
        .pw-strength {
            height: 3px;
            border-radius: 3px;
            margin-top: 6px;
            background: var(--border);
            overflow: hidden;
        }
        .pw-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 3px;
            transition: width .3s, background .3s;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #059669, var(--accent2));
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(5,150,105,.3);
            margin-top: 8px;
            letter-spacing: .3px;
        }
        .btn-submit:hover  { opacity: .92; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        .back-link {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            margin-top: 20px;
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
            <div class="brand-icon">💊</div>
            <span class="brand-name">PillMate</span>
        </div>

        <h1>Benvenuto<br>su PillMate!</h1>
        <p>Il tuo account è stato creato dal tuo medico. Imposta la tua password personale per completare la registrazione.</p>

        <div class="info-list">
            <div class="info-item">
                <div class="info-icon">🔒</div>
                <div class="info-text">
                    <strong>Password sicura</strong>
                    <span>Usa almeno 8 caratteri, con lettere e numeri</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">✅</div>
                <div class="info-text">
                    <strong>Accesso immediato</strong>
                    <span>Dopo il salvataggio potrai accedere alla tua dashboard</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">🛡️</div>
                <div class="info-text">
                    <strong>Dati protetti</strong>
                    <span>La tua password è cifrata e non visibile a nessuno</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ RIGHT PANEL ═══ --}}
<div class="right">
    <div class="form-box">
        <h2>Imposta la password 🔑</h2>
        <p class="subtitle">Inserisci la tua email e scegli una password sicura</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('first-access.store') }}">
            @csrf

            <div class="field">
                <label for="email">La tua Email</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="mario.rossi@email.it"
                        value="{{ old('email', Auth::user()->email) }}"
                        autocomplete="email"
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                    />
                </div>
                @error('email') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">Nuova password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Almeno 8 caratteri"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        oninput="checkStrength(this.value)"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw('password','btn1')" id="btn1">👁</button>
                </div>
                <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
                @error('password') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Conferma password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Ripeti la password"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','btn2')" id="btn2">👁</button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Salva e accedi →</button>
        </form>

        <div class="back-link">
            Hai già una password? <a href="{{ route('login') }}">Torna al login</a>
        </div>
    </div>
</div>

<script>
    function togglePw(inputId, btnId) {
        const input = document.getElementById(inputId);
        const btn   = document.getElementById(btnId);
        input.type  = input.type === 'password' ? 'text' : 'password';
        btn.textContent = input.type === 'password' ? '👁' : '🙈';
    }

    function checkStrength(value) {
        const bar = document.getElementById('pw-bar');
        let score = 0;
        if (value.length >= 8)          score++;
        if (/[A-Z]/.test(value))        score++;
        if (/[0-9]/.test(value))        score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const colors = ['#ef4444', '#f97316', '#eab308', '#10b981'];
        const widths = ['25%', '50%', '75%', '100%'];

        bar.style.width      = score > 0 ? widths[score - 1] : '0';
        bar.style.background = score > 0 ? colors[score - 1] : 'transparent';
    }
</script>

</body>
</html>
