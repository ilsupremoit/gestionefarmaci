<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Le mie terapie</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('paziente._styles')
</head>
<body>
@include('paziente._sidebar', ['active' => 'terapie'])
<main class="main">

    <div class="page-header">
        <div>
            <h1>Le mie terapie</h1>
            <p>Piano terapeutico attuale e storico prescrizioni</p>
        </div>
    </div>

    <div style="margin-bottom:32px;">
        <div style="font-family:'Syne',sans-serif;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--green);margin-bottom:14px;display:flex;align-items:center;gap:8px;">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--green);display:inline-block;"></span>
            Terapie in corso ({{ $terapieAttive->count() }})
        </div>

        @forelse($terapieAttive as $t)
            @php
                $farm = $t->farmaco;
                $totAss = \App\Models\Assunzione::whereHas('somministrazione', fn($q)=>$q->where('id_terapia',$t->id))
                    ->where('data_prevista','>=',now()->subDays(30))->count();
                $prese  = \App\Models\Assunzione::whereHas('somministrazione', fn($q)=>$q->where('id_terapia',$t->id))
                    ->where('data_prevista','>=',now()->subDays(30))
                    ->whereIn('stato',['assunta','erogata'])->count();
                $aderenza = $totAss > 0 ? round($prese/$totAss*100) : 0;
            @endphp

            <div class="terapia-card">
                <div class="terapia-card-header">
                    <div>
                        <div class="terapia-card-name">{{ $farm->nome ?? 'Farmaco sconosciuto' }}</div>
                        @if($farm?->dose)
                            <div class="terapia-card-dose">
                                {{ $farm->dose }}
                                @if($farm->descrizione) · {{ Str::limit($farm->descrizione,60) }} @endif
                            </div>
                        @endif
                    </div>
                    <span class="badge-attiva">Attiva</span>
                </div>

                <div class="terapia-meta">
                    <span><i data-lucide="calendar" style="width:13px;height:13px;"></i> Dal {{ $t->data_inizio->format('d/m/Y') }}@if($t->data_fine) al {{ $t->data_fine->format('d/m/Y') }}@endif</span>
                    <span><i data-lucide="pill" style="width:13px;height:13px;"></i> {{ $t->quantita }} pillola/e per dose</span>
                    @if($t->frequenza)<span><i data-lucide="clock-3" style="width:13px;height:13px;"></i> {{ $t->frequenza }}</span>@endif
                    @if($t->medico)<span><i data-lucide="stethoscope" style="width:13px;height:13px;"></i> Dr. {{ $t->medico->cognome }}</span>@endif
                </div>

                @if($t->somministrazioni->count())
                    <div class="terapia-orari">
                        @foreach($t->somministrazioni as $s)
                            <span class="orario-chip">{{ $s->giorno_settimana }} {{ substr($s->ora,0,5) }}</span>
                        @endforeach
                    </div>
                @endif

                @if($t->istruzioni)
                    <div class="istruzioni-box" style="display:flex;align-items:flex-start;gap:6px;">
                        <i data-lucide="notebook-pen" style="width:14px;height:14px;margin-top:1px;"></i>
                        <span><strong>Istruzioni:</strong> {{ $t->istruzioni }}</span>
                    </div>
                @endif

                <div style="margin-top:14px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:4px;">
                        <span>Aderenza ultimi 30 giorni</span>
                        <span style="font-weight:700;color:{{ $aderenza >= 80 ? '#059669' : ($aderenza >= 50 ? '#d97706' : '#dc2626') }}">{{ $aderenza }}%</span>
                    </div>
                    <div class="adherence-bar">
                        <div class="adherence-fill" style="width:{{ $aderenza }}%;background:{{ $aderenza >= 80 ? 'linear-gradient(90deg,var(--accent),var(--green))' : ($aderenza >= 50 ? 'linear-gradient(90deg,#d97706,var(--yellow))' : 'linear-gradient(90deg,#dc2626,var(--red))') }}"></div>
                    </div>
                    <div style="font-size:11px;color:var(--muted);margin-top:4px;">{{ $prese }}/{{ $totAss }} dosi prese nel periodo</div>
                </div>

                @if($t->medico)
                    <div class="medico-info">
                        <i data-lucide="stethoscope" style="width:14px;height:14px;"></i>
                        Prescritta dal Dr. {{ $t->medico->cognome }} {{ $t->medico->nome }}
                        @if($t->medico->telefono)
                            · <i data-lucide="phone" style="width:13px;height:13px;"></i> {{ $t->medico->telefono }}
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="card">
                <div class="empty-state">Nessuna terapia attiva al momento. Il medico non ha ancora prescritto terapie.</div>
            </div>
        @endforelse
    </div>

    @if($terapieConcluse->count())
        <div>
            <div style="font-family:'Syne',sans-serif;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <span style="width:8px;height:8px;border-radius:50%;background:var(--muted);display:inline-block;"></span>
                Terapie concluse ({{ $terapieConcluse->count() }})
            </div>

            @foreach($terapieConcluse as $t)
                <div class="terapia-card" style="opacity:.65">
                    <div class="terapia-card-header">
                        <div>
                            <div class="terapia-card-name">{{ $t->farmaco->nome ?? 'Farmaco' }}</div>
                            @if($t->farmaco?->dose)<div class="terapia-card-dose">{{ $t->farmaco->dose }}</div>@endif
                        </div>
                        <span class="badge-conclusa">Conclusa</span>
                    </div>

                    <div class="terapia-meta">
                        <span><i data-lucide="calendar" style="width:13px;height:13px;"></i> Dal {{ $t->data_inizio->format('d/m/Y') }}@if($t->data_fine) al {{ $t->data_fine->format('d/m/Y') }}@endif</span>
                        <span><i data-lucide="pill" style="width:13px;height:13px;"></i> {{ $t->quantita }} pillola/e</span>
                        @if($t->medico)<span><i data-lucide="stethoscope" style="width:13px;height:13px;"></i> Dr. {{ $t->medico->cognome }}</span>@endif
                    </div>

                    @if($t->istruzioni)
                        <div class="istruzioni-box" style="display:flex;align-items:flex-start;gap:6px;">
                            <i data-lucide="notebook-pen" style="width:14px;height:14px;margin-top:1px;"></i>
                            <span>{{ $t->istruzioni }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</main>
</body>
</html>
