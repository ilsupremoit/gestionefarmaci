{{-- resources/views/auth/verify-email.blade.php --}}
    <!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Verifica email</title>
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
            --yellow:  #f59e0b;
            --text:    #e2e8f0;
            --muted:   #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        /* Background orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: .15;
            animation: drift 10s ease-in-out infinite alternate;
            pointer-events: none;
        }
        .orb-1 { width: 500px; height: 500px; background: var(--accent);  top: -150px; left: -100px; }
        .orb-2 { width: 400px; height: 400px; background: var(--accent2); bottom: -100px; right: -80px; animation-delay: 3s; }
        .orb-3 { width: 300px; height: 300px; background: var(--green);   top: 50%; left: 50%; transform: translate(-50%,-50%); animation-delay: 6s; }

        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(25px, 20px) scale(1.06); }
        }

        /* Card */
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px 44px;
            box-shadow: 0 24px 80px rgba(0,0,0,.4);
            animation: fadeUp .6s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Brand */
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 36px;
        }

        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Email icon */
        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59,130,246,.2), rgba(6,182,212,.2));
            border: 2px solid rgba(59,130,246,.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(59,130,246,.3); }
            50%       { box-shadow: 0 0 0 14px rgba(59,130,246,.0); }
        }

        /* Text */
        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--muted);
            text-align: center;
            line-height: 1.65;
            margin-bottom: 32px;
        }

        /* Alert success */
        .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(16,185,129,.1);
            border: 1px solid rgba(16,185,129,.3);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #6ee7b7;
            animation: fadeIn .3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* Steps */
        .steps {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 32px;
            padding: 20px;
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .step-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--muted);
        }

        .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
            color: #fff;
        }

        /* Button */
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
            position: relative;
        }
        .btn-submit:hover  { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(59,130,246,.45); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; transform: none; }

        /* Cooldown label */
        .cooldown-text {
            display: none;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            margin-top: 10px;
        }
        .cooldown-text.visible { display: block; }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: var(--muted);
            font-size: 12px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* Footer links */
        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .footer-links a {
            font-size: 13px;
            color: var(--muted);
            text-decoration: none;
            transition: color .2s;
        }
        .footer-links a:hover { color: var(--accent); }

        .logout-form button {
            background: none;
            border: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            color: var(--muted);
            cursor: pointer;
            transition: color .2s;
            padding: 0;
        }
        .logout-form button:hover { color: #fca5a5; }

        @media (max-width: 520px) {
            .card { padding: 36px 24px; }
        }
    </style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="card">

    {{-- Brand --}}
    <div class="brand">
        <span class="brand-name">PillMate</span>
    </div>

    {{-- Testo --}}
    <h1 class="card-title">Verifica la tua email</h1>
    <p class="card-subtitle">
        Abbiamo inviato un link di verifica al tuo indirizzo email.<br>
        Controlla la tua casella e clicca sul link per attivare l'account.
    </p>

    {{-- Messaggio di successo (es. dopo reinvio) --}}
    @if (session('success'))
        <div class="alert-success">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Passi da seguire --}}
    <div class="steps">
        <div class="step-row">
            <div class="step-num">1</div>
            <span>Apri la tua casella di posta elettronica</span>
        </div>
        <div class="step-row">
            <div class="step-num">2</div>
            <span>Trova l'email da <strong style="color:var(--text)">PillMate</strong> e aprila</span>
        </div>
        <div class="step-row">
            <div class="step-num">3</div>
            <span>Clicca sul bottone <strong style="color:var(--text)">Verifica email</strong> al suo interno</span>
        </div>
    </div>

    {{-- Reinvio email --}}
    <form method="POST" action="{{ route('verification.send') }}" id="resend-form">
        @csrf
        <button
            type="submit"
            class="btn-submit"
            id="resend-btn"
            onclick="startCooldown()"
        >
            Invia di nuovo la email
        </button>
        <p class="cooldown-text" id="cooldown-msg">Puoi richiedere un nuovo invio tra <span id="countdown">60</span>s</p>
    </form>

    <div class="divider">oppure</div>

    <div class="footer-links">
        <a href="{{ route('login') }}">← Torna al login</a>

        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit">Esci dall'account</button>
        </form>
    </div>

</div>

<script>
    let cooldownActive = false;

    function startCooldown() {
        if (cooldownActive) return;
        cooldownActive = true;

        const btn     = document.getElementById('resend-btn');
        const msg     = document.getElementById('cooldown-msg');
        const counter = document.getElementById('countdown');

        btn.disabled = true;
        msg.classList.add('visible');

        let seconds = 60;
        counter.textContent = seconds;

        const interval = setInterval(() => {
            seconds--;
            counter.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(interval);
                btn.disabled = false;
                msg.classList.remove('visible');
                cooldownActive = false;
            }
        }, 1000);
    }
</script>

</body>
</html>
