<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Dettaglio assunzione</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('paziente._styles')
</head>
<body>
@include('paziente._sidebar', ['active' => 'storico'])
<main class="main">

    <div class="breadcrumb">
        <a href="{{ route('paziente.dashboard') }}">Dashboard</a>
        <span class="sep">›</span>
        <a href="{{ route('paziente.storico') }}">Storico assunzioni</a>
        <span class="sep">›</span>
        <span style="color:var(--text);">Dettaglio #{{ $assunzione->id }}</span>
    </div>

    @php
        $terapia = $assunzione->somministrazione->terapia ?? null;
        $farm    = $terapia?->farmaco;
        $medico  = $terapia?->medico;
        $disp    = $assunzione->dispositivo;
    @endphp

    <div class="page-header">
        <div>
            <h1>Assunzione #{{ $assunzione->id }}</h1>
            <p>{{ $farm?->nome ?? 'Farmaco' }} — {{ \Carbon\Carbon::parse($assunzione->data_prevista)->isoFormat('dddd D MMMM Y') }}</p>
        </div>
        <span class="stato-badge stato-{{ $assunzione->stato }}" style="font-size:14px;padding:8px 16px;">{{ statoLbl($assunzione->stato) }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <div class="card">
            <div class="card-title">
                <i data-lucide="clipboard-list"></i>
                Dettagli assunzione
            </div>

            <div class="detail-grid">
                <div class="detail-field">
                    <div class="detail-label">Data prevista</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($assunzione->data_prevista)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="detail-field">
                    <div class="detail-label">Orario somministrazione</div>
                    <div class="detail-value">{{ substr($assunzione->somministrazione->ora ?? '--', 0, 5) }}</div>
                </div>
                <div class="detail-field">
                    <div class="detail-label">Data erogazione</div>
                    <div class="detail-value">{{ $assunzione->data_erogazione ? \Carbon\Carbon::parse($assunzione->data_erogazione)->format('d/m/Y H:i') : '—' }}</div>
                </div>
                <div class="detail-field">
                    <div class="detail-label">Confermata il</div>
                    <div class="detail-value">{{ $assunzione->data_conferma ? \Carbon\Carbon::parse($assunzione->data_conferma)->format('d/m/Y H:i') : '—' }}</div>
                </div>
                <div class="detail-field">
                    <div class="detail-label">Confermata da</div>
                    <div class="detail-value">{{ $assunzione->confermata_da ?? '—' }}</div>
                </div>
                <div class="detail-field">
                    <div class="detail-label">Scomparto n°</div>
                    <div class="detail-value">{{ $assunzione->scomparto_numero ?? '—' }}</div>
                </div>
            </div>

            @if($assunzione->apertura_forzata)
                <div style="margin-top:14px;padding:12px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;">
                    <div style="font-size:12px;font-weight:700;color:var(--accent);margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                        <i data-lucide="unlock"></i>
                        Erogazione forzata dal medico
                    </div>
                    <div style="font-size:12px;color:var(--muted);">
                        Data apertura: {{ $assunzione->data_apertura_forzata ? \Carbon\Carbon::parse($assunzione->data_apertura_forzata)->format('d/m/Y H:i') : '—' }}
                    </div>
                </div>
            @endif

            @if($assunzione->allarme_inviato)
                <div style="margin-top:10px;padding:12px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;">
                    <div style="font-size:12px;font-weight:700;color:var(--red);margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                        <i data-lucide="bell-ring"></i>
                        Allarme inviato
                    </div>
                    <div style="font-size:12px;color:var(--muted);">
                        {{ $assunzione->data_allarme ? \Carbon\Carbon::parse($assunzione->data_allarme)->format('d/m/Y H:i') : '—' }}
                    </div>
                </div>
            @endif

            @if($assunzione->note_evento)
                <div style="margin-top:10px;padding:10px 12px;background:#f8faff;border:1px solid var(--border);border-radius:8px;font-size:12px;color:var(--muted);display:flex;align-items:flex-start;gap:6px;">
                    <i data-lucide="notebook-pen" style="width:14px;height:14px;margin-top:1px;"></i>
                    <span>{{ $assunzione->note_evento }}</span>
                </div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="card">
                <div class="card-title">
                    <i data-lucide="pill"></i>
                    Farmaco e terapia
                </div>

                @if($farm)
                    <div style="margin-bottom:12px;">
                        <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;">{{ $farm->nome }}</div>
                        @if($farm->dose)<div style="font-size:13px;color:var(--muted);">{{ $farm->dose }}</div>@endif
                        @if($farm->descrizione)<div style="font-size:12px;color:var(--muted);margin-top:4px;">{{ $farm->descrizione }}</div>@endif
                    </div>
                @endif

                @if($terapia)
                    <div class="detail-grid" style="grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="detail-field">
                            <div class="detail-label">Inizio terapia</div>
                            <div class="detail-value">{{ $terapia->data_inizio->format('d/m/Y') }}</div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">Fine terapia</div>
                            <div class="detail-value">{{ $terapia->data_fine?->format('d/m/Y') ?? 'Indefinita' }}</div>
                        </div>
                        <div class="detail-field" style="grid-column:1/-1;">
                            <div class="detail-label">Quantità per dose</div>
                            <div class="detail-value">{{ $terapia->quantita }} pillola/e</div>
                        </div>
                    </div>

                    @if($terapia->istruzioni)
                        <div class="istruzioni-box" style="margin-top:10px;display:flex;align-items:flex-start;gap:6px;">
                            <i data-lucide="notebook-pen" style="width:14px;height:14px;margin-top:1px;"></i>
                            <span>{{ $terapia->istruzioni }}</span>
                        </div>
                    @endif
                @endif
            </div>

            @if($medico)
                <div class="card">
                    <div class="card-title">
                        <i data-lucide="stethoscope"></i>
                        Medico prescrivente
                    </div>

                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#dbeafe,#cffafe);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:var(--accent);flex-shrink:0;">
                            {{ strtoupper(substr($medico->nome,0,1)) }}{{ strtoupper(substr($medico->cognome,0,1)) }}
                        </div>
                        <div>
                            <div style="font-weight:600;">Dr. {{ $medico->cognome }} {{ $medico->nome }}</div>
                            @if($medico->email)<div style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px;"><i data-lucide="mail" style="width:13px;height:13px;"></i>{{ $medico->email }}</div>@endif
                            @if($medico->telefono)<div style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px;"><i data-lucide="phone" style="width:13px;height:13px;"></i>{{ $medico->telefono }}</div>@endif
                        </div>
                    </div>
                </div>
            @endif

            @if($disp)
                <div class="card">
                    <div class="card-title">
                        <i data-lucide="radio"></i>
                        Dispositivo
                    </div>

                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#dbeafe,#cffafe);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
                            <i data-lucide="pill"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;">{{ $disp->nome_dispositivo ?? $disp->codice_seriale }}</div>
                            <div style="font-size:12px;color:var(--muted);">S/N: {{ $disp->codice_seriale }}</div>
                            <span class="device-status status-{{ $disp->stato }}" style="margin-top:4px;">{{ ucfirst($disp->stato) }}</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <div style="margin-top:20px;">
        <a href="{{ route('paziente.storico') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left"></i>
            Torna allo storico
        </a>
    </div>

</main>
</body>
</html>

@php
    function statoLbl(string $s): string {
        return match($s) {
            'assunta'          => 'Presa',
            'erogata'          => 'Erogata',
            'saltata'          => 'Saltata',
            'non_ritirata'     => 'Non ritirata',
            'in_attesa'        => 'In attesa',
            'ritardo'          => 'Ritardo',
            'allarme_attivo'   => 'Allarme',
            'apertura_forzata' => 'Forzata',
            default            => ucfirst($s),
        };
    }
@endphp
