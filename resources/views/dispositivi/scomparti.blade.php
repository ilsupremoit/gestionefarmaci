{{-- resources/views/dispositivi/scomparti.blade.php --}}
@extends('layouts.app')

@section('title', 'Scomparti - ' . $dispositivo->nome_dispositivo)

@section('content')
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-0">Configurazione Scomparti</h2>
            <small class="text-muted">
                {{ $dispositivo->nome_dispositivo }} ({{ $dispositivo->codice_seriale }})
                &mdash; Paziente: {{ $dispositivo->paziente?->utente?->nome ?? 'N/D' }}
                {{ $dispositivo->paziente?->utente?->cognome ?? '' }}
            </small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="richiediMappa(this)">
                Sync da dispositivo
            </button>
            <button class="btn btn-outline-warning btn-sm" onclick="testBuzzer(this)">
                Test Buzzer
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="alert alert-{{ $dispositivo->stato === 'attivo' ? 'success' : 'danger' }} py-2 mb-4">
        <strong>Dispositivo:</strong>
        {{ $dispositivo->stato === 'attivo' ? 'Online' : 'Offline' }}
        @if($dispositivo->ultima_connessione)
            &mdash; ultima connessione: {{ \Carbon\Carbon::parse($dispositivo->ultima_connessione)->diffForHumans() }}
        @endif
        @if($dispositivo->temperatura)
            | {{ $dispositivo->temperatura }}Â°C
        @endif
        @if($dispositivo->umidita)
            {{ $dispositivo->umidita }}%
        @endif
    </div>

    <form method="POST" action="{{ route('dispositivi.scomparti.salva', $dispositivo->id) }}">
        @csrf

        <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
            @foreach($dispositivo->scomparti as $scomparto)
            <div class="col">
                <div class="card h-100 border-{{ $scomparto->pieno ? 'success' : 'secondary' }}">
                    <div class="card-header d-flex justify-content-between align-items-center
                        {{ $scomparto->pieno ? 'bg-success bg-opacity-10' : '' }}">
                        <strong>Scomparto {{ $scomparto->numero_scomparto }}</strong>
                        <span class="badge bg-secondary">{{ $scomparto->angolo }}&deg;</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Farmaco assegnato</label>
                            <select name="scomparti[{{ $scomparto->numero_scomparto }}][id_farmaco]"
                                    class="form-select form-select-sm">
                                <option value="0">â€” Nessun farmaco â€”</option>
                                @foreach($farmaci as $farmaco)
                                    <option value="{{ $farmaco->id }}"
                                        {{ $scomparto->id_farmaco == $farmaco->id ? 'selected' : '' }}>
                                        {{ $farmaco->nome }}
                                        @if($farmaco->dose)({{ $farmaco->dose }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="scomparti[{{ $scomparto->numero_scomparto }}][pieno]"
                                   id="pieno_{{ $scomparto->numero_scomparto }}"
                                   value="1"
                                   {{ $scomparto->pieno ? 'checked' : '' }}>
                            <label class="form-check-label" for="pieno_{{ $scomparto->numero_scomparto }}">
                                Scomparto carico
                            </label>
                        </div>
                    </div>
                    @if($scomparto->farmaco)
                    <div class="card-footer bg-transparent small text-muted">
                        {{ $scomparto->farmaco->descrizione ?? '' }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Salva e invia al dispositivo</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Annulla</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const dispositivoId = {{ $dispositivo->id }};
const csrfToken     = '{{ csrf_token() }}';

async function richiediMappa(btn) {
    btn.disabled = true; btn.textContent = 'In attesa...';
    try {
        const res  = await fetch(`/mqtt/${dispositivoId}/mappa-scomparti`);
        const json = await res.json();
        alert(json.messaggio);
    } catch(e) { alert('Errore di comunicazione.'); }
    finally { btn.disabled = false; btn.textContent = 'Sync da dispositivo'; }
}

async function testBuzzer(btn) {
    btn.disabled = true;
    try {
        const res  = await fetch(`/mqtt/${dispositivoId}/buzzer-test`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
        });
        const json = await res.json();
        alert(json.messaggio);
    } finally { btn.disabled = false; }
}
</script>
@endpush
