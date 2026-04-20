{{-- resources/views/paziente/dashboard.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;

    $nonLette = DB::table('notifiche')
        ->where('id_utente', $utente->id)
        ->where('letta', false)
        ->count();
@endphp

    <!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('paziente._styles')
</head>
<body>
@include('paziente._sidebar', ['active' => 'dashboard'])

<main class="main">

    <div class="page-header">
        <div>
            <h1>Ciao, {{ $utente->nome }} 👋</h1>
            <p>Il piano terapeutico di oggi — {{ now()->isoFormat('dddd D MMMM Y') }}</p>
        </div>

        @if($paziente && $medici->count())
            <div class="medico-badge">
                <span class="medico-ico">👨‍⚕️</span>
                <div>
                    <div style="font-size:11px;color:var(--muted);">Medico curante</div>
                    <div style="font-size:13px;font-weight:600;">
                        {{ $medici->first()->cognome }} {{ $medici->first()->nome }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Stats --}}
    <div class="stats">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Terapie attive</span>
                <div class="stat-ico blue">💊</div>
            </div>
            <div class="stat-value {{ $terapieAttive->count() > 0 ? 'c-blue' : '' }}">
                {{ $terapieAttive->count() }}
            </div>
            <div class="stat-sub">in corso</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Prese oggi</span>
                <div class="stat-ico green">✅</div>
            </div>
            <div class="stat-value {{ $statOggi['prese'] > 0 ? 'c-green' : '' }}">
                {{ $statOggi['prese'] }}
            </div>
            <div class="stat-sub">di {{ $statOggi['totali'] }} previste</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Da assumere</span>
                <div class="stat-ico yellow">⏳</div>
            </div>
            <div class="stat-value {{ $statOggi['attesa'] > 0 ? 'c-yellow' : '' }}">
                {{ $statOggi['attesa'] }}
            </div>
            <div class="stat-sub">ancora oggi</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Dispositivi</span>
                <div class="stat-ico teal">📡</div>
            </div>
            <div class="stat-value {{ $numDispositivi > 0 ? 'c-teal' : '' }}">
                {{ $numDispositivi }}
            </div>
            <div class="stat-sub">connessi</div>
        </div>
    </div>

    {{-- Avviso dosi saltate --}}
    @if($statOggi['saltate'] > 0)
        <div class="alert-banner">
            ⚠️ <strong>Attenzione:</strong> {{ $statOggi['saltate'] }} dose/i risultano saltate oggi. Contattare il medico se necessario.
        </div>
    @endif

    <div class="content-grid">

        {{-- Terapie attive --}}
        <div class="card">
            <div class="card-title">
                📋 Terapie attive
                <a href="{{ route('paziente.terapie') }}" class="card-link">Vedi tutto →</a>
            </div>

            @forelse($terapieAttive->take(4) as $t)
                <div class="terapia-row">
                    <div>
                        <div style="font-weight:600;font-size:14px;">
                            {{ $t->farmaco->nome ?? '—' }}
                        </div>

                        <div style="font-size:12px;color:var(--muted);">
                            Dal {{ $t->data_inizio->format('d/m/Y') }}
                            @if($t->data_fine)
                                al {{ $t->data_fine->format('d/m/Y') }}
                            @endif
                            · {{ $t->quantita }} pillola/e
                        </div>

                        @if($t->istruzioni)
                            <div style="font-size:11px;color:var(--muted);margin-top:3px;">
                                📝 {{ Str::limit($t->istruzioni, 60) }}
                            </div>
                        @endif
                    </div>

                    <span class="badge-attiva">Attiva</span>
                </div>
            @empty
                <div class="empty-state">Nessuna terapia attiva al momento.</div>
            @endforelse
        </div>

        {{-- Assunzioni di oggi --}}
        <div class="card">
            <div class="card-title">📅 Oggi — {{ now()->format('d/m/Y') }}</div>

            @forelse($assunzioniOggi as $a)
                @php
                    $farm = $a->somministrazione->terapia->farmaco ?? null;
                @endphp

                <div class="assunzione-row">
                    <div class="assunzione-ico" style="{{ in_array($a->stato,['assunta','erogata']) ? 'background:rgba(16,185,129,.15)' : (in_array($a->stato,['saltata','non_ritirata']) ? 'background:rgba(239,68,68,.15)' : 'background:rgba(245,158,11,.12)') }}">
                        {{ in_array($a->stato,['assunta','erogata']) ? '✅' : (in_array($a->stato,['saltata','non_ritirata']) ? '❌' : '⏳') }}
                    </div>

                    <div class="assunzione-info">
                        <div class="assunzione-name">{{ $farm->nome ?? 'Farmaco' }}</div>
                        <div class="assunzione-time">
                            {{ substr($a->somministrazione->ora ?? '--', 0, 5) }}
                            @if($a->apertura_forzata)
                                · <span style="color:var(--accent);font-size:10px;">🔓 Forzata</span>
                            @endif
                        </div>
                    </div>

                    <span class="stato-badge stato-{{ $a->stato }}">{{ statoLbl($a->stato) }}</span>
                </div>
            @empty
                <div class="empty-state">Nessuna assunzione prevista per oggi.</div>
            @endforelse
        </div>

        {{-- Notifiche --}}
        <div class="card">
            <div class="card-title">
                🔔 Ultime notifiche
                <a href="{{ route('paziente.notifiche') }}" class="card-link">Vedi tutto →</a>
            </div>

            @forelse($notifiche as $n)
                <div style="padding:10px 0;border-bottom:1px solid rgba(31,45,69,.5);">
                    <div style="font-size:13px;font-weight:500;">
                        {{ $n->titolo ?? 'Notifica' }}
                    </div>

                    <div style="font-size:12px;color:var(--muted);margin-top:3px;">
                        {{ Str::limit($n->messaggio ?? '', 80) }}
                    </div>

                    <div style="font-size:11px;color:var(--muted);margin-top:4px;">
                        {{ !empty($n->data_invio) ? \Carbon\Carbon::parse($n->data_invio)->diffForHumans() : 'Data non disponibile' }}
                    </div>
                </div>
            @empty
                <div class="empty-state">Nessuna notifica ricevuta.</div>
            @endforelse
        </div>

    </div>
</main>
</body>
</html>

@php
    function statoLbl(string $s): string {
        return match($s) {
            'assunta' => '✅ Presa',
            'erogata' => '💊 Erogata',
            'saltata' => '❌ Saltata',
            'non_ritirata' => '⏸ Non ritirata',
            'in_attesa' => '⏳ In attesa',
            'ritardo' => '⚠️ Ritardo',
            'allarme_attivo' => '🔔 Allarme',
            'apertura_forzata' => '🔓 Forzata',
            default => ucfirst($s),
        };
    }
@endphp
