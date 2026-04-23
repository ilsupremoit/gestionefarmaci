<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Accedi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/auth/login.css')

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

        <h1>Gestione terapie<br>semplice e sicura</h1>
        <p>Monitoraggio in tempo reale delle somministrazioni, dispositivi IoT e comunicazione continua tra medici, pazienti e familiari.</p>

        <div class="features">
            <div class="feat">
                <div class="feat-ico">
                    <i data-lucide="radio"></i>
                </div>
                <div class="feat-text">
                    <strong>Dispositivi IoT connessi</strong>
                    <span>Temperatura, umidità e batteria in tempo reale</span>
                </div>
            </div>

            <div class="feat">
                <div class="feat-ico">
                    <i data-lucide="clipboard-list"></i>
                </div>
                <div class="feat-text">
                    <strong>Terapie personalizzate</strong>
                    <span>Il medico imposta, il paziente viene guidato</span>
                </div>
            </div>

            <div class="feat">
                <div class="feat-ico">
                    <i data-lucide="bell"></i>
                </div>
                <div class="feat-text">
                    <strong>Alert e notifiche</strong>
                    <span>Familiari sempre informati sulle assunzioni</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="right">
    <div class="form-box">
        <h2>
            <i data-lucide="log-in"></i>
            Bentornato
        </h2>
        <p class="subtitle">Accedi al tuo account PillMate</p>

        @if (session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->has('email') || $errors->has('login') || session('error'))
            <div class="alert alert-error">
                <i data-lucide="circle-alert"></i>
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
                    <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Mostra o nascondi password">
                        <i data-lucide="eye" id="pwIcon"></i>
                    </button>
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

                <a href="{{ route('password.request') }}" class="forgot-link">
                    Password dimenticata?
                </a>
            </div>

            <button type="submit" class="btn-submit">
                <i data-lucide="arrow-right"></i>
                Accedi a PillMate
            </button>
        </form>
    </div>
</div>

<script>
    function renderLucide() {
        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons();
        }
    }

    function togglePw() {
        const input = document.getElementById('password');
        const icon = document.getElementById('pwIcon');

        input.type = input.type === 'password' ? 'text' : 'password';
        icon.setAttribute('data-lucide', input.type === 'password' ? 'eye' : 'eye-off');
        renderLucide();
    }

    renderLucide();
</script>

</body>
</html>
