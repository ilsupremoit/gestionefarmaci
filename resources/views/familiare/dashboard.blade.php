{{-- resources/views/familiare/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PillMate — Dashboard Familiare</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
    <style>
        :root{--bg:#0b0f1a;--surface:#111827;--border:#1f2d45;--accent:#3b82f6;--accent2:#06b6d4;--green:#10b981;--red:#ef4444;--yellow:#f59e0b;--text:#e2e8f0;--muted:#64748b}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
        .sidebar{width:240px;flex-shrink:0;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:28px 0;position:fixed;top:0;left:0;height:100vh}
        .brand{display:flex;align-items:center;gap:12px;padding:0 24px 28px;border-bottom:1px solid var(--border);margin-bottom:20px}
        .brand-icon{width:38px;height:38px;background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px}
        .brand-name{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;background:linear-gradient(135deg,#fff,var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .nav-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:0 24px;margin-bottom:8px}
        .nav-item{display:flex;align-items:center;gap:12px;padding:10px 24px;font-size:14px;color:var(--muted);text-decoration:none;transition:all .2s}
        .nav-item:hover{color:var(--text);background:rgba(255,255,255,.04)}
        .nav-item.active{color:var(--accent);background:rgba(59,130,246,.08);border-right:2px solid var(--accent)}
        .nav-item .ico{font-size:16px;width:20px;text-align:center}
        .sidebar-footer{margin-top:auto;padding:0 24px;border-top:1px solid var(--border);padding-top:20px}
        .user-info{display:flex;align-items:center;gap:10px;margin-bottom:14px}
        .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--yellow),var(--accent));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700}
        .user-name{font-size:13px;font-weight:600}
        .user-role{font-size:11px;color:var(--muted)}
        .btn-logout{width:100%;padding:9px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;color:#fca5a5;font-size:13px;cursor:pointer;transition:all .2s;font-family:inherit}
        .btn-logout:hover{background:rgba(239,68,68,.2)}
        .main{margin-left:240px;flex:1;padding:36px 40px}
        .page-header{margin-bottom:32px}
        .page-header h1{font-family:'Syne',sans-serif;font-size:26px;font-weight:700;margin-bottom:4px}
        .page-header p{color:var(--muted);font-size:14px}
        .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:32px}
        .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px}
        .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
        .stat-label{font-size:12px;color:var(--muted);font-weight:500}
        .stat-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px}
        .stat-ico.blue{background:rgba(59,130,246,.15)}
        .stat-ico.green{background:rgba(16,185,129,.15)}
        .stat-ico.yellow{background:rgba(245,158,11,.15)}
        .stat-value{font-family:'Syne',sans-serif;font-size:28px;font-weight:700}
        .stat-sub{font-size:11px;color:var(--muted);margin-top:2px}
        .content-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px}
        .card-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:8px}
        .empty-state{text-align:center;color:var(--muted);font-size:13px;padding:20px 0}
        @media(max-width:768px){.sidebar{display:none}.main{margin-left:0;padding:24px 20px}}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">💊</div>
        <span class="brand-name">PillMate</span>
    </div>
    <div class="nav-label">Menu</div>
    <a class="nav-item active" href="#"><span class="ico">🏠</span> Dashboard</a>
    <a class="nav-item" href="#"><span class="ico">👥</span> I miei cari</a>
    <a class="nav-item" href="#"><span class="ico">📋</span> Assunzioni</a>
    <a class="nav-item" href="#"><span class="ico">🔔</span> Notifiche</a>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr($familiare->nome, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ $familiare->nome }} {{ $familiare->cognome }}</div>
                <div class="user-role">👨‍👩‍👧 Familiare</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">🚪 Esci</button>
        </form>
    </div>
</aside>
<main class="main">
    <div class="page-header">
        <h1>Ciao, {{ $familiare->nome }} 👋</h1>
        <p>Monitora le terapie dei tuoi cari — {{ now()->format('d/m/Y') }}</p>
    </div>
    <div class="stats">
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Pazienti monitorati</span><div class="stat-ico blue">👥</div></div>
            <div class="stat-value">0</div>
            <div class="stat-sub">collegati</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Dosi regolari oggi</span><div class="stat-ico green">✅</div></div>
            <div class="stat-value">0</div>
            <div class="stat-sub">assunte</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><span class="stat-label">Allerte</span><div class="stat-ico yellow">⚠️</div></div>
            <div class="stat-value">0</div>
            <div class="stat-sub">attive</div>
        </div>
    </div>
    <div class="content-grid">
        <div class="card">
            <div class="card-title">👥 Pazienti collegati</div>
            <div class="empty-state">Nessun paziente ancora collegato.</div>
        </div>
        <div class="card">
            <div class="card-title">🔔 Notifiche recenti</div>
            <div class="empty-state">Nessuna notifica.</div>
        </div>
    </div>
</main>
</body>
</html>
