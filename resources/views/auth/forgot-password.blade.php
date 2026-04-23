<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Password Dimenticata</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/auth/forgot-password.css')

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

        <h1>Recupera il tuo<br><span>accesso</span></h1>
        <p>Inserisci la tua email e riceverai un link sicuro per reimpostare la password in pochi istanti.</p>

        <div class="steps">
            <div class="step">
                <div class="step-ico cyan">
                    <i data-lucide="mail"></i>
                </div>
                <div class="step-text">
                    <strong>Inserisci la tua email</strong>
                    <span>Quella con cui ti sei registrato</span>
                </div>
            </div>

            <div class="step">
                <div class="step-ico blue">
                    <i data-lucide="link"></i>
                </div>
                <div class="step-text">
                    <strong>Controlla la casella</strong>
                    <span>Riceverai un link valido per 60 minuti</span>
                </div>
            </div>

            <div class="step">
                <div class="step-ico green">
                    <i data-lucide="lock"></i>
                </div>
                <div class="step-text">
                    <strong>Imposta la nuova password</strong>
                    <span>Torna ad accedere a PillMate</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="right">
    <div class="form-box">
        <h2>Password dimenticata?</h2>
        <p class="subtitle">Nessun problema. Inserisci la tua email e ti invieremo un link per reimpostarla.</p>

        @if (session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->has('email'))
            <div class="alert alert-error">
                <i data-lucide="circle-alert"></i>
                <span>{{ $errors->first('email') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <span class="ico">
                        <i data-lucide="mail"></i>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="la-tua-email@esempio.com"
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

            <button type="submit" class="btn-submit">
                <i data-lucide="send"></i>
                Invia link di recupero
            </button>
        </form>

        <div class="back-link">
            Ricordi la password? <a href="{{ route('login') }}">Torna al login</a>
        </div>
    </div>
</div>

</body>
</html>
