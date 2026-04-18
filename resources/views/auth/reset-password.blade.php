{{-- resources/views/auth/reset-password.blade.php --}}
    <!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Reimposta Password</title>
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
        .orb-1 { width: 340px; height: 340px; background: var(--green);  top: -60px; left: -70px; }
        .orb-2 { width: 260px; height: 260px; background: var(--accent); bottom: 50px; right: -50px; animation-delay: 2s; }
        .orb-3 { width: 200px; height: 200px; background: var(--accent2);top: 40%; left: 80px; animation-delay: 4s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(16px, 20px) scale(1.07); }
        }

        .left-content { position: relative; z-index: 1; }

        .brand { display: flex; align-items: center; gap: 14px; margin-bottom: 56px; }

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
            background: linear-gradient(90deg, var(--green), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .left p {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 360px;
            margin-bottom: 40px;
        }

        /* Password rules list */
        .pw-rules {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .pw-rule {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--muted);
            padding: 10px 16px;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            animation: fadeUp .5s ease both;
        }
        .pw-rule:nth-child(1) { animation-delay: .1s; }
        .pw-rule:nth-child(2) { animation-delay: .2s; }
        .pw-rule:nth-child(3) { animation-delay: .3s; }
        .pw-rule:nth-child(4) { animation-delay: .4s; }

        .pw-rule-ico {
            font-size: 15px;
            width: 28px; height: 28px;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(16,185,129,.15);
            flex-shrink: 0;
        }

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
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #fca5a5;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: shake .35s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-5px); }
            75%      { transform: translateX(5px); }
        }

        /* Fields */
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
            font-size: 15px;
            padding: 4px;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--text); }

        .field-error {
            font-size: 11px;
            color: var(--red);
            margin-top: 5px;
        }

        /* Password strength */
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
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(59, 130, 246, .35);
            letter-spacing: .3px;
            margin-bottom: 24px;
        }
        .btn-submit:hover  { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(16,185,129,.45); }
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

        <h1>Crea una<br><span>password sicura</span></h1>
        <p>Scegli una password forte per proteggere il tuo account e i dati dei tuoi pazienti.</p>


    </div>
</div>

{{-- ═══ RIGHT PANEL ═══ --}}
<div class="right">
    <div class="form-box">
        <h2>Reimposta la password</h2>
        <p class="subtitle">Inserisci la tua email e la nuova password<br>per riprendere l'accesso a PillMate.</p>

        {{-- Errore generale --}}
        @if ($errors->has('email') && !$errors->has('password'))
            <div class="alert-error">
                <span>{{ $errors->first('email') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- Token nascosto --}}
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Nuova password --}}
            <div class="field">
                <label for="password">Nuova password</label>
                <div class="input-wrap">
                    <span class="ico"></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Almeno 8 caratteri"
                        autocomplete="new-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        oninput="checkStrength(this.value)"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw('password','btn1')" id="btn1">👁</button>
                </div>
                <div class="pw-strength">
                    <div class="pw-strength-bar" id="pw-bar"></div>
                </div>
                @error('password')
                <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Conferma password --}}
            <div class="field">
                <label for="password_confirmation">Conferma nuova password</label>
                <div class="input-wrap">
                    <span class="ico"></span>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Ripeti la nuova password"
                        autocomplete="new-password"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','btn2')" id="btn2">👁</button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Salva nuova password</button>
        </form>

        <div class="back-link">
            <a href="{{ route('login') }}">← Torna al login</a>
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
        if (value.length >= 8)           score++;
        if (/[A-Z]/.test(value))         score++;
        if (/[0-9]/.test(value))         score++;
        if (/[^A-Za-z0-9]/.test(value))  score++;

        const colors = ['#ef4444', '#f97316', '#eab308', '#10b981'];
        const widths = ['25%', '50%', '75%', '100%'];

        bar.style.width      = score > 0 ? widths[score - 1] : '0';
        bar.style.background = score > 0 ? colors[score - 1] : 'transparent';
    }
</script>

</body>
</html>
