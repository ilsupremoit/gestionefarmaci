<style>
:root{
    --bg:#f0f7ff;--surface:#ffffff;--border:#dde8f5;
    --accent:#2563eb;--accent2:#0891b2;--green:#059669;
    --red:#dc2626;--yellow:#d97706;--teal:#0891b2;
    --text:#1e293b;--muted:#64748b;
    --shadow:0 1px 4px rgba(37,99,235,.08);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}

/* ── Sidebar ── */
.sidebar{width:240px;flex-shrink:0;background:#fff;border-right:1px solid var(--border);box-shadow:2px 0 12px rgba(0,0,0,.04);display:flex;flex-direction:column;padding:24px 0;position:fixed;top:0;left:0;height:100vh;overflow-y:auto}
.brand{display:flex;align-items:center;gap:12px;padding:0 20px 20px;border-bottom:1px solid var(--border);margin-bottom:16px}
.brand-name{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:var(--text)}
.nav-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;padding:0 20px;margin-bottom:6px}
.nav-item{display:flex;align-items:center;gap:12px;padding:10px 20px;font-size:14px;color:var(--muted);text-decoration:none;transition:all .15s;border-left:3px solid transparent;position:relative}
.nav-item:hover{color:var(--text);background:#f1f5f9}
.nav-item.active{color:var(--accent);background:#eff6ff;border-left-color:var(--accent);font-weight:600}
.nav-item .ico{font-size:16px;width:20px;text-align:center;flex-shrink:0}
.nav-badge{position:absolute;right:16px;top:50%;transform:translateY(-50%);background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;min-width:18px;text-align:center}
.sidebar-footer{margin-top:auto;padding:16px 20px 0;border-top:1px solid var(--border)}
.user-info{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.user-name{font-size:13px;font-weight:600;color:var(--text)}
.user-role{font-size:11px;color:var(--muted)}
.btn-logout{width:100%;padding:9px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#b91c1c;font-size:13px;cursor:pointer;transition:all .2s;font-family:inherit;font-weight:600}
.btn-logout:hover{background:#fee2e2}

/* ── Main ── */
.main{margin-left:240px;flex:1;padding:32px 36px;max-width:calc(100vw - 240px)}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px}
.page-header h1{font-family:'Syne',sans-serif;font-size:26px;font-weight:700;margin-bottom:4px;color:var(--text)}
.page-header p{color:var(--muted);font-size:14px}
.medico-badge{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--border);padding:10px 14px;border-radius:12px;box-shadow:var(--shadow)}
.medico-ico{font-size:20px}

/* ── Stats ── */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.stat-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px;box-shadow:var(--shadow)}
.stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.stat-label{font-size:12px;color:var(--muted);font-weight:500}
.stat-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px}
.stat-ico.blue{background:#eff6ff}.stat-ico.green{background:#f0fdf4}.stat-ico.yellow{background:#fff7ed}.stat-ico.teal{background:#ecfeff}.stat-ico.red{background:#fef2f2}
.stat-value{font-family:'Syne',sans-serif;font-size:28px;font-weight:700;color:var(--text)}
.c-blue{color:var(--accent)}.c-green{color:var(--green)}.c-yellow{color:var(--yellow)}.c-teal{color:var(--teal)}.c-red{color:var(--red)}
.stat-sub{font-size:11px;color:var(--muted);margin-top:2px}

/* ── Alert ── */
.alert-banner{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:12px;font-size:13px;margin-bottom:16px}
.alert-success{background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:12px;font-size:13px;margin-bottom:16px}

/* ── Grid ── */
.content-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.full-col{grid-column:1 / -1}

/* ── Card ── */
.card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)}
.card-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;color:var(--text)}
.card-link{margin-left:auto;font-size:12px;font-weight:400;color:var(--accent);text-decoration:none;font-family:'DM Sans',sans-serif}
.card-link:hover{text-decoration:underline}
.empty-state{text-align:center;color:var(--muted);font-size:13px;padding:24px 0}

/* ── Assunzioni rows ── */
.assunzione-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
.assunzione-row:last-child{border-bottom:none}
.assunzione-ico{width:36px;height:36px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.assunzione-info{flex:1;min-width:0}
.assunzione-name{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text)}
.assunzione-time{font-size:11px;color:var(--muted);margin-top:2px}

/* ── Terapia rows ── */
.terapia-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)}
.terapia-row:last-child{border-bottom:none}

/* ── Stato badge ── */
.stato-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;flex-shrink:0}
.stato-assunta,.stato-erogata{background:#f0fdf4;color:#15803d;border:1px solid #86efac}
.stato-saltata,.stato-non_ritirata{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.stato-in_attesa,.stato-ritardo{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
.stato-allarme_attivo{background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;animation:pulse 1.5s infinite}
.stato-apertura_forzata{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}

/* ── Badge terapia ── */
.badge-attiva{background:#f0fdf4;color:#15803d;border:1px solid #86efac;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;flex-shrink:0}
.badge-conclusa{background:#f8fafc;color:var(--muted);border:1px solid var(--border);padding:3px 10px;border-radius:20px;font-size:11px;flex-shrink:0}

/* ── Bottone ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;text-decoration:none;transition:all .2s}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;box-shadow:0 4px 12px rgba(37,99,235,.2)}
.btn-primary:hover{opacity:.9}
.btn-ghost{background:#f8faff;color:var(--muted);border:1px solid var(--border)}
.btn-ghost:hover{color:var(--text);border-color:var(--accent)}

/* ── Filtri ── */
.filters{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;align-items:flex-end}
.filter-group{display:flex;flex-direction:column;gap:5px}
.filter-group label{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);font-weight:600}
.filter-group input,.filter-group select{background:#fff;border:1.5px solid var(--border);color:var(--text);padding:9px 12px;border-radius:9px;font:inherit;font-size:13px;outline:none;transition:border-color .2s}
.filter-group input:focus,.filter-group select:focus{border-color:var(--accent)}

/* ── Tabella ── */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:13px}
th{text-align:left;padding:10px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);border-bottom:2px solid var(--border);font-weight:600;background:#f8faff}
td{padding:12px 12px;border-bottom:1px solid var(--border);vertical-align:middle;color:var(--text)}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f8faff;cursor:pointer}

/* ── Dispositivo card ── */
.device-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.device-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px;transition:border-color .2s;box-shadow:var(--shadow)}
.device-card:hover{border-color:var(--accent)}
.device-header{display:flex;align-items:center;gap:14px;margin-bottom:16px}
.device-img{width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0}
.device-name{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:3px;color:var(--text)}
.device-serial{font-size:11px;color:var(--muted)}
.device-status{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;margin-bottom:14px}
.status-attivo{background:#f0fdf4;color:#15803d;border:1px solid #86efac}
.status-offline{background:#f8fafc;color:var(--muted);border:1px solid var(--border)}
.status-errore{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.status-manutenzione{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
.dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot-green{background:var(--green)}.dot-gray{background:var(--muted)}.dot-red{background:var(--red)}.dot-yellow{background:var(--yellow)}
.device-metrics{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
.metric{background:#f8faff;border:1px solid var(--border);border-radius:8px;padding:8px 10px}
.metric-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px}
.metric-value{font-size:14px;font-weight:700;font-family:'Syne',sans-serif;color:var(--text)}
.device-last{font-size:11px;color:var(--muted);text-align:center;margin-top:8px}

/* ── Notifica item ── */
.notifica-item{padding:14px;border:1px solid var(--border);border-radius:12px;margin-bottom:10px;transition:border-color .2s;background:#fff;box-shadow:var(--shadow)}
.notifica-item.unread{border-left:3px solid var(--accent);background:#f8fbff}
.notifica-item:hover{border-color:var(--accent)}
.notifica-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.notifica-title{font-weight:600;font-size:14px;color:var(--text)}
.notifica-time{font-size:11px;color:var(--muted)}
.notifica-body{font-size:13px;color:var(--muted)}

/* ── Breadcrumb ── */
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:20px}
.breadcrumb a{color:var(--muted);text-decoration:none}
.breadcrumb a:hover{color:var(--accent)}
.breadcrumb .sep{opacity:.4}

/* ── Detail ── */
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.detail-field{padding:14px;background:#f8faff;border:1px solid var(--border);border-radius:10px}
.detail-label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);font-weight:600;margin-bottom:5px}
.detail-value{font-size:14px;font-weight:500;color:var(--text)}

/* ── Pagination ── */
.pagination{display:flex;gap:6px;margin-top:20px;justify-content:center;flex-wrap:wrap}
.pagination a,.pagination span{padding:7px 12px;border-radius:8px;font-size:13px;text-decoration:none;border:1px solid var(--border);color:var(--muted);transition:all .2s;background:#fff}
.pagination a:hover{color:var(--accent);border-color:var(--accent)}
.pagination .active span{background:var(--accent);color:#fff;border-color:var(--accent)}

/* ── Terapia card ── */
.terapia-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:12px;transition:border-color .2s;box-shadow:var(--shadow)}
.terapia-card:hover{border-color:var(--accent)}
.terapia-card-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;gap:12px}
.terapia-card-name{font-family:'Syne',sans-serif;font-size:17px;font-weight:700;color:var(--text)}
.terapia-card-dose{font-size:13px;color:var(--muted);margin-top:2px}
.terapia-meta{display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:var(--muted);margin-bottom:12px}
.terapia-meta span{display:flex;align-items:center;gap:4px}
.terapia-orari{display:flex;flex-wrap:wrap;gap:6px}
.orario-chip{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:3px 10px;border-radius:10px;font-size:12px}
.istruzioni-box{margin-top:12px;padding:10px 12px;background:#f8faff;border:1px solid var(--border);border-radius:8px;font-size:12px;color:var(--muted)}
.medico-info{display:flex;align-items:center;gap:8px;margin-top:10px;padding:8px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:12px;color:var(--muted)}
.adherence-bar{height:6px;background:#e2e8f0;border-radius:3px;margin-top:4px;overflow:hidden}
.adherence-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--accent),var(--green));transition:width .6s}

@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}

@media(max-width:1024px){.stats{grid-template-columns:repeat(2,1fr)}.content-grid{grid-template-columns:1fr}}
@media(max-width:768px){.sidebar{display:none}.main{margin-left:0;padding:20px 16px;max-width:100vw}.stats{grid-template-columns:1fr 1fr}}
</style>
