{{-- resources/views/medico/pazienti/storico.blade.php --}}

@php
    $utente = $paziente->utente;

    $tipoMeta = array_replace_recursive([
        'oggi' => [
            'label' => 'Previste oggi',
            'icon' => 'calendar-clock',
        ],
        'prese' => [
            'label' => 'Prese',
            'icon' => 'check-circle-2',
        ],
        'saltate' => [
            'label' => 'Saltate',
            'icon' => 'x-circle',
        ],
        'attesa' => [
            'label' => 'In attesa',
            'icon' => 'clock',
        ],
        'forzate' => [
            'label' => 'Erogazioni forzate',
            'icon' => 'alert-triangle',
        ],
    ], $tipoMeta ?? []);

    $metaCorrente = $tipoMeta[$tipo] ?? [
        'label' => 'Storico',
        'icon' => 'history',
    ];

    function statoLabel(string $stato): string {
        return match($stato) {
            'assunta'          => 'Assunta',
            'erogata'          => 'Erogata',
            'saltata'          => 'Saltata',
            'non_ritirata'     => 'Non ritirata',
            'in_attesa'        => 'In attesa',
            'ritardo'          => 'Ritardo',
            'allarme_attivo'   => 'Allarme attivo',
            'apertura_forzata' => 'Erogazione forzata',
            default            => ucfirst($stato),
        };
    }
@endphp

<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>PillMate — Storico {{ $metaCorrente['label'] }} — {{ $paziente->utente->cognome }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/medico/infoPaziente.css')

    <style>
        /* ── Storico-specific styles ─────────────────────────────── */
        .storico-nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .snav-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            border: 2px solid transparent;
            transition: all .18s;
            color: var(--muted);
            background: var(--surface);
            border-color: var(--border);
        }

        .snav-pill:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .snav-pill.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .snav-pill.green {
            --pill-c: var(--green);
        }

        .snav-pill.red {
            --pill-c: var(--red);
        }

        .snav-pill.yellow {
            --pill-c: var(--yellow);
        }

        .snav-pill.orange {
            --pill-c: #ea580c;
        }

        .snav-pill.blue {
            --pill-c: var(--accent);
        }

        .snav-pill.active.green {
            background: var(--green);
            border-color: var(--green);
        }

        .snav-pill.active.red {
            background: var(--red);
            border-color: var(--red);
        }

        .snav-pill.active.yellow {
            background: var(--yellow);
            border-color: var(--yellow);
        }

        .snav-pill.active.orange {
            background: #ea580c;
            border-color: #ea580c;
        }

        .snav-pill .badge-n {
            background: rgba(255,255,255,.25);
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }

        .snav-pill:not(.active) .badge-n {
            background: var(--border);
            color: var(--text);
        }

        /* ── Tabella ─────────────────────────────────────────────── */
        .storico-table-wrap {
            overflow-x: auto;
        }

        .storico-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .storico-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--muted);
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }

        .storico-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text);
        }

        .storico-table tr:last-child td {
            border-bottom: none;
        }

        .storico-table tr:hover td {
            background: #f8fbff;
        }

        /* ── Stato badge ─────────────────────────────────────────── */
        .stato-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }

        .stato-assunta,
        .stato-erogata {
            background: #dcfce7;
            color: #166534;
        }

        .stato-saltata,
        .stato-non_ritirata {
            background: #fee2e2;
            color: #991b1b;
        }

        .stato-in_attesa {
            background: #fef9c3;
            color: #854d0e;
        }

        .stato-allarme_attivo {
            background: #ffedd5;
            color: #9a3412;
        }

        .stato-apertura_forzata {
            background: #ede9fe;
            color: #5b21b6;
        }

        .stato-ritardo {
            background: #fef3c7;
            color: #92400e;
        }

        /* ── Forzata badge ───────────────────────────────────────── */
        .forzata-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            background: #ede9fe;
            color: #5b21b6;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
        }

        /* ── Azione btn ──────────────────────────────────────────── */
        .btn-allarme-singolo {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--accent);
            cursor: pointer;
            font-family: inherit;
            transition: all .15s;
        }

        .btn-allarme-singolo:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        /* ── Empty state ─────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        .empty-state i[data-lucide] {
            width: 48px;
            height: 48px;
            stroke-width: 1.2;
            margin-bottom: 16px;
            opacity: .4;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* ── stat-pill orange ────────────────────────────────────── */
        .stat-pill.orange {
            background: linear-gradient(135deg,#fff7ed,#ffedd5);
            border-color: #fed7aa;
            color: #9a3412;
        }

        .stat-link {
            text-decoration: none;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
        }

        .stat-link:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-hint {
            font-size: 10px;
            color: var(--muted);
            margin-top: 4px;
            opacity: .8;
        }
    </style>
</head>

<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i data-lucide="pill"></i>
        </div>
        <span class="brand-name">PillMate</span>
    </div>

    <div class="nav-label">Menu</div>

    <a class="nav-item" href="{{ route('medico.dashboard') }}">
        <span class="ico">
            <i data-lucide="layout-dashboard"></i>
        </span>
        Dashboard
    </a>

    <a class="nav-item active" href="{{ route('medico.pazienti.index') }}">
        <span class="ico">
            <i data-lucide="users"></i>
        </span>
        I miei pazienti
    </a>

    <a class="nav-item" href="{{ route('medico.notifiche') }}">
        <span class="ico">
            <i data-lucide="bell"></i>
        </span>
        Notifiche
    </a>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
            </div>
            <div>
                <div class="user-name">
                    {{ auth()->user()->nome }} {{ auth()->user()->cognome }}
                </div>
                <div class="user-role">
                    <i data-lucide="user-round"></i> Medico
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i data-lucide="log-out"></i> Esci
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
        <a href="{{ route('medico.pazienti.show', $paziente->id) }}">
            {{ $utente->cognome }} {{ $utente->nome }}
        </a>
        <span class="sep">›</span>
        <span class="current">{{ $metaCorrente['label'] }}</span>
    </div>

    {{-- ── Intestazione ──────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
        <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#dbeafe,#cffafe);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
            <i data-lucide="{{ $metaCorrente['icon'] }}" style="width:24px;height:24px;"></i>
        </div>

        <div>
            <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:20px;">
                {{ $metaCorrente['label'] }}
            </div>
            <div style="font-size:13px;color:var(--muted);">
                {{ $utente->cognome }} {{ $utente->nome }}
                &nbsp;·&nbsp; {{ $assunzioni->total() }} record totali
            </div>
        </div>

        <a href="{{ route('medico.pazienti.show', $paziente->id) }}" class="btn-back" style="margin-left:auto;text-decoration:none;">
            <i data-lucide="arrow-left"></i> Torna al paziente
        </a>
    </div>

    {{-- ── Nav tipi ──────────────────────────────────────────────── --}}
    <div class="storico-nav">
        @php
            $navItems = [
                'oggi' => [
                    'label' => 'Previste oggi',
                    'color' => 'blue',
                    'icon' => 'calendar-clock',
                ],
                'prese' => [
                    'label' => 'Prese',
                    'color' => 'green',
                    'icon' => 'check-circle-2',
                ],
                'saltate' => [
                    'label' => 'Saltate',
                    'color' => 'red',
                    'icon' => 'x-circle',
                ],
                'attesa' => [
                    'label' => 'In attesa',
                    'color' => 'yellow',
                    'icon' => 'clock',
                ],
                'forzate' => [
                    'label' => 'Erogazioni forzate',
                    'color' => 'orange',
                    'icon' => 'alert-triangle',
                ],
            ];
        @endphp

        @foreach($navItems as $t => $meta)
            <a href="{{ route('medico.pazienti.storico', [$paziente->id, $t]) }}"
               class="snav-pill {{ $meta['color'] }} {{ $tipo === $t ? 'active' : '' }}">
                <i data-lucide="{{ $meta['icon'] }}" style="width:15px;height:15px;"></i>
                {{ $meta['label'] }}
                <span class="badge-n">{{ $totali[$t] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- ── Tabella principale ────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden;">

        @if($assunzioni->isEmpty())
            <div class="empty-state">
                <i data-lucide="{{ $metaCorrente['icon'] }}"></i>
                <p>
                    Nessun record trovato per <strong>{{ $metaCorrente['label'] }}</strong>.
                </p>
            </div>
        @else
            <div class="storico-table-wrap">
                <table class="storico-table">
                    <thead>
                    <tr>
                        <th>Data prevista</th>
                        <th>Orario</th>
                        <th>Farmaco</th>
                        <th>Dose</th>
                        <th>Stato</th>

                        @if($tipo === 'prese')
                            <th>Erogata alle</th>
                            <th>Confermata da</th>
                            <th>Qta rimasta</th>
                            <th>Scomparto</th>
                        @elseif($tipo === 'saltate')
                            <th>Note</th>
                            <th>Azione</th>
                        @elseif($tipo === 'attesa')
                            <th>Allarme inviato</th>
                            <th>Orario allarme</th>
                            <th>Azione medico</th>
                        @elseif($tipo === 'forzate')
                            <th>Erogata alle</th>
                            <th>Medico</th>
                            <th>Qta rimasta</th>
                            <th>Scomparto</th>
                        @elseif($tipo === 'oggi')
                            <th>Stato attuale</th>
                            <th>Allarme</th>
                            <th>Scomparto</th>
                            <th>Azione</th>
                        @endif

                        <th>Dispositivo</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($assunzioni as $a)
                        @php
                            $farmaco = $a->somministrazione?->terapia?->farmaco;
                            $ora = substr($a->somministrazione?->ora ?? '', 0, 5);
                            $quantitaTerapia = $a->somministrazione?->terapia?->quantita ?? '—';
                        @endphp

                        <tr>
                            <td>{{ $a->data_prevista?->format('d/m/Y') ?? '—' }}</td>

                            <td style="font-weight:600;">
                                {{ $ora ?: '—' }}
                            </td>

                            <td>
                                <strong>{{ $farmaco?->nome ?? 'N/A' }}</strong>

                                @if($farmaco?->dose)
                                    <br>
                                    <span style="font-size:11px;color:var(--muted);">
                                        {{ $farmaco->dose }}
                                    </span>
                                @endif
                            </td>

                            <td style="font-size:12px;color:var(--muted);">
                                {{ $quantitaTerapia }} cps
                            </td>

                            <td>
                                <span class="stato-badge stato-{{ $a->stato }}">
                                    {{ statoLabel($a->stato) }}
                                </span>

                                @if($a->forzata_medico || $a->apertura_forzata)
                                    <br>
                                    <span class="forzata-tag" style="margin-top:4px;">
                                        <i data-lucide="alert-triangle" style="width:11px;height:11px;"></i>
                                        Forzata
                                    </span>
                                @endif
                            </td>

                            {{-- Colonne contestuali per tipo --}}
                            @if($tipo === 'prese')
                                <td>
                                    {{ $a->data_erogazione?->format('d/m H:i') ?? '—' }}
                                </td>

                                <td>
                                    @php $conf = $a->confermata_da; @endphp

                                    <span style="font-size:12px;background:var(--border);padding:2px 8px;border-radius:6px;">
                                        {{ match($conf) {
                                            'paziente' => '👤 Paziente',
                                            'sensore' => '📡 Sensore',
                                            'familiare' => '👨‍👩‍👧 Familiare',
                                            'sistema' => '🤖 Sistema',
                                            default => $conf ?? '—',
                                        } }}
                                    </span>
                                </td>

                                <td>
                                    @if($a->quantita_erogata !== null)
                                        <span style="font-weight:600;color:var(--green);">
                                            {{ $a->quantita_erogata }}
                                        </span>
                                        <span style="font-size:11px;color:var(--muted);">
                                            rimaste
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td style="font-size:12px;color:var(--muted);">
                                    {{ $a->scomparto_numero ? 'Scomp. ' . $a->scomparto_numero : '—' }}
                                </td>

                            @elseif($tipo === 'saltate')
                                <td style="font-size:12px;color:var(--muted);max-width:200px;">
                                    {{ $a->note_evento ?? '—' }}
                                </td>

                                <td>
                                    @php
                                        $disp = $paziente->dispositivi->where('stato', 'attivo')->first();
                                    @endphp

                                    @if($disp && $farmaco)
                                        <button class="btn-allarme-singolo"
                                                onclick="attivaAllarme({{ $disp->id }}, {{ $farmaco->id }}, this)"
                                                title="Attiva allarme per questa pillola">
                                            <i data-lucide="bell-ring" style="width:13px;height:13px;"></i>
                                            Attiva allarme
                                        </button>
                                    @else
                                        <span style="font-size:12px;color:var(--muted);">—</span>
                                    @endif
                                </td>

                            @elseif($tipo === 'attesa')
                                <td>
                                    @if($a->allarme_inviato)
                                        <span style="color:var(--green);font-size:12px;font-weight:600;">
                                            ✔ Sì
                                        </span>
                                    @else
                                        <span style="color:var(--muted);font-size:12px;">
                                            No
                                        </span>
                                    @endif
                                </td>

                                <td style="font-size:12px;color:var(--muted);">
                                    {{ $a->data_allarme?->format('d/m H:i') ?? '—' }}
                                </td>

                                <td>
                                    @php
                                        $disp = $paziente->dispositivi->where('stato', 'attivo')->first();
                                    @endphp

                                    @if($disp && $farmaco)
                                        <button class="btn-allarme-singolo"
                                                onclick="attivaAllarme({{ $disp->id }}, {{ $farmaco->id }}, this)"
                                                title="Invia reminder allarme">
                                            <i data-lucide="bell-ring" style="width:13px;height:13px;"></i>
                                            Reminder
                                        </button>
                                    @else
                                        <span style="font-size:12px;color:var(--muted);">—</span>
                                    @endif
                                </td>

                            @elseif($tipo === 'forzate')
                                <td>
                                    {{ $a->data_erogazione?->format('d/m H:i') ?? $a->data_apertura_forzata?->format('d/m H:i') ?? '—' }}
                                </td>

                                <td style="font-size:12px;">
                                    @if($a->medicoForzante)
                                        Dr. {{ $a->medicoForzante->cognome }}
                                    @elseif($a->forzata_medico)
                                        <span style="color:var(--muted);">
                                            Medico storico
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if($a->quantita_erogata !== null)
                                        <span style="font-weight:600;color:var(--green);">
                                            {{ $a->quantita_erogata }}
                                        </span>
                                        <span style="font-size:11px;color:var(--muted);">
                                            rimaste
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td style="font-size:12px;color:var(--muted);">
                                    {{ $a->scomparto_numero ? 'Scomp. ' . $a->scomparto_numero : '—' }}
                                </td>

                            @elseif($tipo === 'oggi')
                                <td>
                                    <span class="stato-badge stato-{{ $a->stato }}">
                                        {{ statoLabel($a->stato) }}
                                    </span>
                                </td>

                                <td>
                                    @if($a->allarme_inviato)
                                        <span style="font-size:12px;color:var(--green);">
                                            ✔ Inviato {{ $a->data_allarme?->format('H:i') }}
                                        </span>
                                    @else
                                        <span style="font-size:12px;color:var(--muted);">
                                            Non inviato
                                        </span>
                                    @endif
                                </td>

                                <td style="font-size:12px;color:var(--muted);">
                                    {{ $a->scomparto_numero ? 'Scomp. ' . $a->scomparto_numero : '—' }}
                                </td>

                                <td>
                                    @php
                                        $disp = $paziente->dispositivi->where('stato', 'attivo')->first();
                                    @endphp

                                    @if($disp && $farmaco && in_array($a->stato, ['saltata', 'in_attesa', 'allarme_attivo']))
                                        <button class="btn-allarme-singolo"
                                                onclick="attivaAllarme({{ $disp->id }}, {{ $farmaco->id }}, this)">
                                            <i data-lucide="bell-ring" style="width:13px;height:13px;"></i>
                                            Allarme
                                        </button>
                                    @else
                                        <span style="font-size:12px;color:var(--muted);">—</span>
                                    @endif
                                </td>
                            @endif

                            <td style="font-size:12px;color:var(--muted);">
                                {{ $a->dispositivo?->nome_dispositivo ?? $a->dispositivo?->codice_seriale ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginazione --}}
            @if($assunzioni->hasPages())
                <div style="padding:16px 20px;border-top:1px solid var(--border);">
                    {{ $assunzioni->links() }}
                </div>
            @endif
        @endif
    </div>

</main>

<div class="toast" id="toast"></div>

<script>
    function attivaAllarme(idDispositivo, idFarmaco, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" style="width:13px;height:13px;"></i> Invio...';

        fetch(`/medico/mqtt/${idDispositivo}/allarme`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                id_farmaco: idFarmaco
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showToast('🔔 Allarme inviato al dispositivo!', 'success');
                btn.innerHTML = '<i data-lucide="check" style="width:13px;height:13px;"></i> Inviato';
            } else {
                showToast('⚠️ ' + (data.messaggio || 'Errore'), 'error');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="bell-ring" style="width:13px;height:13px;"></i> Allarme';
            }

            if (window.lucide) {
                window.lucide.createIcons();
            }
        })
        .catch(() => {
            showToast('Errore di rete', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="bell-ring" style="width:13px;height:13px;"></i> Allarme';

            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }

    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = `toast ${type} show`;

        setTimeout(() => {
            t.classList.remove('show');
        }, 3500);
    }
</script>

</body>
</html>
