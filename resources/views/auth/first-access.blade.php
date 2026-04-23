<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Primo accesso</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/auth/first-acess.css')
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

        <h1>Benvenuto<br>su PillMate</h1>
        <p>Il tuo account è stato creato dal tuo medico. Imposta la tua password personale per completare la registrazione.</p>

        <div class="info-list">
            <div class="info-item">
                <div class="info-icon">
                    <i data-lucide="lock"></i>
                </div>
                <div class="info-text">
                    <strong>Password sicura</strong>
                    <span>Usa almeno 8 caratteri, con lettere e numeri</span>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <i data-lucide="check-circle-2"></i>
                </div>
                <div class="info-text">
                    <strong>Accesso immediato</strong>
                    <span>Dopo il salvataggio potrai accedere alla tua dashboard</span>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <i data-lucide="shield-check"></i>
                </div>
                <div class="info-text">
                    <strong>Dati protetti</strong>
                    <span>La tua password è cifrata e non visibile a nessuno</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="right">
    <div class="form-box">
        <h2>
            <i data-lucide="key-round"></i>
            Imposta la password
        </h2>
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
                    <button type="button" class="toggle-pw" onclick="togglePw('password','icon1')" aria-label="Mostra o nascondi password">
                        <i data-lucide="eye" id="icon1"></i>
                    </button>
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
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','icon2')" aria-label="Mostra o nascondi conferma password">
                        <i data-lucide="eye" id="icon2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i data-lucide="arrow-right"></i>
                Salva e accedi
            </button>
        </form>

        <div class="back-link">
            Hai già una password? <a href="{{ route('login') }}">Torna al login</a>
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
