<style>
    :root {
        --bg:      #f0f7ff;
        --surface: #ffffff;
        --border:  #dde8f5;
        --accent:  #2563eb;
        --accent2: #0891b2;
        --text:    #1e293b;
        --muted:   #64748b;
        --red:     #dc2626;
        --green:   #059669;
        --yellow:  #d97706;
        --shadow:  0 2px 12px rgba(37,99,235,.08);
        --shadow-md: 0 4px 20px rgba(37,99,235,.12);
    }

    *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        display: flex;
    }

    i[data-lucide] {
        width: 18px;
        height: 18px;
        stroke-width: 1.9;
        vertical-align: middle;
    }

    /* Sidebar */
    .sidebar {
        width: 240px;
        flex-shrink: 0;
        background: var(--surface);
        border-right: 1px solid var(--border);
        box-shadow: 2px 0 12px rgba(0,0,0,.04);
        display: flex;
        flex-direction: column;
        padding: 28px 0;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        overflow-y: auto;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 24px 24px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 20px;
    }

    .brand-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
    }

    .brand-name {
        font-family: 'Syne', sans-serif;
        font-size: 19px;
        font-weight: 800;
        color: var(--text);
    }

    .brand-badge {
        font-size: 9px;
        font-weight: 700;
        background: #eff6ff;
        color: var(--accent);
        padding: 2px 7px;
        border-radius: 999px;
        letter-spacing: .5px;
        border: 1px solid #bfdbfe;
    }

    .nav-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        padding: 0 24px;
        margin-bottom: 6px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 24px;
        font-size: 14px;
        color: var(--muted);
        text-decoration: none;
        transition: all .18s;
    }

    .nav-item:hover {
        color: var(--text);
        background: #f1f5f9;
    }

    .nav-item.active {
        color: var(--accent);
        background: #eff6ff;
        border-right: 3px solid var(--accent);
        font-weight: 600;
    }

    .nav-item .ico {
        width: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-footer {
        margin-top: auto;
        padding: 20px 24px 0;
        border-top: 1px solid var(--border);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .user-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
    }

    .user-role {
        font-size: 11px;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-logout {
        width: 100%;
        padding: 9px 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #b91c1c;
        font-size: 13px;
        cursor: pointer;
        transition: all .2s;
        font-family: inherit;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-logout:hover {
        background: #fee2e2;
    }

    /* Main */
    .main {
        margin-left: 240px;
        flex: 1;
        padding: 32px 36px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .page-header h1 {
        font-family: 'Syne', sans-serif;
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text);
    }

    .page-header p {
        color: var(--muted);
        font-size: 14px;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 28px;
    }

    .title h1 {
        font-family: 'Syne', sans-serif;
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text);
    }

    .title p {
        color: var(--muted);
        font-size: 14px;
    }

    .btn-back,
    .btn-ghost {
        text-decoration: none;
        color: var(--text);
        border: 1px solid var(--border);
        background: #fff;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s;
        font-weight: 500;
        cursor: pointer;
        font-family: inherit;
    }

    .btn-back:hover,
    .btn-ghost:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: #f8fbff;
    }

    .card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
        box-shadow: var(--shadow);
    }

    .card-title {
        font-family: 'Syne', sans-serif;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text);
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
        box-shadow: var(--shadow);
        transition: box-shadow .2s, transform .15s;
    }

    .stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--muted);
        font-weight: 500;
    }

    .stat-ico {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text);
    }

    .stat-ico.purple,
    .stat-ico.blue { background: #dbeafe; color: #1d4ed8; }
    .stat-ico.green { background: #d1fae5; color: #059669; }
    .stat-ico.yellow { background: #fef3c7; color: #d97706; }
    .stat-ico.red { background: #fee2e2; color: #dc2626; }

    .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--text);
    }

    .stat-sub {
        font-size: 11px;
        color: var(--muted);
        margin-top: 2px;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 18px;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 0;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field label,
    label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--muted);
        font-weight: 700;
        margin-bottom: 0;
    }

    .field input,
    .field select,
    .field textarea,
    input,
    textarea,
    select {
        width: 100%;
        background: #f8faff;
        border: 1.5px solid var(--border);
        color: var(--text);
        padding: 11px 14px;
        border-radius: 10px;
        font: inherit;
        font-size: 14px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus,
    input:focus,
    textarea:focus,
    select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(37,99,235,.08);
    }

    input::placeholder,
    textarea::placeholder {
        color: #94a3b8;
    }

    textarea {
        min-height: 80px;
        resize: vertical;
    }

    .section-label {
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--accent);
        grid-column: 1 / -1;
        margin-top: 8px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hint {
        font-size: 11px;
        color: var(--muted);
        margin-top: -2px;
    }

    .cf-row {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }

    .cf-row input {
        flex: 1;
    }

    .btn-calc {
        padding: 11px 14px;
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        border: none;
        border-radius: 10px;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        white-space: nowrap;
        transition: opacity .2s, transform .15s;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-calc:hover {
        opacity: .92;
        transform: translateY(-1px);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--accent), var(--accent2));
        border: none;
        color: #fff;
        padding: 12px 22px;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(37,99,235,.25);
        transition: opacity .2s, transform .15s;
        text-decoration: none;
    }

    .btn-primary:hover {
        opacity: .92;
        transform: translateY(-1px);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 8px;
    }

    .btn-danger {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .btn-danger:hover {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 13px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .alert-error ul {
        margin: 6px 0 0;
        padding-left: 18px;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #86efac;
        color: #166534;
        padding: 13px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-warn {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #92400e;
        padding: 13px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cf-result {
        display: none;
        margin-top: 6px;
        padding: 8px 12px;
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #15803d;
        font-family: monospace;
        letter-spacing: 1px;
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    th {
        text-align: left;
        padding: 10px 14px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
        font-weight: 600;
        background: #f8faff;
    }

    td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        color: var(--text);
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover td {
        background: #f8fbff;
    }

    .ruolo-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .ruolo-admin { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .ruolo-medico { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .ruolo-paziente { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
    .ruolo-familiare { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }

    .pag {
        display: flex;
        gap: 4px;
        margin-top: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .pag a,
    .pag span {
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 13px;
        text-decoration: none;
        border: 1px solid var(--border);
        color: var(--muted);
        background: #fff;
        transition: all .15s;
    }

    .pag a:hover {
        color: var(--accent);
        border-color: var(--accent);
    }

    .pag span.active {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    .empty-state {
        text-align: center;
        color: var(--muted);
        font-size: 13px;
        padding: 32px 0;
    }

    .dev-attivo {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #86efac;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .dev-offline {
        background: #f8fafc;
        color: var(--muted);
        border: 1px solid var(--border);
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
    }

    .dev-allarme {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    @media (max-width: 1024px) {
        .stats { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .sidebar { display: none; }
        .main { margin-left: 0; padding: 20px 16px; max-width: 100vw; }
    }

    @media (max-width: 600px) {
        .grid { grid-template-columns: 1fr; }
        .field.full { grid-column: 1; }
        .cf-row { flex-direction: column; align-items: stretch; }
    }
</style>
