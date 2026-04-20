<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Notifiche</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('paziente._styles')
</head>
<body>
@include('paziente._sidebar', ['active' => 'notifiche'])

<main class="main">

    <div class="page-header">
        <div>
            <h1>🔔 Notifiche</h1>
            <p>Messaggi e avvisi dal tuo medico e dal sistema</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($notifiche->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding:48px 0;">
                🔔<br><br>Nessuna notifica ricevuta al momento.
            </div>
        </div>
    @else

        @foreach($notifiche as $n)
            @php
                $icona = match($n->tipo ?? '') {
                    'allarme' => '🔔',
                    'terapia' => '💊',
                    'assunzione' => '✅',
                    'avviso_saltata' => '⚠️',
                    'erogazione_forzata' => '🔓',
                    'medico' => '👨‍⚕️',
                    default => '📩',
                };

                $colore = match($n->tipo ?? '') {
                    'allarme', 'avviso_saltata' => 'rgba(239,68,68,.15)',
                    'terapia', 'medico' => 'rgba(59,130,246,.15)',
                    'erogazione_forzata' => 'rgba(59,130,246,.12)',
                    default => 'rgba(255,255,255,.04)',
                };
            @endphp

            <div class="notifica-item {{ $n->letta ? '' : 'unread' }}">
                <div class="notifica-header">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:10px;background:{{ $colore }};display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                            {{ $icona }}
                        </div>

                        <div>
                            <div class="notifica-title">{{ $n->titolo ?? 'Notifica' }}</div>

                            @if(!$n->letta)
                                <span style="font-size:10px;font-weight:700;background:var(--accent);color:#fff;padding:1px 7px;border-radius:10px;">
                                    NUOVA
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="notifica-time">
                        {{ !empty($n->data_invio) ? \Carbon\Carbon::parse($n->data_invio)->diffForHumans() : 'Data non disponibile' }}
                    </div>
                </div>

                <div class="notifica-body" style="margin-left:48px;">
                    {{ $n->messaggio ?? '' }}
                </div>

                @if(isset($n->id_assunzione) && $n->id_assunzione)
                    <div style="margin-left:48px;margin-top:8px;">
                        <a href="{{ route('paziente.assunzione.show', $n->id_assunzione) }}" class="btn btn-ghost" style="font-size:11px;padding:5px 12px;">
                            Vedi assunzione →
                        </a>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="pagination" style="margin-top:24px;">
            {{ $notifiche->links('pagination::simple-tailwind') }}
        </div>
    @endif

</main>
</body>
</html>
