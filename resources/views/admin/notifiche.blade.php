<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate Admin — Messaggi</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')
</head>
<body>
@include('admin._sidebar', ['active' => 'notifiche'])
<main class="main">

    <div class="page-header">
        <div>
            <h1>📨 Messaggi</h1>
            <p>Invia e ricevi comunicazioni con medici e altri admin</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

    <div style="display:grid;grid-template-columns:400px 1fr;gap:24px;align-items:start;">

        {{-- FORM INVIO --}}
        <div class="card" style="position:sticky;top:24px;">
            <div class="card-title">✉️ Invia messaggio</div>
            <form method="POST" action="{{ route('admin.notifiche.invia') }}">
                @csrf
                <div class="field">
                    <label>Destinatario *</label>
                    <select name="id_utente" required>
                        <option value="">— Seleziona destinatario —</option>
                        @if($medici->count())
                        <optgroup label="👨‍⚕️ Medici">
                            @foreach($medici as $m)
                                <option value="{{ $m->id }}">Dr. {{ $m->cognome }} {{ $m->nome }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if($adminList->count())
                        <optgroup label="🛡️ Altri Admin">
                            @foreach($adminList as $a)
                                <option value="{{ $a->id }}">{{ $a->cognome }} {{ $a->nome }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                    </select>
                </div>
                <div class="field">
                    <label>Tipo *</label>
                    <select name="tipo">
                        <option value="info">ℹ️ Informazione</option>
                        <option value="promemoria">⏰ Promemoria</option>
                        <option value="allarme">🚨 Urgente</option>
                        <option value="messaggio">💬 Messaggio</option>
                    </select>
                </div>
                <div class="field">
                    <label>Oggetto *</label>
                    <input type="text" name="titolo" required maxlength="100" placeholder="Es. Aggiornamento sistema"/>
                </div>
                <div class="field">
                    <label>Messaggio *</label>
                    <textarea name="messaggio" rows="4" required placeholder="Scrivi qui il tuo messaggio..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">📤 Invia</button>
            </form>
        </div>

        {{-- COLONNA DESTRA --}}
        <div>

            {{-- RICEVUTI --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="card-title">📥 Messaggi ricevuti ({{ $ricevuti->total() }})</div>
                @forelse($ricevuti as $n)
                @php
                    $mitt = \App\Models\User::find($n->id_mittente);
                    $ico  = match($n->tipo ?? 'info') { 'allarme'=>'🚨','promemoria'=>'⏰','messaggio'=>'💬',default=>'ℹ️' };
                @endphp
                <div style="padding:12px 0;border-bottom:1px solid var(--border);display:flex;gap:12px;">
                    <div style="font-size:22px;flex-shrink:0;">{{ $ico }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                            <div style="font-weight:700;font-size:13px;">{{ $n->titolo }}</div>
                            <div style="font-size:11px;color:var(--muted);">{{ \Carbon\Carbon::parse($n->data_invio)->format('d/m H:i') }}</div>
                        </div>
                        <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">
                            Da: <strong>{{ $mitt ? $mitt->cognome.' '.$mitt->nome.' ('.ucfirst($mitt->ruolo).')' : 'Sistema' }}</strong>
                        </div>
                        <div style="font-size:13px;background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:8px 10px;">
                            {{ $n->messaggio }}
                        </div>
                        <div style="margin-top:5px;font-size:11px;color:{{ $n->letta ? '#15803d' : 'var(--muted)' }};">
                            {{ $n->letta ? '✓ Letto' : '● Non letto' }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">Nessun messaggio ricevuto.</div>
                @endforelse
                <div style="margin-top:12px;">{{ $ricevuti->links('pagination::simple-tailwind') }}</div>
            </div>

            {{-- INVIATI --}}
            <div class="card">
                <div class="card-title">📤 Messaggi inviati ({{ $inviati->total() }})</div>
                @forelse($inviati as $n)
                @php
                    $dest = \App\Models\User::find($n->id_utente);
                    $ico  = match($n->tipo ?? 'info') { 'allarme'=>'🚨','promemoria'=>'⏰','messaggio'=>'💬',default=>'ℹ️' };
                @endphp
                <div style="padding:12px 0;border-bottom:1px solid var(--border);display:flex;gap:12px;">
                    <div style="font-size:22px;flex-shrink:0;">{{ $ico }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                            <div style="font-weight:700;font-size:13px;">{{ $n->titolo }}</div>
                            <div style="font-size:11px;color:var(--muted);">{{ \Carbon\Carbon::parse($n->data_invio)->format('d/m H:i') }}</div>
                        </div>
                        <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">
                            A: <strong>{{ $dest ? $dest->cognome.' '.$dest->nome.' ('.ucfirst($dest->ruolo).')' : '?' }}</strong>
                        </div>
                        <div style="font-size:12px;color:#475569;">{{ \Illuminate\Support\Str::limit($n->messaggio, 120) }}</div>
                        <div style="margin-top:5px;font-size:11px;color:{{ $n->letta ? '#15803d' : 'var(--muted)' }};">
                            {{ $n->letta ? '✓ Letto' : '● In attesa di lettura' }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">Nessun messaggio inviato.</div>
                @endforelse
                <div style="margin-top:12px;">{{ $inviati->links('pagination::simple-tailwind') }}</div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
