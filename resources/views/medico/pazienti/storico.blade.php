<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Storico paziente</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @vite('resources/css/medico/infoPaziente.css')
</head>
<body>
@php
    $utente = $paziente->utente;
    $meta = $tipoMeta[$tipo] ?? $tipoMeta['tutte'];
@endphp

<main class="main" style="margin-left:40px;max-width:1200px;">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
        <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#dbeafe,#cffafe);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
            <i data-lucide="{{ $meta['icon'] ?? 'list' }}" style="width:24px;height:24px;"></i>
        </div>
        <div>
            <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:20px;">{{ $meta['label'] ?? 'Storico' }}</div>
            <div style="font-size:13px;color:var(--muted);">{{ $utente->cognome }} {{ $utente->nome }} · {{ $assunzioni->total() }} record</div>
        </div>
        <a href="{{ route('medico.pazienti.show', $paziente->id) }}" class="btn-back" style="margin-left:auto;text-decoration:none;">
            <i data-lucide="arrow-left"></i> Torna al paziente
        </a>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        @foreach(['tutte' => 'Tutte','oggi' => 'Oggi','prese' => 'Prese','saltate' => 'Saltate','forzate' => 'Forzate'] as $k => $lbl)
            <a href="{{ route('medico.pazienti.storico', [$paziente->id, $k]) }}" class="btn-add-terapia" style="text-decoration:none;{{ $tipo===$k ? 'border-color:var(--accent);' : '' }}">{{ $lbl }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data prevista</th>
                    <th>Farmaco</th>
                    <th>Stato</th>
                    <th>Confermata da</th>
                    <th>Dispositivo</th>
                </tr>
                </thead>
                <tbody>
                @forelse($assunzioni as $a)
                    <tr>
                        <td>{{ optional($a->data_prevista)->format('d/m/Y H:i') }}</td>
                        <td>{{ $a->somministrazione->terapia->farmaco->nome ?? 'N/A' }}</td>
                        <td>{{ $a->stato }}</td>
                        <td>{{ $a->confermata_da }}</td>
                        <td>{{ $a->dispositivo?->codice_seriale ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);">Nessun record</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:10px;">{{ $assunzioni->links() }}</div>
    </div>
</main>
</body>
</html>
