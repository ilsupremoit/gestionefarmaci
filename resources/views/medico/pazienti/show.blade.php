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
    @vite('resources/css/medico/infoPaziente.css')
</head>
<body>

@php
    $utente      = $paziente->utente;
    $dispositivo = $paziente->dispositivi->where('stato','attivo')->first()
                   ?? $paziente->dispositivi->first();
    $hasAlarm    = $paziente->dispositivi->where('allarme_attivo', true)->isNotEmpty();
    $eta         = $paziente->data_nascita ? \Carbon\Carbon::parse($paziente->data_nascita)->age : null;
@endphp

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i data-lucide="pill"></i></div>
        <span class="brand-name">PillMate</span>
    </div>
    <div class="nav-label">Menu</div>
    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <span class="ico"><i data-lucide="layout-dashboard"></i></span>Dashboard
    </a>
    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">
        <span class="ico"><i data-lucide="users"></i></span>I miei pazienti
    </a>
    <a class="nav-item" href="{{ route('medico.notifiche') }}">
        <span class="ico"><i data-lucide="bell"></i></span>Notifiche
    </a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nome,0,1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->nome }} {{ auth()->user()->cognome }}</div>
                <div class="user-role"><i data-lucide="user-round"></i> Medico</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout"><i data-lucide="log-out"></i> Esci</button>
        </form>
    </div>
</aside>
@include('medico._mobile-nav', ['active' => 'pazienti'])

<main class="main">

    <div class="breadcrumb">
        <a href="{{ route('medico.dashboard') }}">Dashboard</a>
        <span class="sep">›</span>
        <a href="{{ route('medico.pazienti.index') }}">Pazienti</a>
        <span class="sep">›</span>
        <span class="current">{{ $utente->cognome }} {{ $utente->nome }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success"><i data-lucide="check-circle-2"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error"><i data-lucide="circle-alert"></i> {{ session('error') }}</div>
    @endif

    {{-- ── Hero paziente ─────────────────────────────────────────────── --}}
    <div class="patient-hero">
        <div class="hero-avatar">
            {{ strtoupper(substr($utente->nome,0,1)) }}{{ strtoupper(substr($utente->cognome,0,1)) }}
        </div>
        <div class="hero-info">
            <div class="hero-name">{{ $utente->cognome }} {{ $utente->nome }}</div>
            <div class="hero-meta">
                @if($eta)<span><i data-lucide="cake"></i> {{ $eta }} anni</span>@endif
                @if($paziente->data_nascita)<span><i data-lucide="calendar"></i> {{ $paziente->data_nascita->format('d/m/Y') }}</span>@endif
                @if($utente->email)<span><i data-lucide="mail"></i> {{ $utente->email }}</span>@endif
                @if($utente->telefono)<span><i data-lucide="phone"></i> {{ $utente->telefono }}</span>@endif
                @if($paziente->indirizzo)<span><i data-lucide="map-pin"></i> {{ $paziente->indirizzo }}</span>@endif
                @if($paziente->codice_fiscale)<span><i data-lucide="badge-info"></i> {{ $paziente->codice_fiscale }}</span>@endif
            </div>
            @if($paziente->note_mediche)
                <div style="margin-top:8px;font-size:12px;color:var(--muted);background:#f8fbff;padding:8px 12px;border-radius:8px;border:1px solid var(--border);">
                    <i data-lucide="notebook-pen" style="width:14px;height:14px;margin-right:6px;"></i>{{ $paziente->note_mediche }}
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

                <form method="POST" action="{{ route('medico.pazienti.eroga', $paziente->id) }}" style="display:inline;"
                      onsubmit="return confirm('Confermi l\'erogazione forzata?')">
                    @csrf
                    <button type="submit" class="btn-iot btn-eroga"><i data-lucide="pill"></i> Eroga ora</button>
                </form>

                @if($hasAlarm)
                    <form method="POST" action="{{ route('medico.pazienti.allarme', $paziente->id) }}" style="display:inline;">
                        @csrf <input type="hidden" name="attiva" value="0"/>
                        <button type="submit" class="btn-iot btn-alarm-off"><i data-lucide="bell-off"></i> Disattiva allarme</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('medico.pazienti.allarme', $paziente->id) }}" style="display:inline;"
                          onsubmit="return confirm('Attivare l\'allarme?')">
                        @csrf <input type="hidden" name="attiva" value="1"/>
                        <button type="submit" class="btn-iot btn-alarm-on"><i data-lucide="bell-ring"></i> Attiva allarme</button>
                    </form>
                @endif
            @else
                <span class="device-badge offline"><span class="dot gray"></span>Nessun dispositivo</span>
            @endif
            <a href="{{ route('medico.pazienti.index') }}" class="btn-back"><i data-lucide="arrow-left"></i> Torna alla lista</a>
        </div>
    </div>

    {{-- ── Pill stat cliccabili ──────────────────────────────────────── --}}
    <div class="stat-row">
        <a href="{{ route('medico.pazienti.storico', [$paziente->id, 'oggi']) }}" class="stat-pill blue stat-link">
            <div class="val">{{ $stats['oggi'] }}</div>
            <div class="lbl">Previste oggi</div>
            <div class="stat-hint">Vedi storico →</div>
        </a>
        <a href="{{ route('medico.pazienti.storico', [$paziente->id, 'prese']) }}" class="stat-pill green stat-link">
            <div class="val">{{ $stats['prese'] }}</div>
            <div class="lbl">Prese</div>
            <div class="stat-hint">Vedi storico →</div>
        </a>
        <a href="{{ route('medico.pazienti.storico', [$paziente->id, 'saltate']) }}" class="stat-pill red stat-link">
            <div class="val">{{ $stats['saltate'] }}</div>
            <div class="lbl">Saltate</div>
            <div class="stat-hint">Vedi storico →</div>
        </a>
        <a href="{{ route('medico.pazienti.storico', [$paziente->id, 'forzate']) }}" class="stat-pill orange stat-link">
            <div class="val">{{ $stats['forzate'] }}</div>
            <div class="lbl">Forzate medico</div>
            <div class="stat-hint">Vedi storico →</div>
        </a>
    </div>

    {{-- ── Dispositivi ────────────────────────────────────────────────── --}}
    <div class="card" id="dispositivi" style="margin-bottom:20px;">
        <div class="card-title">
            <i data-lucide="radio"></i> Dispositivi
            <button class="btn-add-terapia" onclick="toggleSection('formDispositivo')" style="margin-left:auto;">
                <i data-lucide="plus"></i> Aggiungi dispositivo
            </button>
        </div>
        <div class="form-add" id="formDispositivo">
            <form method="POST" action="{{ route('medico.pazienti.dispositivi.store', $paziente->id) }}">
                @csrf
                <div class="form-row">
                    <div>
                        <label>Codice seriale ESP32 *</label>
                        <input type="text" name="codice_seriale" required style="text-transform:lowercase"/>
                        <small style="color:var(--muted);font-size:11px;margin-top:4px;display:block;">Es. <code>disp_01</code> → topic MQTT: <code>pillmate/disp_01/...</code></small>
                    </div>
                    <div>
                        <label>Nome dispositivo</label>
                        <input type="text" name="nome_dispositivo"/>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="submit" class="btn-submit"><i data-lucide="save"></i> Aggiungi</button>
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
                    <i data-lucide="settings-2"></i> Gestisci
                </a>
            </div>
        @empty
            <div style="text-align:center;color:var(--muted);padding:20px;font-size:13px;">Nessun dispositivo. Aggiungine uno con il pulsante sopra.</div>
        @endforelse
    </div>

    {{-- ── Messaggi ───────────────────────────────────────────────────── --}}
    <div class="card" id="messaggi" style="margin-bottom:20px;">
        <div class="card-title"><i data-lucide="message-square"></i> Invia messaggio al paziente</div>
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
                    <input type="text" name="titolo" required/>
                </div>
            </div>
            <div>
                <label>Testo *</label>
                <textarea name="messaggio" rows="3" required></textarea>
            </div>
            <div>
                <button type="submit" class="btn-submit"><i data-lucide="send"></i> Invia notifica</button>
            </div>
        </form>
    </div>

    {{-- ── Terapie ─────────────────────────────────────────────────────── --}}
    <div class="card" id="terapie">
        <div class="card-title">
            <i data-lucide="pill"></i> Terapie
            <button class="btn-add-terapia" onclick="toggleForm()" style="margin-left:auto;">
                <i data-lucide="plus"></i> Aggiungi terapia
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
                        <label>Quantità (pillole per dose) *</label>
                        <input type="number" name="quantita" min="1" value="1" required/>
                    </div>
                </div>
                <div class="form-row">
                    <div><label>Data inizio *</label><input type="date" name="data_inizio" value="{{ date('Y-m-d') }}" required/></div>
                    <div><label>Data fine</label><input type="date" name="data_fine"/></div>
                </div>
                <div class="form-row">
                    <div><label>Orario somministrazione *</label><input type="time" name="ora" required/></div>
                    <div><label>Frequenza</label><input type="text" name="frequenza" placeholder="es. ogni 8 ore"/></div>
                </div>
                <div class="form-row full" style="margin-bottom:12px;">
                    <div>
                        <label>Giorni *</label>
                        <div class="giorni-check">
                            @foreach(['Tutti','Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $g)
                                <label><input type="checkbox" name="giorni[]" value="{{ $g }}" {{ $g==='Tutti'?'checked':'' }}/> {{ $g }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-row full">
                    <div><label>Istruzioni</label><textarea name="istruzioni" rows="2" placeholder="es. a stomaco pieno..."></textarea></div>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-submit"><i data-lucide="save"></i> Salva terapia</button>
                    <button type="button" onclick="toggleForm()" style="padding:10px 16px;background:transparent;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:13px;cursor:pointer;font-family:inherit;">Annulla</button>
                </div>
            </form>
        </div>

        @forelse($paziente->terapie as $terapia)
            <div class="terapia-item" id="terapia-{{ $terapia->id }}">
                <div class="terapia-header">
                    <div class="terapia-name">
                        <i data-lucide="pill" style="width:14px;height:14px;margin-right:4px;"></i>
                        {{ $terapia->farmaco->nome ?? 'Farmaco sconosciuto' }}
                        @if($terapia->farmaco?->dose)
                            <span style="font-size:12px;color:var(--muted);font-weight:400;"> — {{ $terapia->farmaco->dose }}</span>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="{{ $terapia->attiva ? 'badge-attiva' : 'badge-inattiva' }}">{{ $terapia->attiva ? 'Attiva' : 'Conclusa' }}</span>
                        {{-- Bottone Modifica --}}
                        <button onclick="apriModificaTerapia({{ $terapia->id }})" title="Modifica" style="background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.2);border-radius:7px;padding:5px 10px;cursor:pointer;color:var(--accent);font-size:12px;display:inline-flex;align-items:center;gap:4px;">
                            <i data-lucide="pencil" style="width:13px;height:13px;"></i> Modifica
                        </button>
                        {{-- Bottone Elimina / Disattiva --}}
                        <form method="POST" action="{{ route('medico.pazienti.terapie.destroy', [$paziente->id, $terapia->id]) }}" onsubmit="return confirm('Disattivare la terapia {{ addslashes($terapia->farmaco?->nome ?? '') }}? Lo storico sarà conservato.')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" title="Disattiva" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:7px;padding:5px 10px;cursor:pointer;color:var(--red);font-size:12px;display:inline-flex;align-items:center;gap:4px;">
                                <i data-lucide="trash-2" style="width:13px;height:13px;"></i> Disattiva
                            </button>
                        </form>
                    </div>
                </div>
                <div class="terapia-meta">
                    <i data-lucide="calendar" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->data_inizio->format('d/m/Y') }}
                    @if($terapia->data_fine) → {{ $terapia->data_fine->format('d/m/Y') }} @endif
                    &nbsp;·&nbsp;
                    <i data-lucide="capsule" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->quantita }} pillola/e per dose
                    @if($terapia->frequenza)&nbsp;·&nbsp;<i data-lucide="clock-3" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->frequenza }}@endif
                </div>
                @if($terapia->istruzioni)
                    <div style="margin-top:6px;font-size:12px;color:var(--muted);">
                        <i data-lucide="notebook-pen" style="width:13px;height:13px;margin-right:4px;"></i>{{ $terapia->istruzioni }}
                    </div>
                @endif
                @if($terapia->somministrazioni->count())
                    <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach($terapia->somministrazioni as $somm)
                            <span style="font-size:11px;background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.16);color:var(--accent);padding:2px 8px;border-radius:10px;">
                                {{ $somm->giorno_settimana }} {{ substr($somm->ora,0,5) }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Form modifica inline (nascosto) --}}
                <div class="form-add" id="formModifica-{{ $terapia->id }}" style="margin-top:14px;">
                    <form method="POST" action="{{ route('medico.pazienti.terapie.update', [$paziente->id, $terapia->id]) }}">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div>
                                <label>Farmaco *</label>
                                <select name="id_farmaco" required>
                                    @foreach(\App\Models\Farmaco::orderBy('nome')->get() as $f)
                                        <option value="{{ $f->id }}" {{ $f->id == $terapia->id_farmaco ? 'selected' : '' }}>{{ $f->nome }}{{ $f->dose ? ' ('.$f->dose.')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label>Quantità *</label><input type="number" name="quantita" min="1" value="{{ $terapia->quantita }}" required/></div>
                        </div>
                        <div class="form-row">
                            <div><label>Data inizio *</label><input type="date" name="data_inizio" value="{{ $terapia->data_inizio->format('Y-m-d') }}" required/></div>
                            <div><label>Data fine</label><input type="date" name="data_fine" value="{{ $terapia->data_fine?->format('Y-m-d') }}"/></div>
                        </div>
                        <div class="form-row">
                            <div><label>Orario *</label><input type="time" name="ora" value="{{ substr($terapia->somministrazioni->first()?->ora ?? '08:00',0,5) }}" required/></div>
                            <div><label>Frequenza</label><input type="text" name="frequenza" value="{{ $terapia->frequenza }}"/></div>
                        </div>
                        <div class="form-row full" style="margin-bottom:12px;">
                            <div>
                                <label>Giorni *</label>
                                <div class="giorni-check">
                                    @foreach(['Tutti','Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $g)
                                        <label><input type="checkbox" name="giorni[]" value="{{ $g }}"
                                            {{ $terapia->somministrazioni->contains('giorno_settimana', $g) ? 'checked' : '' }}/> {{ $g }}</label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="form-row full">
                            <div><label>Istruzioni</label><textarea name="istruzioni" rows="2">{{ $terapia->istruzioni }}</textarea></div>
                        </div>
                        <div style="display:flex;gap:10px;">
                            <button type="submit" class="btn-submit"><i data-lucide="save"></i> Salva modifiche</button>
                            <button type="button" onclick="chiudiModificaTerapia({{ $terapia->id }})" style="padding:10px 16px;background:transparent;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:13px;cursor:pointer;font-family:inherit;">Annulla</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align:center;color:var(--muted);padding:24px;font-size:13px;">Nessuna terapia registrata.</div>
        @endforelse
    </div>

</main>

<div class="toast" id="toast"></div>

<script>
    function toggleForm()         { document.getElementById('formTerapia').classList.toggle('open'); }
    function toggleSection(id)    { document.getElementById(id).classList.toggle('open'); }

    function apriModificaTerapia(id)  { document.getElementById('formModifica-' + id).classList.add('open'); }
    function chiudiModificaTerapia(id){ document.getElementById('formModifica-' + id).classList.remove('open'); }

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
</body>
</html>
