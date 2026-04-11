{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Registrazione</title>
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
        .orb-1 { width: 380px; height: 380px; background: var(--green);  top: -80px;  left: -80px; }
        .orb-2 { width: 300px; height: 300px; background: var(--accent); bottom: 40px; right: -60px; animation-delay: 2s; }
        .orb-3 { width: 200px; height: 200px; background: var(--accent2); bottom: 160px; left: 80px; animation-delay: 4s; }

        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(20px,20px) scale(1.08); }
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
            font-size: 36px;
            font-weight: 800;
            line-height: 1.2;
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
        .step:nth-child(4) { animation-delay: .4s; }

        .step-num {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .step-text { font-size: 13px; color: var(--muted); }
        .step-text strong { display: block; color: var(--text); font-size: 13px; margin-bottom: 2px; }

        /* ── Right panel ─────────────────────────────── */
        .right {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 48px;
            overflow-y: auto;
        }

        .form-box {
            width: 100%;
            max-width: 440px;
            animation: fadeUp .5s ease both;
            padding: 8px 0;
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
            margin-bottom: 32px;
        }

        /* Alert errore generale */
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #fca5a5;
        }

        /* Griglia a 2 colonne */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 16px;
        }

        /* Separatore sezione */
        .section-sep {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            margin: 24px 0 20px;
        }

        /* Form fields */
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

        .field label .opt {
            font-weight: 400;
            font-size: 10px;
            text-transform: none;
            letter-spacing: 0;
            margin-left: 4px;
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

        .input-wrap input,
        .input-wrap select {
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
            appearance: none;
        }

        .input-wrap input:focus,
        .input-wrap select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }

        .input-wrap input.is-invalid,
        .input-wrap select.is-invalid {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(239,68,68,.1);
        }

        .input-wrap input::placeholder { color: var(--muted); }

        /* select arrow */
        .input-wrap select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        .input-wrap select option { background: #1e293b; color: var(--text); }

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

        /* Paziente extra fields — nascosti di default */
        #paziente-fields { display: none; }
        #paziente-fields.visible { display: block; }

        /* Password strength bar */
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
        }
        .btn-submit:hover  { opacity: .9; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }

        .login-link {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            margin-top: 20px;
        }
        .login-link a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }
        .login-link a:hover { text-decoration: underline; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            body { grid-template-columns: 1fr; }
            .left { display: none; }
            .right { padding: 40px 24px; }
            .grid-2 { grid-template-columns: 1fr; }
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

        <h1>Unisciti a<br><span>PillMate</span></h1>
        <p>Crea il tuo account in pochi secondi e inizia a gestire le terapie in modo sicuro e intelligente.</p>

        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">
                    <strong>Scegli il tuo ruolo</strong>
                    <span>Paziente, medico o familiare</span>
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">
                    <strong>Inserisci i tuoi dati</strong>
                    <span>Nome, email e password sicura</span>
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">
                    <strong>Accedi alla dashboard</strong>
                    <span>Inizia a usare PillMate subito</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ RIGHT PANEL ═══ --}}
<div class="right">
    <div class="form-box">
        <h2>Crea il tuo account</h2>
        <p class="subtitle">Compila il modulo per registrarti a PillMate</p>

        {{-- Errori generici --}}
        @if ($errors->any() && !$errors->has('nome') && !$errors->has('cognome') && !$errors->has('email') && !$errors->has('password') && !$errors->has('ruolo'))
            <div class="alert-error">
                <strong>Correggi i seguenti errori:</strong>
                <ul style="margin-top:6px; padding-left:16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- ── Dati personali ── --}}
            <div class="section-sep">Dati personali</div>

            <div class="grid-2">
                <div class="field">
                    <label for="nome">Nome</label>
                    <div class="input-wrap">
                        <span class="ico">👤</span>
                        <input type="text" id="nome" name="nome"
                               placeholder="Mario"
                               value="{{ old('nome') }}"
                               class="{{ $errors->has('nome') ? 'is-invalid' : '' }}"
                               required />
                    </div>
                    @error('nome') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="cognome">Cognome</label>
                    <div class="input-wrap">
                        <span class="ico">👤</span>
                        <input type="text" id="cognome" name="cognome"
                               placeholder="Rossi"
                               value="{{ old('cognome') }}"
                               class="{{ $errors->has('cognome') ? 'is-invalid' : '' }}"
                               required />
                    </div>
                    @error('cognome') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <span class="ico">✉️</span>
                    <input type="email" id="email" name="email"
                           placeholder="mario.rossi@email.com"
                           value="{{ old('email') }}"
                           autocomplete="email"
                           class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                           required />
                </div>
                @error('email') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="telefono">Telefono <span class="opt">(opzionale)</span></label>
                <div class="input-wrap">
                    <span class="ico">📞</span>
                    <input type="tel" id="telefono" name="telefono"
                           placeholder="+39 333 123 4567"
                           value="{{ old('telefono') }}" />
                </div>
            </div>

            {{-- ── Ruolo ── --}}
            <div class="section-sep">Ruolo</div>

            <div class="field">
                <label for="ruolo">Seleziona il tuo ruolo</label>
                <div class="input-wrap">
                    <span class="ico">🏷️</span>
                    <select id="ruolo" name="ruolo"
                            class="{{ $errors->has('ruolo') ? 'is-invalid' : '' }}"
                            onchange="togglePazienteFields(this.value)"
                            required>
                        <option value="" disabled {{ old('ruolo') ? '' : 'selected' }}>— Scegli ruolo —</option>
                        <option value="paziente"  {{ old('ruolo') === 'paziente'  ? 'selected' : '' }}>🧑‍🦯 Paziente</option>
                        <option value="medico"    {{ old('ruolo') === 'medico'    ? 'selected' : '' }}>👨‍⚕️ Medico</option>
                        <option value="familiare" {{ old('ruolo') === 'familiare' ? 'selected' : '' }}>👨‍👩‍👧 Familiare</option>
                    </select>
                </div>
                @error('ruolo') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            {{-- ── Campi extra per Paziente ── --}}
            <div id="paziente-fields" class="{{ old('ruolo') === 'paziente' ? 'visible' : '' }}">
                <div class="section-sep">Dati paziente</div>

                <div class="field">
                    <label for="data_nascita">Data di nascita</label>
                    <div class="input-wrap">
                        <span class="ico">🎂</span>
                        <input type="date" id="data_nascita" name="data_nascita"
                               value="{{ old('data_nascita') }}"
                               class="{{ $errors->has('data_nascita') ? 'is-invalid' : '' }}" />
                    </div>
                    @error('data_nascita') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="indirizzo">Indirizzo <span class="opt">(opzionale)</span></label>
                    <div class="input-wrap">
                        <span class="ico">🏠</span>
                        <input type="text" id="indirizzo" name="indirizzo"
                               placeholder="Via Roma 1, Milano"
                               value="{{ old('indirizzo') }}" />
                    </div>
                </div>
            </div>

            {{-- ── Sicurezza ── --}}
            <div class="section-sep">Sicurezza</div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="ico">🔒</span>
                    <input type="password" id="password" name="password"
                           placeholder="Almeno 8 caratteri"
                           autocomplete="new-password"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           oninput="checkStrength(this.value)"
                           required />
                    <button type="button" class="toggle-pw" onclick="togglePw('password','btn1')" id="btn1">👁</button>
                </div>
                <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
                @error('password') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Conferma password</label>
                <div class="input-wrap">
                    <span class="ico">🔒</span>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Ripeti la password"
                           autocomplete="new-password"
                           required />
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','btn2')" id="btn2">👁</button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Crea account</button>
        </form>

        <div class="login-link">
            Hai già un account? <a href="{{ route('login') }}">Accedi</a>
        </div>
    </div>
</div>

<script>
    // Mostra/nascondi campi paziente
    function togglePazienteFields(ruolo) {
        const el = document.getElementById('paziente-fields');
        const dn = document.getElementById('data_nascita');
        if (ruolo === 'paziente') {
            el.classList.add('visible');
            dn.setAttribute('required', 'required');
        } else {
            el.classList.remove('visible');
            dn.removeAttribute('required');
        }
    }

    // Toggle visibilità password
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

    // Indicatore forza password
    function checkStrength(value) {
        const bar = document.getElementById('pw-bar');
        let score = 0;
        if (value.length >= 8)  score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const colors = ['#ef4444', '#f97316', '#eab308', '#10b981'];
        const widths = ['25%', '50%', '75%', '100%'];

        bar.style.width      = score > 0 ? widths[score - 1] : '0';
        bar.style.background = score > 0 ? colors[score - 1] : 'transparent';
    }

    // Ripristina stato se c'è un old('ruolo') al caricamento
    document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('ruolo');
        if (sel.value) togglePazienteFields(sel.value);
    });
</script>

</body>
</html>
