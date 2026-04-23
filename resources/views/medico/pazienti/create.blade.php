<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Nuovo paziente</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root{--bg:#f0f7ff;--surface:#fff;--border:#dde8f5;--accent:#3b82f6;--accent2:#06b6d4;--text:#1e293b;--muted:#64748b;--red:#dc2626;--green:#059669;}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
        .sidebar{width:240px;flex-shrink:0;background:#fff;border-right:1px solid var(--border);display:flex;flex-direction:column;padding:24px 0;position:fixed;top:0;left:0;height:100vh}
        .brand{padding:0 20px 20px;border-bottom:1px solid var(--border);margin-bottom:16px}
        .brand-name{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .nav-item{display:flex;align-items:center;gap:10px;padding:10px 20px;font-size:14px;color:var(--muted);text-decoration:none;transition:all .2s;border-left:3px solid transparent}
        .nav-item:hover{color:var(--text);background:#f5f3ff}
        .nav-item.active{color:var(--accent);background:#eff6ff;border-left-color:var(--accent);font-weight:600}
        .sidebar-footer{margin-top:auto;padding:16px 20px 0;border-top:1px solid var(--border)}
        .user-info{display:flex;align-items:center;gap:10px;margin-bottom:12px}
        .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
        .user-name{font-size:13px;font-weight:600}.user-role{font-size:11px;color:var(--muted)}
        .btn-logout{width:100%;padding:9px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#b91c1c;font-size:13px;cursor:pointer;font-family:inherit}
        .main{margin-left:240px;flex:1;padding:32px 36px}
        .topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:28px}
        .title h1{font-family:'Syne',sans-serif;font-size:26px;font-weight:700;margin-bottom:4px}
        .title p{color:var(--muted);font-size:14px}
        .btn-back{text-decoration:none;color:var(--text);border:1px solid var(--border);background:#fff;padding:10px 16px;border-radius:10px;font-size:13px;display:inline-flex;align-items:center;gap:6px;transition:all .2s;font-weight:500}
        .btn-back:hover{border-color:var(--accent);color:var(--accent)}
        .card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:0 1px 4px rgba(0,0,0,.06)}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
        .field{display:flex;flex-direction:column;gap:6px}
        .field.full{grid-column:1/-1}
        label{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);font-weight:700}
        .optional{font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted);opacity:.6;font-size:10px;margin-left:4px}
        .req{color:var(--red);margin-left:2px}
        input,textarea,select{width:100%;background:#f8faff;border:1.5px solid var(--border);color:var(--text);padding:11px 14px;border-radius:10px;font:inherit;font-size:14px;outline:none;transition:border-color .2s}
        input:focus,textarea:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.08)}
        input::placeholder{color:#94a3b8}
        input.required-empty{border-color:var(--red);background:#fef2f2}
        textarea{min-height:80px;resize:vertical}
        .section-label{font-family:'Syne',sans-serif;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--accent);grid-column:1/-1;margin-top:8px;padding-top:18px;border-top:1px solid var(--border)}
        .hint{font-size:11px;color:var(--muted);margin-top:-2px}
        .cf-row{display:flex;gap:8px;align-items:flex-end}
        .cf-row input{flex:1}
        .btn-calc{padding:11px 14px;background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;white-space:nowrap;transition:opacity .2s;flex-shrink:0}
        .btn-calc:hover{opacity:.9}
        .actions{display:flex;justify-content:flex-end;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--border)}
        .btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;color:#fff;padding:12px 22px;border-radius:12px;font-weight:700;cursor:pointer;font-family:inherit;font-size:14px}
        .btn-primary:hover{opacity:.9}
        .alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:13px 16px;border-radius:12px;margin-bottom:20px;font-size:13px}
        .alert-error ul{margin:6px 0 0;padding-left:18px}
        .alert-success{background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:13px 16px;border-radius:12px;margin-bottom:20px;font-size:13px}
        .cf-result{display:none;margin-top:6px;padding:8px 12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:13px;font-weight:700;color:#15803d;font-family:monospace;letter-spacing:1px}
        @media(max-width:768px){.sidebar{display:none}.main{margin-left:0;padding:20px 16px}}
        @media(max-width:600px){.grid{grid-template-columns:1fr}.field.full{grid-column:1}}
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand"><span class="brand-name">PillMate</span></div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">🏠 Dashboard</a>
    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">👥 I miei pazienti</a>
    <a class="nav-item" href="{{ route('medico.notifiche') }}">🔔 Notifiche</a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->nome }} {{ auth()->user()->cognome }}</div>
                <div class="user-role">👨‍⚕️ Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="title">
            <h1>Nuovo paziente</h1>
            <p>Crea un account paziente con credenziali provvisorie</p>
        </div>
        <a class="btn-back" href="{{ route('medico.pazienti.index') }}">← Torna ai pazienti</a>
    </div>

    @if ($errors->any())
    <div class="alert-error">
        <strong>Controlla questi campi:</strong>
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif

    <div class="card">
        <form method="POST" action="{{ route('medico.pazienti.store') }}" id="formPaziente">
            @csrf
            <div class="grid">

                <div class="section-label">📋 Dati anagrafici</div>

                <div class="field">
                    <label for="nome">Nome <span class="req">*</span></label>
                    <input id="nome" type="text" name="nome" value="{{ old('nome') }}" placeholder="es. Mario" required/>
                </div>
                <div class="field">
                    <label for="cognome">Cognome <span class="req">*</span></label>
                    <input id="cognome" type="text" name="cognome" value="{{ old('cognome') }}" placeholder="es. Rossi" required/>
                </div>

                {{-- Sesso — necessario per calcolo CF --}}
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

                {{-- Comune di nascita — necessario per CF --}}
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

                {{-- CODICE FISCALE + CALCOLO AUTOMATICO --}}
                <div class="field full">
                    <label for="codice_fiscale">Codice fiscale <span class="req">*</span></label>
                    <div class="cf-row">
                        <input id="codice_fiscale" type="text" name="codice_fiscale"
                               value="{{ old('codice_fiscale') }}"
                               placeholder="es. RSSMRA80A01H501Z"
                               maxlength="16" style="text-transform:uppercase;font-family:monospace;letter-spacing:1px;" required/>
                        <button type="button" class="btn-calc" onclick="calcolaCodiceFiscale()">
                            🔢 Calcola CF
                        </button>
                    </div>
                    <div class="cf-result" id="cfResult"></div>
                    <span class="hint">Inserisci manualmente o clicca "Calcola CF" dopo aver compilato nome, cognome, data nascita, sesso e comune.</span>
                </div>

                <div class="field full">
                    <label for="note_mediche">Note mediche <span class="optional">(opzionale)</span></label>
                    <textarea id="note_mediche" name="note_mediche" placeholder="Allergie, patologie note, annotazioni...">{{ old('note_mediche') }}</textarea>
                </div>

                <div class="section-label">🔐 Credenziali di accesso</div>

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
                <a class="btn-back" href="{{ route('medico.pazienti.index') }}">Annulla</a>
                <button class="btn-primary" type="submit">✅ Crea paziente</button>
            </div>
        </form>
    </div>
</main>

<script>
// ── Calcolo Codice Fiscale italiano ───────────────────────────────────
// Tabella codici catastali principali comuni italiani
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

    // Per il nome: se ci sono 4+ consonanti, usa 1°, 3°, 4°
    if (isNome && consonanti.length >= 4) {
        return (consonanti[0] + consonanti[2] + consonanti[3]).substring(0, 3);
    }

    const combined = consonanti.concat(vocali);
    while (combined.length < 3) combined.push('X');
    return combined.slice(0, 3).join('');
}

function calcolaCodiceFiscale() {
    const nome   = document.getElementById('nome').value.trim();
    const cognome= document.getElementById('cognome').value.trim();
    const dn     = document.getElementById('data_nascita').value; // YYYY-MM-DD
    const sesso  = document.getElementById('sesso').value;
    const comune = document.getElementById('comune_nascita').value.trim().toLowerCase();

    if (!nome || !cognome || !dn || !sesso || !comune) {
        alert('Compila: Nome, Cognome, Data di nascita, Sesso e Comune di nascita per calcolare il CF.');
        return;
    }

    const codiceCatastale = CODICI_CATASTALI[comune];
    if (!codiceCatastale) {
        // Comune non trovato nella tabella locale
        const cfBox = document.getElementById('cfResult');
        cfBox.style.display = 'block';
        cfBox.style.background = '#fff7ed';
        cfBox.style.borderColor = '#fed7aa';
        cfBox.style.color = '#c2410c';
        cfBox.textContent = '⚠️ Comune "' + document.getElementById('comune_nascita').value + '" non trovato. Inserisci il CF manualmente o verifica il nome del comune.';
        return;
    }

    const [anno, mese, giorno] = dn.split('-');

    const cfCognome = codiceParte(cognome, false);
    const cfNome    = codiceParte(nome, true);
    const cfAnno    = anno.substring(2);
    const cfMese    = MESI[parseInt(mese) - 1];
    const cfGiorno  = sesso === 'M' ? giorno.padStart(2, '0') : String(parseInt(giorno) + 40).padStart(2, '0');

    // Parte senza carattere di controllo
    const base = cfCognome + cfNome + cfAnno + cfMese + cfGiorno + codiceCatastale;

    // Calcolo carattere di controllo
    let somma = 0;
    for (let i = 0; i < 15; i++) {
        const c = base[i];
        const val = /[0-9]/.test(c) ? parseInt(c) : c.charCodeAt(0) - 65;
        if (i % 2 === 0) { // posizione dispari (1-based) = indice pari (0-based)
            somma += TAB_DISPARI[val] ?? val;
        } else {
            somma += TAB_CONSONANTI[c] !== undefined ? TAB_CONSONANTI[c] : (TAB_VOCALI[c] !== undefined ? TAB_VOCALI[c] : val);
        }
    }
    const cf = base + String.fromCharCode(65 + (somma % 26));

    // Inserisci nel campo
    const cfInput = document.getElementById('codice_fiscale');
    cfInput.value = cf;
    cfInput.style.borderColor = '#86efac';
    cfInput.style.background = '#f0fdf4';

    const cfBox = document.getElementById('cfResult');
    cfBox.style.display = 'block';
    cfBox.style.background = '#f0fdf4';
    cfBox.style.borderColor = '#86efac';
    cfBox.style.color = '#15803d';
    cfBox.textContent = '✅ CF calcolato: ' + cf + ' — Verifica che sia corretto prima di salvare.';
}

// Auto-uppercase CF mentre si digita
document.getElementById('codice_fiscale').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
</body>
</html>
