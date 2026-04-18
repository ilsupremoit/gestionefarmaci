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
            animation: drift 8s ease-in-out infinite alternate;
        }
        .orb-1 { width: 360px; height: 360px; background: var(--accent);  top: -60px;  left: -60px; }
        .orb-2 { width: 280px; height: 280px; background: var(--green);   bottom: 60px; right: -40px; animation-delay: 2s; }
        .orb-3 { width: 180px; height: 180px; background: var(--accent2); bottom: 200px; left: 100px; animation-delay: 4s; }

        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(18px,18px) scale(1.07); }
        }

        .left-content { position: relative; z-index: 1; }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 52px;
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
            font-size: 36px;
            font-weight: 800;
            line-height: 1.2;
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
            margin-bottom: 44px;
        }

        /* Info list */
        .info-list { display: flex; flex-direction: column; gap: 14px; }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 18px;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
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
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .info-icon.blue  { background: rgba(59,130,246,.15); }
        .info-icon.green { background: rgba(16,185,129,.15); }
        .info-icon.cyan  { background: rgba(6,182,212,.15); }

        .info-text strong { display: block; font-weight: 500; font-size: 13px; margin-bottom: 2px; }
        .info-text span   { color: var(--muted); font-size: 12px; line-height: 1.5; }

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
            margin-bottom: 40px;
        }

        .form-box .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 32px;
        }

        /* Alerts */
        .alert {
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

        /* Fields */
        .section-sep {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            margin: 0 0 20px;
        }

        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-wrap .ico {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            pointer-events: none;
            opacity: .45;
        }

        .input-wrap input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 12px 11px 38px;
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

        /* Submit */
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
            margin-top: 8px;
            letter-spacing: .3px;
        }
        .btn-submit:hover  { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(59,130,246,.45); }
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
            <span class="brand-name">PillMate</span>
        </div>

        <h1>Benvenuto su<br><span>PillMate</span></h1>
        <p>Il tuo account è stato creato da un amministratore. Per completare l'accesso, scegli una nuova password personale.</p>

        <div class="info-list">
            <div class="info-item">
                <div class="info-icon blue">🔒</div>
                <div class="info-text">
                    <strong>Password sicura</strong>
                    <span>Usa almeno 8 caratteri, con lettere e numeri</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon green">✅</div>
                <div class="info-text">
                    <strong>Accesso immediato</strong>
                    <span>Dopo il salvataggio potrai accedere subito alla dashboard</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon cyan">🛡️</div>
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
        <h2>Imposta la password</h2>

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


            {{-- Email --}}
            <div class="field">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="la-tua@email.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                    />
                </div>
                @error('email') <div class="field-error">{{ $message }}</div> @enderror
            </div>


            {{-- Nuova password --}}
            <div class="field">
                <label for="password">Nuova password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        oninput="checkStrength(this.value)"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw('password','btn1')" id="btn1">👁</button>
                </div>
                <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
                @error('password') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            {{-- Conferma password --}}
            <div class="field">
                <label for="password_confirmation">Conferma password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','btn2')" id="btn2">👁</button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Salva e accedi</button>
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
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
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
