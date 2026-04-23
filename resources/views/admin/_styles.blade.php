<style>
    :root{
        --bg:#f0f7ff;
        --surface:#fff;
        --border:#dde8f5;
        --accent:#7c3aed;
        --accent2:#2563eb;
        --accent3:#0891b2;
        --green:#059669;
        --red:#dc2626;
        --yellow:#d97706;
        --text:#1e293b;
        --muted:#64748b;
        --shadow:0 1px 4px rgba(124,58,237,.08);
        --shadow-md:0 4px 18px rgba(124,58,237,.12);
    }

    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

    body{
        font-family:'DM Sans',sans-serif;
        background:var(--bg);
        color:var(--text);
        min-height:100vh;
        display:flex
    }

    i[data-lucide]{
        width:18px;
        height:18px;
        stroke-width:1.9;
        vertical-align:middle;
    }

    /* ── Sidebar ── */
    .sidebar{
        width:240px;
        flex-shrink:0;
        background:#fff;
        border-right:1px solid var(--border);
        box-shadow:2px 0 12px rgba(0,0,0,.04);
        display:flex;
        flex-direction:column;
        padding:24px 0;
        position:fixed;
        top:0;
        left:0;
        height:100vh;
        overflow-y:auto
    }

    .brand{
        display:flex;
        align-items:center;
        gap:10px;
        padding:0 20px 20px;
        border-bottom:1px solid var(--border);
        margin-bottom:16px
    }

    .brand-icon{
        width:36px;
        height:36px;
        background:linear-gradient(135deg,var(--accent),var(--accent2));
        border-radius:10px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        flex-shrink:0
    }

    .brand-name{
        font-family:'Syne',sans-serif;
        font-size:19px;
        font-weight:800;
        color:var(--text)
    }

    .brand-badge{
        font-size:9px;
        font-weight:700;
        background:linear-gradient(135deg,var(--accent),var(--accent2));
        color:#fff;
        padding:2px 7px;
        border-radius:10px;
        letter-spacing:.5px
    }

    .nav-label{
        font-size:10px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:1.2px;
        color:#94a3b8;
        padding:0 20px;
        margin-bottom:6px
    }

    .nav-item{
        display:flex;
        align-items:center;
        gap:10px;
        padding:10px 20px;
        font-size:14px;
        color:var(--muted);
        text-decoration:none;
        transition:all .15s;
        border-left:3px solid transparent
    }

    .nav-item:hover{
        color:var(--text);
        background:#f5f3ff
    }

    .nav-item.active{
        color:var(--accent);
        background:#f5f3ff;
        border-left-color:var(--accent);
        font-weight:600
    }

    .nav-item .ico{
        width:20px;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0
    }

    .sidebar-footer{
        margin-top:auto;
        padding:16px 20px 0;
        border-top:1px solid var(--border)
    }

    .user-info{
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:12px
    }

    .avatar{
        width:36px;
        height:36px;
        border-radius:50%;
        background:linear-gradient(135deg,var(--accent),var(--accent2));
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:13px;
        font-weight:700;
        color:#fff;
        flex-shrink:0
    }

    .user-name{
        font-size:13px;
        font-weight:600;
        color:var(--text)
    }

    .user-role{
        font-size:11px;
        color:var(--muted);
        display:flex;
        align-items:center;
        gap:6px
    }

    .btn-logout{
        width:100%;
        padding:9px 12px;
        background:#fef2f2;
        border:1px solid #fecaca;
        border-radius:8px;
        color:#b91c1c;
        font-size:13px;
        cursor:pointer;
        transition:all .15s;
        font-family:inherit;
        font-weight:600;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:8px
    }

    .btn-logout:hover{
        background:#fee2e2
    }

    /* ── Main ── */
    .main{
        margin-left:240px;
        flex:1;
        padding:32px 36px;
        max-width:calc(100vw - 240px)
    }

    .page-header{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        margin-bottom:24px;
        flex-wrap:wrap;
        gap:16px
    }

    .page-header h1{
        font-family:'Syne',sans-serif;
        font-size:26px;
        font-weight:700;
        margin-bottom:4px;
        color:var(--text)
    }

    .page-header p{
        color:var(--muted);
        font-size:14px
    }

    /* ── Alerts ── */
    .alert{
        border-radius:12px;
        padding:13px 16px;
        margin-bottom:20px;
        font-size:14px;
        border:1px solid;
        display:flex;
        align-items:flex-start;
        gap:10px
    }

    .alert-success{background:#f0fdf4;border-color:#86efac;color:#166534}
    .alert-error{background:#fef2f2;border-color:#fecaca;color:#991b1b}
    .alert-warn{background:#fff7ed;border-color:#fed7aa;color:#92400e}

    /* ── Cards ── */
    .card{
        background:#fff;
        border:1px solid var(--border);
        border-radius:16px;
        padding:24px;
        box-shadow:var(--shadow)
    }

    .card-title{
        font-family:'Syne',sans-serif;
        font-size:16px;
        font-weight:700;
        margin-bottom:20px;
        display:flex;
        align-items:center;
        gap:8px;
        color:var(--text)
    }

    /* ── Stats ── */
    .stats{
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:16px;
        margin-bottom:24px
    }

    .stat-card{
        background:#fff;
        border:1px solid var(--border);
        border-radius:14px;
        padding:20px;
        box-shadow:var(--shadow);
        transition:box-shadow .18s, transform .18s
    }

    .stat-card:hover{
        box-shadow:var(--shadow-md);
        transform:translateY(-1px)
    }

    .stat-top{
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-bottom:10px
    }

    .stat-label{
        font-size:12px;
        color:var(--muted);
        font-weight:500
    }

    .stat-ico{
        width:36px;
        height:36px;
        border-radius:9px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:var(--text)
    }

    .stat-ico.purple{background:#f5f3ff;color:#7c3aed}
    .stat-ico.blue{background:#eff6ff;color:#2563eb}
    .stat-ico.green{background:#f0fdf4;color:#059669}
    .stat-ico.yellow{background:#fff7ed;color:#d97706}
    .stat-ico.red{background:#fef2f2;color:#dc2626}

    .stat-value{
        font-family:'Syne',sans-serif;
        font-size:26px;
        font-weight:700;
        color:var(--text)
    }

    .stat-sub{
        font-size:11px;
        color:var(--muted);
        margin-top:2px
    }

    /* ── Table ── */
    .table-wrap{overflow-x:auto}

    table{
        width:100%;
        border-collapse:collapse;
        font-size:13px
    }

    th{
        text-align:left;
        padding:10px 14px;
        font-size:11px;
        text-transform:uppercase;
        letter-spacing:.5px;
        color:var(--muted);
        border-bottom:2px solid var(--border);
        font-weight:600;
        background:#f8faff
    }

    td{
        padding:12px 14px;
        border-bottom:1px solid var(--border);
        vertical-align:middle;
        color:var(--text)
    }

    tr:last-child td{border-bottom:none}
    tr:hover td{background:#faf8ff}

    /* ── Badge ruolo ── */
    .ruolo-badge{
        display:inline-flex;
        align-items:center;
        padding:3px 9px;
        border-radius:20px;
        font-size:11px;
        font-weight:700
    }

    .ruolo-admin{background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe}
    .ruolo-medico{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
    .ruolo-paziente{background:#f0fdf4;color:#15803d;border:1px solid #86efac}
    .ruolo-familiare{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}

    /* ── Bottoni ── */
    .btn{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:8px 14px;
        border-radius:8px;
        font-size:13px;
        font-weight:600;
        font-family:inherit;
        cursor:pointer;
        border:none;
        text-decoration:none;
        transition:all .15s
    }

    .btn-primary{
        background:linear-gradient(135deg,var(--accent),var(--accent2));
        color:#fff;
        box-shadow:0 3px 10px rgba(124,58,237,.2)
    }

    .btn-primary:hover{opacity:.9}

    .btn-sm{
        padding:6px 12px;
        font-size:12px;
        border-radius:7px
    }

    .btn-danger{
        background:#fef2f2;
        color:#b91c1c;
        border:1px solid #fecaca
    }

    .btn-danger:hover{background:#fee2e2}

    .btn-ghost{
        background:#f8faff;
        color:var(--muted);
        border:1px solid var(--border)
    }

    .btn-ghost:hover{
        border-color:var(--accent);
        color:var(--accent)
    }

    /* ── Form ── */
    .field{margin-bottom:16px}

    .field label{
        display:block;
        font-size:11px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.7px;
        color:var(--muted);
        margin-bottom:6px
    }

    .field input,.field select,.field textarea{
        width:100%;
        background:#f8faff;
        border:1.5px solid var(--border);
        border-radius:10px;
        padding:10px 14px;
        color:var(--text);
        font-family:'DM Sans',sans-serif;
        font-size:14px;
        outline:none;
        transition:border-color .2s, box-shadow .2s
    }

    .field input:focus,.field select:focus,.field textarea:focus{
        border-color:var(--accent);
        box-shadow:0 0 0 3px rgba(124,58,237,.1)
    }

    .field textarea{
        min-height:90px;
        resize:vertical
    }

    .field-error{
        font-size:11px;
        color:var(--red);
        margin-top:4px
    }

    .hint{
        font-size:11px;
        color:var(--muted);
        margin-top:3px
    }

    /* ── Pagination ── */
    .pag{
        display:flex;
        gap:4px;
        margin-top:20px;
        justify-content:center;
        flex-wrap:wrap
    }

    .pag a,.pag span{
        padding:7px 12px;
        border-radius:8px;
        font-size:13px;
        text-decoration:none;
        border:1px solid var(--border);
        color:var(--muted);
        background:#fff;
        transition:all .15s
    }

    .pag a:hover{
        color:var(--accent);
        border-color:var(--accent)
    }

    .pag span.active{
        background:var(--accent);
        color:#fff;
        border-color:var(--accent)
    }

    /* ── Empty state ── */
    .empty-state{
        text-align:center;
        color:var(--muted);
        font-size:13px;
        padding:32px 0
    }

    /* ── Dispositivo badge ── */
    .dev-attivo{
        background:#f0fdf4;
        color:#15803d;
        border:1px solid #86efac;
        padding:2px 9px;
        border-radius:20px;
        font-size:11px;
        font-weight:600
    }

    .dev-offline{
        background:#f8fafc;
        color:var(--muted);
        border:1px solid var(--border);
        padding:2px 9px;
        border-radius:20px;
        font-size:11px
    }

    .dev-allarme{
        background:#fef2f2;
        color:#b91c1c;
        border:1px solid #fecaca;
        padding:2px 9px;
        border-radius:20px;
        font-size:11px;
        font-weight:600
    }

    @media(max-width:1024px){
        .stats{grid-template-columns:repeat(2,1fr)}
    }

    @media(max-width:768px){
        .sidebar{display:none}
        .main{margin-left:0;padding:20px 16px;max-width:100vw}
    }
</style>
