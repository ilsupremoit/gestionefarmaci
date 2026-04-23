<!DOCTYPE html>
<html lang="it">
<head>
    @vite('resources/js/app.js')
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate Admin — Utenti</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    @include('admin._styles')
</head>
<body>
@include('admin._sidebar', ['active' => 'utenti'])

<main class="main">
    <div class="page-header">
        <div>
            <h1>Gestione Utenti</h1>
            <p>Crea, modifica ed elimina account medici, pazienti e admin</p>
        </div>

        <a href="{{ route('admin.utenti.create') }}" class="btn btn-primary">
            <i data-lucide="user-plus"></i>
            Nuovo utente
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i data-lucide="check-circle-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i data-lucide="circle-alert"></i>
            {{ session('error') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.utenti') }}" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        <div style="position:relative;flex:1;min-width:220px;">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);display:flex;align-items:center;">
                <i data-lucide="search" style="width:16px;height:16px;"></i>
            </span>
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                placeholder="Cerca per nome, email, username..."
                style="width:100%;background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px 10px 38px;font:inherit;font-size:14px;color:var(--text);outline:none;"
                onfocus="this.style.borderColor='var(--accent)'"
                onblur="this.style.borderColor='var(--border)'"
            />
        </div>

        <select
            name="ruolo"
            style="background:#fff;border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font:inherit;font-size:14px;color:var(--text);outline:none;"
            onchange="this.form.submit()"
        >
            <option value="">Tutti i ruoli</option>
            @foreach(['medico','paziente','admin'] as $r)
                <option value="{{ $r }}" {{ $ruolo===$r ? 'selected' : '' }}>
                    {{ ucfirst($r) }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-ghost">
            <i data-lucide="search"></i>
            Cerca
        </button>

        @if($q || $ruolo)
            <a href="{{ route('admin.utenti') }}" class="btn btn-ghost">
                <i data-lucide="rotate-ccw"></i>
                Reset
            </a>
        @endif
    </form>

    <div class="card">
        <div class="card-title">
            <i data-lucide="users"></i>
            Elenco utenti
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Utente</th>
                    <th>Username / Email</th>
                    <th>Ruolo</th>
                    <th>Password</th>
                    <th>Registrato</th>
                    <th>Azioni</th>
                </tr>
                </thead>
                <tbody>
                @forelse($utenti as $u)
                    <tr>
                        <td style="color:var(--muted);font-size:12px;">#{{ $u->id }}</td>

                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0;">
                                    {{ strtoupper(substr($u->nome,0,1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:13px;">{{ $u->cognome }} {{ $u->nome }}</div>
                                    @if($u->telefono)
                                        <div style="font-size:11px;color:var(--muted);display:flex;align-items:center;gap:5px;">
                                            <i data-lucide="phone" style="width:13px;height:13px;"></i>
                                            {{ $u->telefono }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td style="font-size:12px;">
                            @if($u->username)
                                <div style="font-weight:600;">{{ $u->username }}</div>
                            @endif
                            @if($u->email)
                                <div style="color:var(--muted);">{{ $u->email }}</div>
                            @endif
                        </td>

                        <td>
                            <span class="ruolo-badge ruolo-{{ $u->ruolo }}">{{ ucfirst($u->ruolo) }}</span>
                        </td>

                        <td>
                            @if($u->must_change_password)
                                <span style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-size:11px;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;">
                                <i data-lucide="clock-3" style="width:13px;height:13px;"></i>
                                Da cambiare
                            </span>
                            @else
                                <span style="background:#f0fdf4;color:#15803d;border:1px solid #86efac;font-size:11px;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;">
                                <i data-lucide="check" style="width:13px;height:13px;"></i>
                                Impostata
                            </span>
                            @endif
                        </td>

                        <td style="font-size:12px;color:var(--muted);">
                            {{ $u->created_at ? \Carbon\Carbon::parse($u->created_at)->format('d/m/Y') : '—' }}
                        </td>

                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <button
                                    onclick="openReset({{ $u->id }}, '{{ addslashes($u->nome.' '.$u->cognome) }}')"
                                    class="btn btn-sm btn-ghost"
                                    title="Reset password"
                                    type="button"
                                >
                                    <i data-lucide="key-round"></i>
                                </button>

                                @if($u->id !== auth()->id())
                                    <form
                                        method="POST"
                                        action="{{ route('admin.utenti.elimina', $u->id) }}"
                                        onsubmit="return confirm('Eliminare {{ addslashes($u->nome.' '.$u->cognome) }}? L\'operazione è irreversibile.')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Elimina">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">Nessun utente trovato.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pag">{{ $utenti->links('pagination::simple-tailwind') }}</div>
    </div>
</main>

<div id="modalReset" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="key-round"></i>
            Reset password
        </div>

        <div id="modalNome" style="font-size:13px;color:var(--muted);margin-bottom:16px;"></div>

        <form id="formReset" method="POST">
            @csrf
            <div class="field">
                <label>Nuova password *</label>
                <input type="text" name="nuova_password" placeholder="min. 6 caratteri" required minlength="6"/>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i>
                    Salva
                </button>
                <button type="button" onclick="closeReset()" class="btn btn-ghost">
                    Annulla
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReset(id, nome) {
        document.getElementById('formReset').action = '/admin/utenti/' + id + '/reset-password';
        document.getElementById('modalNome').textContent = 'Utente: ' + nome;
        document.getElementById('modalReset').style.display = 'flex';
    }

    function closeReset() {
        document.getElementById('modalReset').style.display = 'none';
    }
</script>
</body>
</html>
