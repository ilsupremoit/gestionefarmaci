<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Nuovo paziente</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/medico/createPaziente.css')
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="pill"></i>
        </div>
        <span class="brand-name">PillMate</span>
    </div>

    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>
        Dashboard
    </a>

    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">
        <span class="ico"><i data-lucide="users"></i></span>
        I miei pazienti
    </a>

    <a class="nav-item" href="{{ route('medico.notifiche') }}">
        <span class="ico"><i data-lucide="bell"></i></span>
        Notifiche
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->nome }} {{ auth()->user()->cognome }}</div>
                <div class="user-role">
                    <i data-lucide="user-round"></i>
                    Medico
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i data-lucide="log-out"></i>
                Esci
            </button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="title">
            <h1>Nuovo paziente</h1>
            <p>Crea un account paziente con credenziali provvisorie</p>
        </div>

        <a class="btn-back" href="{{ route('medico.pazienti.index') }}">
            <i data-lucide="arrow-left"></i>
            Torna ai pazienti
        </a>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            <strong>Controlla questi campi:</strong>
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert-success">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('medico.pazienti.store') }}" id="formPaziente">
            @csrf

            <div class="grid">
                <div class="section-label">
                    <i data-lucide="clipboard-list"></i>
                    Dati anagrafici
                </div>

                <div class="field">
                    <label for="nome">Nome <span class="req">*</span></label>
                    <input id="nome" type="text" name="nome" value="{{ old('nome') }}" placeholder="es. Mario" required/>
                </div>

                <div class="field">
                    <label for="cognome">Cognome <span class="req">*</span></label>
                    <input id="cognome" type="text" name="cognome" value="{{ old('cognome') }}" placeholder="es. Rossi" required/>
                </div>

                <div class="field">
                    <label for="sesso">Sesso <span class="req">*</span></label>
                    <select id="sesso" name="sesso" required>
                        <option value="">— Seleziona —</option>
                        <option value="M" {{ old('sesso')=='M'?'selected':'' }}>Maschile</option>
                        <option value="F" {{ old('sesso')=='F'?'selected':'' }}>Femminile</option>
                    </select>
                </div>

                <div class="field">
                    <label for="data_nascita">Data di nascita <span class="req">*</span></label>
                    <input id="data_nascita" type="date" name="data_nascita" value="{{ old('data_nascita') }}" required/>
                </div>

                <div class="field">
                    <label for="comune_nascita">Comune di nascita <span class="req">*</span></label>
                    <input id="comune_nascita" type="text" name="comune_nascita" value="{{ old('comune_nascita') }}" placeholder="es. Roma" required/>
                    <span class="hint">Necessario per il calcolo del codice fiscale</span>
                </div>

                <div class="field">
                    <label for="telefono">Telefono <span class="optional">(opzionale)</span></label>
                    <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}" placeholder="es. 3331234567"/>
                </div>

                <div class="field">
                    <label for="email">Email <span class="optional">(opzionale)</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="es. mario.rossi@email.it"/>
                </div>

                <div class="field">
                    <label for="indirizzo">Indirizzo <span class="optional">(opzionale)</span></label>
                    <input id="indirizzo" type="text" name="indirizzo" value="{{ old('indirizzo') }}" placeholder="es. Via Roma 1, Milano"/>
                </div>

                <div class="field full">
                    <label for="codice_fiscale">Codice fiscale <span class="req">*</span></label>
                    <div class="cf-row">
                        <input id="codice_fiscale" type="text" name="codice_fiscale"
                               value="{{ old('codice_fiscale') }}"
                               placeholder="es. RSSMRA80A01H501Z"
                               maxlength="16" style="text-transform:uppercase;font-family:monospace;letter-spacing:1px;" required/>
                        <button type="button" class="btn-calc" onclick="calcolaCodiceFiscale()">
                            <i data-lucide="calculator"></i>
                            Calcola CF
                        </button>
                    </div>
                    <div class="cf-result" id="cfResult"></div>
                    <span class="hint">Inserisci manualmente o clicca "Calcola CF" dopo aver compilato nome, cognome, data nascita, sesso e comune.</span>
                </div>

                <div class="field full">
                    <label for="note_mediche">Note mediche <span class="optional">(opzionale)</span></label>
                    <textarea id="note_mediche" name="note_mediche" placeholder="Allergie, patologie note, annotazioni...">{{ old('note_mediche') }}</textarea>
                </div>

                <div class="section-label">
                    <i data-lucide="shield-check"></i>
                    Credenziali di accesso
                </div>

                <div class="field">
                    <label for="username">Nome utente (login) <span class="req">*</span></label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="es. mario.rossi" required autocomplete="off"/>
                    <span class="hint">Il paziente userà questo per accedere.</span>
                </div>

                <div class="field">
                    <label for="password_temp">Password provvisoria <span class="req">*</span></label>
                    <input id="password_temp" type="text" name="password_temp" value="{{ old('password_temp') }}" placeholder="min. 6 caratteri" required autocomplete="off"/>
                    <span class="hint">Il paziente dovrà cambiarla al primo accesso.</span>
                </div>
            </div>

            <div class="actions">
                <a class="btn-back" href="{{ route('medico.pazienti.index') }}">
                    Annulla
                </a>

                <button class="btn-primary" type="submit">
                    <i data-lucide="user-plus"></i>
                    Crea paziente
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    // ── Calcolo Codice Fiscale italiano ───────────────────────────────────
    const CODICI_CATASTALI = {
        'roma': 'H501', 'milano': 'F205', 'napoli': 'F839', 'torino': 'L219',
        'palermo': 'G273', 'genova': 'D969', 'bologna': 'A944', 'firenze': 'D612',
        'bari': 'A662', 'catania': 'C351', 'venezia': 'L736', 'verona': 'L781',
        'messina': 'F158', 'padova': 'G224', 'trieste': 'L424', 'brescia': 'B157',
        'taranto': 'L049', 'prato': 'G999', 'reggio calabria': 'H224',
        'modena': 'F257', 'reggio emilia': 'H223', 'perugia': 'G478',
        'livorno': 'E625', 'ravenna': 'H199', 'cagliari': 'B354',
        'foggia': 'D643', 'rimini': 'H294', 'salerno': 'H703', 'ferrara': 'D548',
        'sassari': 'I452', 'latina': 'E472', 'giugliano in campania': 'E054',
        'monza': 'F704', 'bergamo': 'A794', 'siracusa': 'I754', 'pescara': 'G482',
        'vicenza': 'L840', 'trento': 'L378', 'novara': 'F952', 'ancona': 'A271',
        'lecce': 'E506', 'udine': 'L483', 'andria': 'A285', 'barletta': 'A669',
        'arezzo': 'A390', 'cesena': 'C573', 'pesaro': 'G453', 'pisa': 'G702',
        'catanzaro': 'C352', 'alessandria': 'A182', 'la spezia': 'E463',
        'vicenza': 'L840', 'terni': 'L117', 'forlì': 'D704', 'bolzano': 'A952',
    };

    const MESI = ['A','B','C','D','E','H','L','M','P','R','S','T'];
    const TAB_CONSONANTI = {'B':0,'C':1,'D':2,'F':3,'G':4,'H':5,'J':6,'K':7,'L':8,'M':9,'N':10,'P':11,'Q':12,'R':13,'S':14,'T':15,'V':16,'W':17,'X':18,'Y':19,'Z':20};
    const TAB_VOCALI = {'A':1,'E':2,'I':3,'O':4,'U':5};
    const TAB_DISPARI = {0:1,1:0,2:5,3:7,4:9,5:13,6:15,7:17,8:19,9:21,10:2,11:4,12:18,13:20,14:11,15:3,16:6,17:8,18:12,19:14,20:16};

    function codiceParte(nome_o_cognome, isNome) {
        const s = nome_o_cognome.toUpperCase().replace(/[^A-Z]/g, '');
        const consonanti = s.split('').filter(c => 'BCDFGHJKLMNPQRSTVWXYZ'.includes(c));
        const vocali = s.split('').filter(c => 'AEIOU'.includes(c));

        if (isNome && consonanti.length >= 4) {
            return (consonanti[0] + consonanti[2] + consonanti[3]).substring(0, 3);
        }

        const combined = consonanti.concat(vocali);
        while (combined.length < 3) combined.push('X');
        return combined.slice(0, 3).join('');
    }

    function calcolaCodiceFiscale() {
        const nome = document.getElementById('nome').value.trim();
        const cognome = document.getElementById('cognome').value.trim();
        const dn = document.getElementById('data_nascita').value;
        const sesso = document.getElementById('sesso').value;
        const comune = document.getElementById('comune_nascita').value.trim().toLowerCase();

        if (!nome || !cognome || !dn || !sesso || !comune) {
            alert('Compila Nome, Cognome, Data di nascita, Sesso e Comune di nascita per calcolare il codice fiscale.');
            return;
        }

        const codiceCatastale = CODICI_CATASTALI[comune];
        if (!codiceCatastale) {
            const cfBox = document.getElementById('cfResult');
            cfBox.style.display = 'block';
            cfBox.style.background = '#fff7ed';
            cfBox.style.borderColor = '#fed7aa';
            cfBox.style.color = '#c2410c';
            cfBox.textContent = 'Comune "' + document.getElementById('comune_nascita').value + '" non trovato. Inserisci il CF manualmente o verifica il nome del comune.';
            return;
        }

        const [anno, mese, giorno] = dn.split('-');

        const cfCognome = codiceParte(cognome, false);
        const cfNome = codiceParte(nome, true);
        const cfAnno = anno.substring(2);
        const cfMese = MESI[parseInt(mese) - 1];
        const cfGiorno = sesso === 'M' ? giorno.padStart(2, '0') : String(parseInt(giorno) + 40).padStart(2, '0');

        const base = cfCognome + cfNome + cfAnno + cfMese + cfGiorno + codiceCatastale;

        let somma = 0;
        for (let i = 0; i < 15; i++) {
            const c = base[i];
            const val = /[0-9]/.test(c) ? parseInt(c) : c.charCodeAt(0) - 65;
            if (i % 2 === 0) {
                somma += TAB_DISPARI[val] ?? val;
            } else {
                somma += TAB_CONSONANTI[c] !== undefined ? TAB_CONSONANTI[c] : (TAB_VOCALI[c] !== undefined ? TAB_VOCALI[c] : val);
            }
        }

        const cf = base + String.fromCharCode(65 + (somma % 26));

        const cfInput = document.getElementById('codice_fiscale');
        cfInput.value = cf;
        cfInput.style.borderColor = '#86efac';
        cfInput.style.background = '#f0fdf4';

        const cfBox = document.getElementById('cfResult');
        cfBox.style.display = 'block';
        cfBox.style.background = '#f0fdf4';
        cfBox.style.borderColor = '#86efac';
        cfBox.style.color = '#15803d';
        cfBox.textContent = 'CF calcolato: ' + cf + ' — Verifica che sia corretto prima di salvare.';
    }

    document.getElementById('codice_fiscale').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
</body>
</html>
