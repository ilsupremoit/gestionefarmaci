{{-- resources/views/medico/pazienti/show.blade.php --}}
    <!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>PillMate — {{ $paziente->utente->cognome }} {{ $paziente->utente->nome }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/infoPaziente.css')

</head>
<body>

@php
    $utente     = $paziente->utente;
    $dispositivo = $paziente->dispositivi->where('stato','attivo')->first()
                  ?? $paziente->dispositivi->first();
    $hasAlarm   = $paziente->dispositivi->where('allarme_attivo', true)->isNotEmpty();
    $eta        = $paziente->data_nascita ? \Carbon\Carbon::parse($paziente->data_nascita)->age : null;
@endphp

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="pill"></i>
        </div>
        <span class="brand-name">PillMate</span>
    </div>

    <div class="nav-label">Menu</div>

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

    <div class="breadcrumb">
        <a href="{{ route('medico.dashboard') }}">Dashboard</a>
        <span class="sep">›</span>
        <a href="{{ route('medico.pazienti.index') }}">Pazienti</a>
        <span class="sep">›</span>
        <span class="current">{{ $utente->cognome }} {{ $utente->nome }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i data-lucide="circle-alert"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="patient-hero">
        <div class="hero-avatar">
            {{ strtoupper(substr($utente->nome,0,1)) }}{{ strtoupper(substr($utente->cognome,0,1)) }}
        </div>

        <div class="hero-info">
            <div class="hero-name">{{ $utente->cognome }} {{ $utente->nome }}</div>

            <div class="hero-meta">
                @if($eta)
                    <span><i data-lucide="cake"></i> {{ $eta }} anni</span>
                @endif
                @if($paziente->data_nascita)
                    <span><i data-lucide="calendar"></i> {{ $paziente->data_nascita->format('d/m/Y') }}</span>
                @endif
                @if($utente->email)
                    <span><i data-lucide="mail"></i> {{ $utente->email }}</span>
                @endif
                @if($utente->telefono)
                    <span><i data-lucide="phone"></i> {{ $utente->telefono }}</span>
                @endif
                @if($paziente->indirizzo)
                    <span><i data-lucide="map-pin"></i> {{ $paziente->indirizzo }}</span>
                @endif
                @if($paziente->codice_fiscale)
                    <span><i data-lucide="badge-info"></i> {{ $paziente->codice_fiscale }}</span>
                @endif
            </div>

            @if($paziente->note_mediche)
                <div style="margin-top:8px; font-size:12px; color:var(--muted); background:#f8fbff; padding:8px 12px; border-radius:8px; border:1px solid var(--border);">
                    <i data-lucide="notebook-pen" style="width:14px;height:14px;margin-right:6px;"></i>
                    {{ $paziente->note_mediche }}
                </div>
            @endif
        </div>

        <div class="hero-actions">
            @if($dispositivo)
                @if($hasAlarm)
                    <span class="device-badge alarm"><span class="dot red"></span>ALLARME ATTIVO</span>
                @elseif($dispositivo->stato === 'attivo')
                    <span class="device-badge attivo"><span class="dot green"></span>Dispositivo online</span>
                @else
                    <span class="device-badge offline"><span class="dot gray"></span>{{ ucfirst($dispositivo->stato) }}</span>
                @endif

                <form method="POST" action="{{ route('medico.pazienti.eroga', $paziente->id) }}" style="display:inline;" onsubmit="return confirm('Confermi l\'erogazione forzata della pillola?')">
                    @csrf
                    <button type="submit" class="btn-iot btn-eroga">
                        <i data-lucide="pill"></i>
                        Eroga ora
                    </button>
                </form>

                @if($hasAlarm)
                    <form method="POST" action="{{ route('medico.pazienti.allarme', $paziente->id) }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="attiva" value="0"/>
                        <button type="submit" class="btn-iot btn-alarm-off">
                            <i data-lucide="bell-off"></i>
                            Disattiva allarme
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('medico.pazienti.allarme', $paziente->id) }}" style="display:inline;" onsubmit="return confirm('Attivare l\'allarme sul dispositivo del paziente?')">
                        @csrf
                        <input type="hidden" name="attiva" value="1"/>
                        <button type="submit" class="btn-iot btn-alarm-on">
                            <i data-lucide="bell-ring"></i>
                            Attiva allarme
                        </button>
                    </form>
                @endif
            @else
                <span class="device-badge offline"><span class="dot gray"></span>Nessun dispositivo</span>
            @endif

            <a href="{{ route('medico.pazienti.index') }}" class="btn-back">
                <i data-lucide="arrow-left"></i>
                Torna alla lista
            </a>
        </div>
    </div>

    <div class="stat-row">
        <div class="stat-pill blue">
            <div class="val">{{ $stats['oggi_totali'] }}</div>
            <div class="lbl">Previste oggi</div>
        </div>
        <div class="stat-pill green">
            <div class="val">{{ $stats['oggi_prese'] }}</div>
            <div class="lbl">Prese</div>
        </div>
        <div class="stat-pill red">
            <div class="val">{{ $stats['oggi_saltate'] }}</div>
            <div class="lbl">Saltate</div>
        </div>
        <div class="stat-pill yellow">
            <div class="val">{{ $stats['oggi_attesa'] }}</div>
            <div class="lbl">In attesa</div>
        </div>
    </div>

    <div class="grid2">
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-title">
                <i data-lucide="clipboard-list"></i>
                Assunzioni — ultimi 7 giorni
                <span style="font-size:11px; color:var(--muted); font-weight:400; margin-left:auto;">Clicca sullo stato per modificarlo</span>
            </div>

            @if($assunzioni->isEmpty())
                <div style="text-align:center; color:var(--muted); padding:30px; font-size:13px;">Nessuna assunzione registrata negli ultimi 7 giorni.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Data prevista</th>
                            <th>Farmaco</th>
                            <th>Orario</th>
                            <th>Stato</th>
                            <th>Confermata da</th>
                            <th>Conferma</th>
                            <th>Dispositivo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assunzioni as $a)
                            @php
                                $farmaco = $a->somministrazione->terapia->farmaco ?? null;
                                $ora     = $a->somministrazione->ora ?? '--';
                            @endphp
                            <tr data-id="{{ $a->id }}">
                                <td>{{ \Carbon\Carbon::parse($a->data_prevista)->format('d/m/Y') }}</td>
                                <td style="font-weight:600;">{{ $farmaco->nome ?? 'N/A' }}
                                    @if($farmaco && $farmaco->dose)<span style="font-size:11px; color:var(--muted); margin-left:4px;">{{ $farmaco->dose }}</span>@endif
                                </td>
                                <td>{{ substr($ora, 0, 5) }}</td>
                                <td>
                            <span class="stato-badge stato-{{ $a->stato }}" onclick="cambiaStato({{ $a->id }}, '{{ $a->stato }}', this)">
                                {{ statoLabel($a->stato) }}
                            </span>
                                </td>
                                <td><span class="conf-badge">{{ $a->confermata_da }}</span></td>
                                <td style="font-size:12px; color:var(--muted);">
                                    {{ $a->data_conferma ? \Carbon\Carbon::parse($a->data_conferma)->format('d/m H:i') : '—' }}
                                </td>
                                <td style="font-size:12px; color:var(--muted);">
                                    {{ $a->dispositivo?->nome_dispositivo ?? $a->dispositivo?->codice_seriale ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card" id="dispositivi" style="margin-bottom:20px;">
        <div class="card-title">
            <i data-lucide="radio"></i>
            Dispositivi
            <button class="btn-add-terapia" onclick="toggleSection('formDispositivo')" style="margin-left:auto;">
                <i data-lucide="plus"></i>
                Aggiungi dispositivo
            </button>
        </div>

        <div class="form-add" id="formDispositivo">
            <form method="POST" action="{{ route('medico.pazienti.dispositivi.store', $paziente->id) }}">
                @csrf
                <div class="form-row">
                    <div>
                        <label>Codice seriale ESP32 *</label>
                        <input type="text" name="codice_seriale" placeholder="es. disp_01" required style="text-transform:lowercase"/>
                        <small style="color:var(--muted);font-size:11px;margin-top:4px;display:block;">Il codice deve corrispondere all'ID configurato nell'ESP32 (es. <code>disp_01</code>). Il topic MQTT sarà <code>pillmate/{codice}/...</code></small>
                    </div>
                    <div>
                        <label>Nome dispositivo</label>
                        <input type="text" name="nome_dispositivo" placeholder="es. Dispenser camera da letto"/>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="submit" class="btn-submit">
                        <i data-lucide="save"></i>
                        Aggiungi
                    </button>
                    <button type="button" onclick="toggleSection('formDispositivo')" style="padding:10px 16px;background:transparent;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:13px;cursor:pointer;font-family:inherit;">Annulla</button>
                </div>
            </form>
        </div>

        @forelse($paziente->dispositivi as $d)
            @php
                $minutiFa = $d->ultima_connessione ? \Carbon\Carbon::parse($d->ultima_connessione)->diffInMinutes(now()) : null;
                $online   = $d->stato === 'attivo' && $minutiFa !== null && $minutiFa <= 5;
            @endphp
            <div style="display:flex;align-items:center;gap:14px;padding:14px 0;border-bottom:1px solid var(--border);">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#dbeafe,#cffafe);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--accent);">
                    <i data-lucide="pill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:14px;">{{ $d->nome_dispositivo ?? 'PillMate Dispenser' }}</div>
                    <div style="font-size:11px;color:var(--muted);">S/N: {{ $d->codice_seriale }} · Topic: <code>pillmate/{{ $d->codice_seriale }}/...</code></div>
                </div>
                @if($d->allarme_attivo)
                    <span class="device-badge alarm"><span class="dot red"></span>ALLARME</span>
                @elseif($online)
                    <span class="device-badge attivo"><span class="dot green"></span>Online</span>
                @else
                    <span class="device-badge offline"><span class="dot gray"></span>{{ ucfirst($d->stato) }}</span>
                @endif
                @if($d->temperatura)<span style="font-size:12px;color:var(--muted);"><i data-lucide="thermometer" style="width:13px;height:13px;margin-right:4px;"></i>{{ $d->temperatura }}°C</span>@endif
                @if($d->batteria !== null)<span style="font-size:12px;color:{{ $d->batteria < 20 ? 'var(--red)' : 'var(--muted)' }};"><i data-lucide="battery" style="width:13px;height:13px;margin-right:4px;"></i>{{ $d->batteria }}%</span>@endif
                <a href="{{ route('medico.pazienti.dispositivi.show', [$paziente->id, $d->id]) }}" class="btn-add-terapia" style="text-decoration:none;">
                    <i data-lucide="settings-2"></i>
                    Gestisci
                </a>
            </div>
        @empty
            <div style="text-align:center;color:var(--muted);padding:20px;font-size:13px;">Nessun dispositivo associato. Aggiungine uno con il pulsante sopra.</div>
        @endforelse
    </div>

    <div class="card" id="messaggi" style="margin-bottom:20px;">
        <div class="card-title">
            <i data-lucide="message-square"></i>
            Invia messaggio al paziente
        </div>
        <form method="POST" action="{{ route('medico.notifiche.invia') }}" style="display:grid;gap:10px;">
            @csrf
            <input type="hidden" name="id_utente" value="{{ $paziente->utente->id }}"/>
            <div class="form-row">
                <div>
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="info">Info</option>
                        <option value="promemoria">Promemoria</option>
                        <option value="allarme">Avviso urgente</option>
                        <option value="messaggio">Messaggio</option>
                    </select>
                </div>
                <div>
                    <label>Oggetto *</label>
                    <input type="text" name="titolo" placeholder="es. Ricordati di prendere la pillola" required/>
                </div>
            </div>
            <div>
                <label>Testo del messaggio *</label>
                <textarea name="messaggio" rows="3" placeholder="Scrivi qui il tuo messaggio per il paziente..." required></textarea>
            </div>
            <div>
                <button type="submit" class="btn-submit">
                    <i data-lucide="send"></i>
                    Invia notifica
                </button>
            </div>
        </form>
    </div>

    <div class="card" id="terapie">
        <div class="card-title">
            <i data-lucide="pill"></i>
            Terapie
            <button class="btn-add-terapia" onclick="toggleForm()" style="margin-left:auto;">
                <i data-lucide="plus"></i>
                Aggiungi terapia
            </button>
        </div>

        <div class="form-add" id="formTerapia">
            <form method="POST" action="{{ route('medico.pazienti.terapie.store', $paziente->id) }}">
                @csrf
                <div class="form-row">
                    <div>
                        <label>Farmaco *</label>
                        <select name="id_farmaco" required>
                            <option value="">— Seleziona farmaco —</option>
                            @foreach(\App\Models\Farmaco::orderBy('nome')->get() as $farmaco)
                                <option value="{{ $farmaco->id }}">{{ $farmaco->nome }}{{ $farmaco->dose ? ' ('.$farmaco->dose.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Quantità (pillole) *</label>
                        <input type="number" name="quantita" min="1" value="1" required/>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Data inizio *</label>
                        <input type="date" name="data_inizio" value="{{ date('Y-m-d') }}" required/>
                    </div>
                    <div>
                        <label>Data fine</label>
                        <input type="date" name="data_fine"/>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Orario somministrazione *</label>
                        <input type="time" name="ora" required/>
                    </div>
                    <div>
                        <label>Frequenza (es. ogni 8h)</label>
                        <input type="text" name="frequenza" placeholder="es. ogni 8 ore"/>
                    </div>
                </div>
                <div class="form-row full" style="margin-bottom:12px;">
                    <div>
                        <label>Giorni somministrazione *</label>
                        <div class="giorni-check">
                            @foreach(['Tutti','Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $g)
                                <label><input type="checkbox" name="giorni[]" value="{{ $g }}" {{ $g==='Tutti'?'checked':'' }}/> {{ $g }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-row full">
                    <div>
                        <label>Istruzioni</label>
                        <textarea name="istruzioni" rows="2" placeholder="es. da prendere a stomaco pieno..."></textarea>
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-submit">
                        <i data-lucide="save"></i>
                        Salva terapia
                    </button>
                    <button type="button" onclick="toggleForm()" style="padding:10px 16px; background:transparent; border:1px solid var(--border); border-radius:8px; color:var(--muted); font-size:13px; cursor:pointer; font-family:inherit;">Annulla</button>
                </div>
            </form>
        </div>

        @forelse($paziente->terapie as $terapia)
            <div class="terapia-item">
                <div class="terapia-header">
                    <div class="terapia-name">
                        <i data-lucide="pill" style="width:14px;height:14px;margin-right:4px;"></i>
                        {{ $terapia->farmaco->nome ?? 'Farmaco sconosciuto' }}
                        @if($terapia->farmaco?->dose)
                            <span style="font-size:12px; color:var(--muted); font-weight:400;"> — {{ $terapia->farmaco->dose }}</span>
                        @endif
                    </div>
                    <span class="{{ $terapia->attiva ? 'badge-attiva' : 'badge-inattiva' }}">{{ $terapia->attiva ? 'Attiva' : 'Conclusa' }}</span>
                </div>
                <div class="terapia-meta">
                    <i data-lucide="calendar" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->data_inizio->format('d/m/Y') }}
                    @if($terapia->data_fine) → {{ $terapia->data_fine->format('d/m/Y') }} @endif
                    &nbsp;·&nbsp;
                    <i data-lucide="capsule" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->quantita }} pillola/e
                    @if($terapia->frequenza)&nbsp;·&nbsp; <i data-lucide="clock-3" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->frequenza }}@endif
                </div>
                @if($terapia->istruzioni)
                    <div style="margin-top:6px; font-size:12px; color:var(--muted);">
                        <i data-lucide="notebook-pen" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->istruzioni }}
                    </div>
                @endif
                @if($terapia->somministrazioni->count())
                    <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:6px;">
                        @foreach($terapia->somministrazioni as $somm)
                            <span style="font-size:11px; background:rgba(37,99,235,.08); border:1px solid rgba(37,99,235,.16); color:var(--accent); padding:2px 8px; border-radius:10px;">
                    {{ $somm->giorno_settimana }} {{ substr($somm->ora,0,5) }}
                </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align:center; color:var(--muted); padding:24px; font-size:13px;">Nessuna terapia registrata. Usa il pulsante per aggiungerne una.</div>
        @endforelse
    </div>

</main>

<div class="toast" id="toast"></div>

<script>
    const STATI_LABELS = {
        'assunta': 'Assunta',
        'erogata': 'Erogata',
        'saltata': 'Saltata',
        'non_ritirata': 'Non ritirata',
        'in_attesa': 'In attesa',
        'ritardo': 'Ritardo',
        'allarme_attivo': 'Allarme',
        'apertura_forzata': 'Forzata',
    };

    const STATI_SEQUENZA = ['in_attesa', 'assunta', 'saltata', 'non_ritirata'];

    function cambiaStato(id, statoAttuale, el) {
        const idx = STATI_SEQUENZA.indexOf(statoAttuale);
        const prossimo = STATI_SEQUENZA[(idx + 1) % STATI_SEQUENZA.length];

        if (!confirm(`Cambia stato: "${STATI_LABELS[statoAttuale] ?? statoAttuale}" → "${STATI_LABELS[prossimo] ?? prossimo}"?`)) return;

        fetch(`/medico/assunzioni/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ stato: prossimo }),
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    el.className = `stato-badge stato-${data.stato}`;
                    el.textContent = STATI_LABELS[data.stato] ?? data.stato;
                    el.setAttribute('onclick', `cambiaStato(${id}, '${data.stato}', this)`);
                    showToast('Stato aggiornato: ' + (STATI_LABELS[data.stato] ?? data.stato), 'success');
                    aggiornaContatori();
                } else {
                    showToast('Errore aggiornamento', 'error');
                }
            })
            .catch(() => showToast('Errore di rete', 'error'));
    }

    function aggiornaContatori() {
        const righe = document.querySelectorAll('tbody tr');
        let prese=0, saltate=0, attesa=0;
        righe.forEach(r => {
            const badge = r.querySelector('.stato-badge');
            if (!badge) return;
            const cl = [...badge.classList].find(c => c.startsWith('stato-'))?.replace('stato-','');
            if (['assunta','erogata'].includes(cl)) prese++;
            else if (['saltata','non_ritirata'].includes(cl)) saltate++;
            else if (cl === 'in_attesa') attesa++;
        });
        document.querySelectorAll('.stat-pill').forEach(p => {
            const lbl = p.querySelector('.lbl')?.textContent.toLowerCase();
            if (lbl?.includes('prese')) p.querySelector('.val').textContent = prese;
            if (lbl?.includes('saltate')) p.querySelector('.val').textContent = saltate;
            if (lbl?.includes('attesa')) p.querySelector('.val').textContent = attesa;
        });
    }

    function showToast(msg, type='success') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    function toggleForm() {
        document.getElementById('formTerapia').classList.toggle('open');
    }

    function toggleSection(id) {
        document.getElementById(id).classList.toggle('open');
    }

    document.querySelectorAll('.giorni-check input').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.value === 'Tutti' && this.checked) {
                document.querySelectorAll('.giorni-check input:not([value=Tutti])').forEach(x => x.checked = false);
            } else if (this.value !== 'Tutti' && this.checked) {
                const tutti = document.querySelector('.giorni-check input[value=Tutti]');
                if (tutti) tutti.checked = false;
            }
        });
    });
</script>

@php
    function statoLabel(string $stato): string {
        return match($stato) {
            'assunta'         => 'Assunta',
            'erogata'         => 'Erogata',
            'saltata'         => 'Saltata',
            'non_ritirata'    => 'Non ritirata',
            'in_attesa'       => 'In attesa',
            'ritardo'         => 'Ritardo',
            'allarme_attivo'  => 'Allarme attivo',
            'apertura_forzata'=> 'Erogazione forzata',
            default           => ucfirst($stato),
        };
    }
@endphp

</body>
</html>
