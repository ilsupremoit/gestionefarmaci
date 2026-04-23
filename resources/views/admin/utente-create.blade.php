<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate Admin — Nuovo Utente</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')
</head>
<body>
@include('admin._sidebar', ['active' => 'utenti'])

<main class="main">
    <div class="page-header">
        <div>
            <h1>Crea un nuovo utente</h1>
        </div>

        <a href="{{ route('admin.utenti') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left"></i>
            Torna agli utenti
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <i data-lucide="circle-alert"></i>
            <div>
                <strong>Controlla i campi:</strong>
                <ul style="margin-top:6px;padding-left:18px;">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('admin.utenti.store') }}" id="formCrea">
            @csrf

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:24px;">
                @foreach([
                    'medico' => ['Medico', 'stethoscope'],
                    'paziente' => ['Paziente', 'user-round'],
                    'admin' => ['Admin', 'shield-check']
                ] as $r => [$label, $icon])
                    <label
                        style="border:2px solid var(--border);border-radius:12px;padding:14px;text-align:center;cursor:pointer;transition:all .15s;font-weight:600;font-size:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;"
                        id="lbl-{{ $r }}"
                    >
                        <input
                            type="radio"
                            name="ruolo"
                            value="{{ $r }}"
                            {{ old('ruolo','medico')===$r ? 'checked':'' }}
                            style="display:none;"
                            onchange="setRuolo('{{ $r }}')"
                        />
                        <i data-lucide="{{ $icon }}"></i>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="field">
                    <label for="nome">Nome *</label>
                    <input id="nome" type="text" name="nome" value="{{ old('nome') }}" required/>
                </div>

                <div class="field">
                    <label for="cognome">Cognome *</label>
                    <input id="cognome" type="text" name="cognome" value="{{ old('cognome') }}" required/>
                </div>
                <div class="field">
                    <label>Username *</label>
                    <input type="text" name="username" value="{{ old('username') }}" required/>
                    <div class="hint"></div>
                </div>

                <div class="field">
                    <label>Password *</label>
                    <input type="text" name="password" value="{{ old('password') }}" placeholder="min. 6 caratteri" required minlength="6"/>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"/>
                </div>

                <div class="field">
                    <label for="telefono">Telefono</label>
                    <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}"/>
                </div>

                <div id="campoPaziente" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;grid-column:1/-1;">
                    <div class="field">
                        <label for="sesso">Sesso</label>
                        <select id="sesso" name="sesso">
                            <option value="">— Seleziona —</option>
                            <option value="M" {{ old('sesso')=='M'?'selected':'' }}>Maschile</option>
                            <option value="F" {{ old('sesso')=='F'?'selected':'' }}>Femminile</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="data_nascita">Data di nascita</label>
                        <input id="data_nascita" type="date" name="data_nascita" value="{{ old('data_nascita') }}"/>
                    </div>

                    <div class="field">
                        <label for="comune_nascita">Comune di nascita</label>
                        <input id="comune_nascita" type="text" name="comune_nascita" value="{{ old('comune_nascita') }}"/>
                    </div>

                    <div class="field">
                        <label for="codice_fiscale">Codice fiscale</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input
                                id="codice_fiscale"
                                type="text"
                                name="codice_fiscale"
                                value="{{ old('codice_fiscale') }}"
                                maxlength="16"
                                style="text-transform:uppercase;font-family:monospace;letter-spacing:1px;flex:1;"
                            />
                            <button type="button" class="btn btn-ghost" onclick="calcolaCodiceFiscale()">
                                <i data-lucide="calculator"></i>
                                Calcola CF
                            </button>
                        </div>
                        <div id="cfResult" class="hint" style="margin-top:8px;"></div>
                    </div>

                    <div class="field">
                        <label for="indirizzo">Indirizzo</label>
                        <input id="indirizzo" type="text" name="indirizzo" value="{{ old('indirizzo') }}"/>
                    </div>

                    <div class="field">
                        <label for="note_mediche">Note mediche</label>
                        <textarea id="note_mediche" name="note_mediche" placeholder="Allergie, patologie note, annotazioni...">{{ old('note_mediche') }}</textarea>
                    </div>
                </div>

                <div class="field" style="grid-column:1/-1;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;text-transform:none;letter-spacing:0;">
                        <input
                            type="checkbox"
                            name="must_change_password"
                            value="1"
                            {{ old('must_change_password','1') ? 'checked':'' }}
                            style="width:16px;height:16px;accent-color:var(--accent);"
                        />
                        Obbliga il cambio password al primo accesso
                    </label>
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="user-plus"></i>
                    Crea utente
                </button>

                <a href="{{ route('admin.utenti') }}" class="btn btn-ghost">
                    Annulla
                </a>
            </div>
        </form>
    </div>
</main>

<script>
    const RUOLI = ['medico','paziente','admin'];

    function setRuolo(r) {
        RUOLI.forEach(x => {
            const lbl = document.getElementById('lbl-' + x);
            lbl.style.borderColor = x === r ? 'var(--accent)' : 'var(--border)';
            lbl.style.background  = x === r ? '#f5f3ff' : '#fff';
            lbl.style.color       = x === r ? 'var(--accent)' : 'var(--text)';
        });

        document.getElementById('campoPaziente').style.display = r === 'paziente' ? 'grid' : 'none';
    }

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

        async function trovaCodiceCatastale(comune) {
        const response = await fetch(`/comuni/cerca?nome=${encodeURIComponent(comune)}`);
        const data = await response.json();
        return data.found ? data.codice : null;
    }

        async function calcolaCodiceFiscale() {
        const nome    = document.getElementById('nome').value.trim();
        const cognome = document.getElementById('cognome').value.trim();
        const dn      = document.getElementById('data_nascita').value;
        const sesso   = document.getElementById('sesso').value;
        const comune  = document.getElementById('comune_nascita').value.trim();

        if (!nome || !cognome || !dn || !sesso || !comune) {
        alert('Compila: Nome, Cognome, Data di nascita, Sesso e Comune di nascita per calcolare il CF.');
        return;
    }

        const codiceCatastale = await trovaCodiceCatastale(comune);

        if (!codiceCatastale) {
        const cfBox = document.getElementById('cfResult');
        cfBox.style.display = 'block';
        cfBox.style.background = '#fff7ed';
        cfBox.style.borderColor = '#fed7aa';
        cfBox.style.color = '#c2410c';
        cfBox.textContent = 'Comune non trovato. Verifica il nome inserito.';
        return;
    }

        const [anno, mese, giorno] = dn.split('-');

        const cfCognome = codiceParte(cognome, false);
        const cfNome    = codiceParte(nome, true);
        const cfAnno    = anno.substring(2);
        const cfMese    = MESI[parseInt(mese) - 1];
        const cfGiorno  = sesso === 'M'
        ? giorno.padStart(2, '0')
        : String(parseInt(giorno) + 40).padStart(2, '0');

        const base = cfCognome + cfNome + cfAnno + cfMese + cfGiorno + codiceCatastale;

        let somma = 0;
        for (let i = 0; i < 15; i++) {
        const c = base[i];
        const val = /[0-9]/.test(c) ? parseInt(c) : c.charCodeAt(0) - 65;
        if (i % 2 === 0) {
        somma += TAB_DISPARI[val] ?? val;
    } else {
        somma += TAB_CONSONANTI[c] !== undefined
        ? TAB_CONSONANTI[c]
        : (TAB_VOCALI[c] !== undefined ? TAB_VOCALI[c] : val);
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
        cfBox.textContent = 'CF calcolato: ' + cf;
    }
        document.getElementById('codice_fiscale').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
</body>
</html>
