<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Dispositivi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('paziente._styles')
</head>
<body>
@include('paziente._sidebar', ['active' => 'dispositivi'])
<main class="main">

    <div class="page-header">
        <div>
            <h1>Dispositivi PillMate</h1>
            <p>Stato e informazioni dei dispenser connessi</p>
        </div>
    </div>

    @if($dispositivi->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding:48px 0;">
                <div style="display:flex;justify-content:center;margin-bottom:14px;color:var(--accent);">
                    <i data-lucide="radio" style="width:30px;height:30px;"></i>
                </div>
                Nessun dispositivo associato.<br>
                <span style="font-size:12px;">Il medico deve configurare e associare un dispositivo al profilo.</span>
            </div>
        </div>
    @else

        <div class="device-grid">
            @foreach($dispositivi as $d)
                @php
                    $online = $d->stato === 'attivo';
                    $ultimoContatto = $d->ultima_connessione ?? $d->ultimo_payload_at;
                    $minutiFa = $ultimoContatto ? \Carbon\Carbon::parse($ultimoContatto)->diffInMinutes(now()) : null;
                    $recentementeOnline = $minutiFa !== null && $minutiFa <= 5;
                @endphp
                <div class="device-card">
                    <div class="device-header">
                        <div class="device-img">
                            <i data-lucide="pill"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="device-name">{{ $d->nome_dispositivo ?? 'PillMate Dispenser' }}</div>
                            <div class="device-serial">S/N: {{ $d->codice_seriale }}</div>
                        </div>
                    </div>

                    <div>
                        @if($d->allarme_attivo)
                            <span class="device-status" style="background:#fef2f2;color:var(--red);border:1px solid #fecaca;animation:pulse 1.5s infinite;">
                        <span class="dot dot-red"></span> ALLARME ATTIVO
                    </span>
                        @elseif($online && $recentementeOnline)
                            <span class="device-status status-attivo"><span class="dot dot-green"></span> Online</span>
                        @elseif($online)
                            <span class="device-status" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;">
                        <span class="dot dot-yellow"></span> Connesso (inattivo)
                    </span>
                        @elseif($d->stato === 'errore')
                            <span class="device-status status-errore"><span class="dot dot-red"></span> Errore</span>
                        @elseif($d->stato === 'manutenzione')
                            <span class="device-status status-manutenzione"><span class="dot dot-yellow"></span> Manutenzione</span>
                        @else
                            <span class="device-status status-offline"><span class="dot dot-gray"></span> Offline</span>
                        @endif
                    </div>

                    <div class="device-metrics" style="margin-top:14px;">
                        @if($d->temperatura !== null)
                            <div class="metric">
                                <div class="metric-label">Temperatura</div>
                                <div class="metric-value" style="color:{{ $d->temperatura > 35 ? 'var(--red)' : 'var(--text)' }}">
                                    {{ $d->temperatura }}°C
                                </div>
                            </div>
                        @endif

                        @if($d->umidita !== null)
                            <div class="metric">
                                <div class="metric-label">Umidità</div>
                                <div class="metric-value">{{ $d->umidita }}%</div>
                            </div>
                        @endif

                        @if($d->batteria !== null)
                            <div class="metric">
                                <div class="metric-label">Batteria</div>
                                <div class="metric-value" style="color:{{ $d->batteria < 20 ? 'var(--red)' : ($d->batteria < 50 ? 'var(--yellow)' : 'var(--green)') }}">
                                    {{ $d->batteria }}%
                                </div>
                            </div>
                        @endif

                        @if($d->wifi_rssi !== null)
                            <div class="metric">
                                <div class="metric-label">Segnale WiFi</div>
                                <div class="metric-value" style="color:{{ $d->wifi_rssi < -80 ? 'var(--red)' : ($d->wifi_rssi < -60 ? 'var(--yellow)' : 'var(--green)') }}">
                                    {{ $d->wifi_rssi }} dBm
                                </div>
                            </div>
                        @endif

                        @if($d->scomparto_attuale !== null)
                            <div class="metric">
                                <div class="metric-label">Scomparto attivo</div>
                                <div class="metric-value">N° {{ $d->scomparto_attuale }}</div>
                            </div>
                        @endif

                        @if($d->sveglia_impostata)
                            <div class="metric">
                                <div class="metric-label">Prossima sveglia</div>
                                <div class="metric-value">{{ substr($d->sveglia_impostata, 0, 5) }}</div>
                            </div>
                        @endif
                    </div>

                    <div style="margin-top:12px;padding:10px 12px;background:#f8faff;border:1px solid var(--border);border-radius:8px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);font-weight:600;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                            <i data-lucide="radio-tower" style="width:12px;height:12px;"></i>
                            Configurazione MQTT
                        </div>
                        <div style="font-size:12px;color:var(--muted);line-height:1.6;">
                            <div>Broker: <span style="color:var(--text);">{{ env('MQTT_HOST','N/D') }}:{{ env('MQTT_PORT',1883) }}</span></div>
                            <div>Topic: <span style="color:var(--accent);font-size:11px;">pillmate/{{ $d->codice_seriale }}/...</span></div>
                        </div>
                    </div>

                    <div class="device-last">
                        @if($ultimoContatto)
                            Ultimo contatto: {{ \Carbon\Carbon::parse($ultimoContatto)->diffForHumans() }}
                        @else
                            Nessun contatto registrato
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card" style="margin-top:24px;">
            <div class="card-title">
                <i data-lucide="info"></i>
                Legenda stati dispositivo
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;font-size:13px;">
                <div style="display:flex;align-items:center;gap:8px;"><span class="dot dot-green" style="flex-shrink:0"></span><span><strong>Online</strong> — Connesso e attivo (heartbeat &lt; 5 min)</span></div>
                <div style="display:flex;align-items:center;gap:8px;"><span class="dot dot-yellow" style="flex-shrink:0"></span><span><strong>Inattivo</strong> — Connesso ma senza aggiornamenti recenti</span></div>
                <div style="display:flex;align-items:center;gap:8px;"><span class="dot dot-gray" style="flex-shrink:0"></span><span><strong>Offline</strong> — Nessuna connessione</span></div>
                <div style="display:flex;align-items:center;gap:8px;"><span class="dot dot-red" style="flex-shrink:0"></span><span><strong>Errore / Allarme</strong> — Richiede attenzione</span></div>
            </div>
        </div>
    @endif

</main>
<style>
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
</style>
</body>
</html>
