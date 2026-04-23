<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
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
            <h1>Notifiche</h1>
            <p>Messaggi dal medico e comunicazioni di sistema</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-banner">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($paziente) && $paziente && $paziente->medici->count())
        <div class="card" style="margin-bottom:24px;">
            <div class="card-title">
                <i data-lucide="send-horizontal"></i>
                Scrivi al tuo medico
            </div>

            <form method="POST" action="{{ route('paziente.notifiche.invia') }}" id="formMsg">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);margin-bottom:6px;">
                            Destinatario
                        </label>
                        <div style="background:#f8faff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font-size:14px;color:var(--text);display:flex;align-items:center;gap:8px;">
                            <i data-lucide="stethoscope" style="width:16px;height:16px;color:var(--accent);"></i>
                            @if($paziente->medici->count() === 1)
                                Dr. {{ $paziente->medici->first()->cognome }} {{ $paziente->medici->first()->nome }}
                            @else
                                {{ $paziente->medici->count() }} medici (invio a tutti)
                            @endif
                        </div>
                    </div>

                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);margin-bottom:6px;">
                            Oggetto *
                        </label>
                        <input type="text" name="titolo" value="{{ old('titolo') }}"
                               placeholder="Es. Domanda sulla mia terapia..."
                               required maxlength="100"
                               style="width:100%;background:#f8faff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font:inherit;font-size:14px;color:var(--text);outline:none;transition:border-color .2s, box-shadow .2s;"
                               onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px rgba(37,99,235,.08)'"
                               onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'"/>
                        @error('titolo')<div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);margin-bottom:6px;">
                        Messaggio *
                    </label>
                    <textarea name="messaggio" required rows="4"
                              placeholder="Scrivi qui il tuo messaggio per il medico..."
                              style="width:100%;background:#f8faff;border:1.5px solid var(--border);border-radius:10px;padding:12px 14px;font:inherit;font-size:14px;color:var(--text);outline:none;resize:vertical;transition:border-color .2s, box-shadow .2s;"
                              onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px rgba(37,99,235,.08)'"
                              onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">{{ old('messaggio') }}</textarea>
                    @error('messaggio')<div style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <button type="submit"
                        style="background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;border-radius:10px;color:#fff;padding:11px 22px;font-family:'Syne',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:opacity .2s, transform .15s;box-shadow:0 4px 12px rgba(37,99,235,.2);display:inline-flex;align-items:center;gap:8px;"
                        onmouseover="this.style.opacity='.92';this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
                    <i data-lucide="send"></i>
                    Invia messaggio
                </button>
            </form>
        </div>
    @endif

    <div class="card">
        <div class="card-title">
            <i data-lucide="inbox"></i>
            Messaggi ricevuti
            @if($notifiche->total() > 0)
                <span style="margin-left:auto;font-size:12px;font-weight:400;color:var(--muted);">
                    {{ $notifiche->total() }} totali
                </span>
            @endif
        </div>

        @if($notifiche->isEmpty())
            <div class="empty-state" style="padding:48px 0;">
                <div style="display:flex;justify-content:center;margin-bottom:12px;color:var(--accent);">
                    <i data-lucide="bell-off" style="width:36px;height:36px;"></i>
                </div>
                <div style="font-weight:600;color:var(--text);margin-bottom:6px;">Nessuna notifica</div>
                <div style="font-size:13px;">Qui appariranno i messaggi dal tuo medico.</div>
            </div>
        @else
            @foreach($notifiche as $n)
                @php
                    $iconName = match($n->tipo ?? 'info') {
                        'allarme'    => 'triangle-alert',
                        'promemoria' => 'clock-3',
                        'messaggio'  => 'message-square',
                        'errore'     => 'circle-alert',
                        default      => 'info',
                    };

                    $bgIcona = match($n->tipo ?? 'info') {
                        'allarme'    => '#fef2f2',
                        'promemoria' => '#fff7ed',
                        'messaggio'  => '#f0fdf4',
                        'errore'     => '#fef2f2',
                        default      => '#eff6ff',
                    };

                    $colorIcona = match($n->tipo ?? 'info') {
                        'allarme'    => 'var(--red)',
                        'promemoria' => 'var(--yellow)',
                        'messaggio'  => 'var(--green)',
                        'errore'     => 'var(--red)',
                        default      => 'var(--accent)',
                    };

                    $mittente = isset($n->id_mittente) ? \App\Models\User::find($n->id_mittente) : null;
                @endphp

                <div class="notifica-item {{ $n->letta ? '' : 'unread' }}">
                    <div class="notifica-header">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;border-radius:11px;background:{{ $bgIcona }};border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:{{ $colorIcona }};">
                                <i data-lucide="{{ $iconName }}"></i>
                            </div>

                            <div>
                                <div class="notifica-title">{{ $n->titolo ?? 'Notifica' }}</div>
                                <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                    @if($mittente)
                                        da {{ $mittente->cognome }} {{ $mittente->nome }}
                                        @if($mittente->ruolo === 'medico') (Medico) @endif
                                        @if($mittente->ruolo === 'admin') (Amministratore) @endif
                                    @endif

                                    @if(!$n->letta)
                                        &nbsp;<span style="background:var(--accent);color:#fff;font-size:9px;font-weight:700;padding:1px 6px;border-radius:8px;">NUOVA</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="notifica-time">
                            {{ !empty($n->data_invio) ? \Carbon\Carbon::parse($n->data_invio)->diffForHumans() : '' }}
                        </div>
                    </div>

                    <div class="notifica-body" style="margin-left:52px;margin-top:6px;line-height:1.6;">
                        {{ $n->messaggio ?? '' }}
                    </div>
                </div>
            @endforeach

            <div class="pagination" style="margin-top:24px;">
                {{ $notifiche->links('pagination::simple-tailwind') }}
            </div>
        @endif
    </div>

</main>
</body>
</html>
