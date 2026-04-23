{{-- resources/views/auth/reset-password.blade.php --}}
    <!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Reimposta Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/auth/reset-password.css')
</head>
<body>

<div class="left">
    <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="left-content">
        <div class="brand">
            <div class="brand-icon">
                <i data-lucide="pill"></i>
            </div>
            <span class="brand-name">PillMate</span>
        </div>

        <h1>Crea una<br><span>password sicura</span></h1>
        <p>Scegli una password forte per proteggere il tuo account e i tuoi dati.</p>

        <div class="pw-rules">
            <div class="pw-rule">
                <div class="pw-rule-ico">
                    <i data-lucide="ruler"></i>
                </div>
                <span>Usa almeno 8 caratteri</span>
            </div>
            <div class="pw-rule">
                <div class="pw-rule-ico">
                    <i data-lucide="type"></i>
                </div>
                <span>Inserisci lettere maiuscole e minuscole</span>
            </div>
            <div class="pw-rule">
                <div class="pw-rule-ico">
                    <i data-lucide="hash"></i>
                </div>
                <span>Aggiungi almeno un numero</span>
            </div>
            <div class="pw-rule">
                <div class="pw-rule-ico">
                    <i data-lucide="shield-check"></i>
                </div>
                <span>Una password forte protegge meglio l’account</span>
            </div>
        </div>
    </div>
</div>

<div class="right">
    <div class="form-box">
        <h2>
            <i data-lucide="key-round"></i>
            Reimposta la password
        </h2>
        <p class="subtitle">Inserisci la tua email e la nuova password per riprendere l'accesso a PillMate.</p>

        @if ($errors->has('email') && !$errors->has('password'))
            <div class="alert-error">
                <i data-lucide="circle-alert"></i>
                <span>{{ $errors->first('email') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ request()->email }}">

            <div class="field">
                <label for="password">Nuova password</label>
                <div class="input-wrap">
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
                    <button type="button" class="toggle-pw" onclick="togglePw('password','icon1')" aria-label="Mostra o nascondi password">
                        <i data-lucide="eye" id="icon1"></i>
                    </button>
                </div>
                <div class="pw-strength">
                    <div class="pw-strength-bar" id="pw-bar"></div>
                </div>
                @error('password')
                <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Conferma nuova password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Ripeti la nuova password"
                        autocomplete="new-password"
                        required
                    />
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','icon2')" aria-label="Mostra o nascondi conferma password">
                        <i data-lucide="eye" id="icon2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i data-lucide="save"></i>
                Salva nuova password
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('login') }}">Torna al login</a>
        </div>
    </div>
</div>

<script>
    function renderLucide() {
        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons();
        }
    }

    function togglePw(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        input.type = input.type === 'password' ? 'text' : 'password';
        icon.setAttribute('data-lucide', input.type === 'password' ? 'eye' : 'eye-off');
        renderLucide();
    }

    function checkStrength(value) {
        const bar = document.getElementById('pw-bar');
        let score = 0;
        if (value.length >= 8) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const colors = ['#ef4444', '#f97316', '#eab308', '#10b981'];
        const widths = ['25%', '50%', '75%', '100%'];

        bar.style.width = score > 0 ? widths[score - 1] : '0';
        bar.style.background = score > 0 ? colors[score - 1] : 'transparent';
    }

    renderLucide();
</script>

</body>
</html>
