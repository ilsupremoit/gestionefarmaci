<!DOCTYPE html>
<html lang="it">
<head>
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
            <h1>➕ Crea nuovo utente</h1>
            <p>Crea un account per medico, paziente, familiare o altro admin</p>
        </div>
        <a href="{{ route('admin.utenti') }}" class="btn btn-ghost">← Torna agli utenti</a>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <strong>⚠️ Controlla i campi:</strong>
            <ul style="margin-top:6px;padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('admin.utenti.store') }}" id="formCrea">
            @csrf

            {{-- Tipo ruolo --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:24px;">
                @foreach(['medico'=>'👨‍⚕️ Medico','paziente'=>'🧑‍🦯 Paziente','familiare'=>'👨‍👩‍👧 Familiare','admin'=>'🛡️ Admin'] as $r => $label)
                <label style="border:2px solid var(--border);border-radius:12px;padding:14px;text-align:center;cursor:pointer;transition:all .15s;font-weight:600;font-size:14px;" id="lbl-{{ $r }}">
                    <input type="radio" name="ruolo" value="{{ $r }}" {{ old('ruolo','medico')===$r ? 'checked':'' }}
                           style="display:none;" onchange="setRuolo('{{ $r }}')"/>
                    {{ $label }}
                </label>
                @endforeach
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="field">
                    <label>Nome *</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" placeholder="es. Mario" required/>
                </div>
                <div class="field">
                    <label>Cognome *</label>
                    <input type="text" name="cognome" value="{{ old('cognome') }}" placeholder="es. Rossi" required/>
                </div>
                <div class="field">
                    <label>Username *</label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="es. mario.rossi" required/>
                    <div class="hint">Usato per il login</div>
                </div>
                <div class="field">
                    <label>Password *</label>
                    <input type="text" name="password" value="{{ old('password') }}" placeholder="min. 6 caratteri" required minlength="6"/>
                </div>
                <div class="field">
                    <label>Email <span style="font-weight:400;color:#94a3b8;font-size:10px;">(opzionale)</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="es. mario@email.it"/>
                </div>
                <div class="field">
                    <label>Telefono <span style="font-weight:400;color:#94a3b8;font-size:10px;">(opzionale)</span></label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" placeholder="es. 3331234567"/>
                </div>

                {{-- Campi extra paziente --}}
                <div id="campoPaziente" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;grid-column:1/-1;{{ old('ruolo','medico') !== 'paziente' ? '' : '' }}">
                    <div class="field">
                        <label>Data di nascita</label>
                        <input type="date" name="data_nascita" value="{{ old('data_nascita') }}"/>
                    </div>
                    <div class="field">
                        <label>Codice fiscale</label>
                        <input type="text" name="codice_fiscale" value="{{ old('codice_fiscale') }}" placeholder="es. RSSMRA80A01H501Z" maxlength="16" style="text-transform:uppercase"/>
                    </div>
                    <div class="field">
                        <label>Indirizzo</label>
                        <input type="text" name="indirizzo" value="{{ old('indirizzo') }}" placeholder="es. Via Roma 1, Milano"/>
                    </div>
                    <div class="field">
                        <label>Note mediche</label>
                        <textarea name="note_mediche" placeholder="Allergie, patologie note...">{{ old('note_mediche') }}</textarea>
                    </div>
                </div>

                <div class="field" style="grid-column:1/-1;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;text-transform:none;letter-spacing:0;">
                        <input type="checkbox" name="must_change_password" value="1" {{ old('must_change_password','1') ? 'checked':'' }}
                               style="width:16px;height:16px;accent-color:var(--accent);"/>
                        Obbliga il cambio password al primo accesso
                    </label>
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                <button type="submit" class="btn btn-primary">✅ Crea utente</button>
                <a href="{{ route('admin.utenti') }}" class="btn btn-ghost">Annulla</a>
            </div>
        </form>
    </div>
</main>

<script>
const RUOLI = ['medico','paziente','familiare','admin'];
function setRuolo(r) {
    RUOLI.forEach(x => {
        const lbl = document.getElementById('lbl-' + x);
        lbl.style.borderColor = x === r ? 'var(--accent)' : 'var(--border)';
        lbl.style.background  = x === r ? '#f5f3ff' : '#fff';
        lbl.style.color       = x === r ? 'var(--accent)' : 'var(--text)';
    });
    // Mostra/nascondi campi extra paziente
    document.getElementById('campoPaziente').style.display = r === 'paziente' ? 'grid' : 'none';
}
// Init
setRuolo('{{ old('ruolo','medico') }}');
</script>
</body>
</html>
