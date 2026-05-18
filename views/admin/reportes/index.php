<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        body { background:#1a1a2e; color:#eaeaea; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 16px; }
        .header-title { margin-bottom: 30px; text-align: center; color: #00fff0; }
        .section-card { background:#16213e; border-radius: 18px; padding:24px; margin-bottom: 24px; box-shadow: 0 0 18px rgba(0,255,240,0.08); }
        .section-card h2 { margin-top: 0; color: #a0fdfd; }
        .field-row { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; margin-bottom: 16px; }
        .field-row label { display: block; font-size: 0.95rem; color: #e3f7ff; margin-bottom: 6px; }
        .field-row input,
        .field-row select { width: 220px; padding: 10px 12px; border-radius: 10px; border: 1px solid #0f3460; background: rgba(0,255,240,0.06); color: #eaeaea; }
        .field-row input[type="date"] { max-width: 220px; }
        .help-text { color: #a0cfe8; margin-bottom: 18px; }
        .form-actions { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 16px; }
        .note { color: #e0e0e0; font-size: 0.95rem; }
        @media(max-width: 820px) {
            .field-row { flex-direction: column; align-items: stretch; }
            .field-row input, .field-row select { width: 100%; }
            .form-actions { flex-direction: column; width: 100%; }
        }
    </style>
</head>
<body>
<?php
$role = $_SESSION['role'] ?? '';
$useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia', 'supervisor'], true);
if ($useEmployeeHeader) {
    include __DIR__ . '/../../employee/partials/header.php';
} else {
    include __DIR__ . '/../partials/header.php';
}
$reportsAction = $reportsAction ?? 'reports';
$reportsController = ($dashboardMode ?? false) ? 'dashboard' : 'admin';
$reportUrlBase = BASE_URL . 'index.php?controller=' . $reportsController . '&action=' . $reportsAction;
?>
<div class="container">
    <h1 class="header-title">Reportes Exportables</h1>

    <div class="section-card">
        <h2>Inventario Actual</h2>
        <p class="help-text">Descarga un archivo XLSX con el estado actual de todos los productos, sus niveles de stock y alertas de bajo stock.</p>
        <a class="btn btn-add-large" href="<?php echo BASE_URL; ?>index.php?controller=admin&action=exportCurrentInventoryCsv">Descargar XLSX</a>
    </div>

    <div class="section-card">
        <h2>Movimientos / Kardex</h2>
        <p class="help-text">Aplica filtros por rango de fechas, producto y tipo de movimiento antes de exportar a XLSX.</p>
        <form method="get" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=exportMovementsCsv">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="exportMovementsCsv">
            <div class="field-row">
                <div>
                    <label for="start_date_mov">Fecha desde</label>
                    <input type="date" id="start_date_mov" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                </div>
                <div>
                    <label for="end_date_mov">Fecha hasta</label>
                    <input type="date" id="end_date_mov" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                </div>
                <div>
                    <label for="product_id_mov">Producto</label>
                    <select id="product_id_mov" name="product_id">
                        <option value="">Todos los productos</option>
                        <?php foreach ($productos as $prod): ?>
                            <option value="<?php echo $prod->id; ?>" <?php echo (isset($_GET['product_id']) && $_GET['product_id'] == $prod->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($prod->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="movement_type">Tipo de movimiento</label>
                    <select id="movement_type" name="movement_type">
                        <option value="">Todos</option>
                        <option value="ingreso" <?php echo (isset($_GET['movement_type']) && $_GET['movement_type'] === 'ingreso') ? 'selected' : ''; ?>>Ingreso</option>
                        <option value="salida" <?php echo (isset($_GET['movement_type']) && $_GET['movement_type'] === 'salida') ? 'selected' : ''; ?>>Salida</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-add-large">Exportar XLSX</button>
                <button type="reset" onclick="window.location.href='<?php echo htmlspecialchars($reportUrlBase); ?>'" class="btn btn-add-large">Limpiar filtros</button>
            </div>
        </form>
    </div>

    <div class="section-card">
        <h2>Ventas</h2>
        <p class="help-text">Filtra las ventas por rango de fechas para obtener un informe XLSX coherente con la trazabilidad del inventario.</p>
        <form method="get" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=exportSalesCsv">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="exportSalesCsv">
            <div class="field-row">
                <div>
                    <label for="start_date_sales">Fecha desde</label>
                    <input type="date" id="start_date_sales" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                </div>
                <div>
                    <label for="end_date_sales">Fecha hasta</label>
                    <input type="date" id="end_date_sales" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-add-large">Exportar XLSX</button>
                <button type="reset" onclick="window.location.href='<?php echo htmlspecialchars($reportUrlBase); ?>'" class="btn btn-add-large">Limpiar filtros</button>
            </div>
        </form>
    </div>

    <div class="section-card">
        <h2>Bitácora de Auditoría (Trazabilidad)</h2>
        <p class="help-text">Registro de operaciones críticas para sustento técnico: ventas, compras, ajustes y órdenes de trabajo.</p>

        <?php
        // reuse labels mapping from supervisor panel for consistency
        $auditActionLabels = [
            'create' => 'Crear', 'update' => 'Actualizar', 'delete' => 'Eliminar', 'adjust' => 'Ajuste',
            'login' => 'Inicio de sesión', 'logout' => 'Cierre de sesión', 'add' => 'Agregar', 'remove' => 'Eliminar'
        ];
        $auditEntityLabels = [
            'product' => 'Producto', 'products' => 'Productos', 'sale' => 'Venta', 'sales' => 'Ventas',
            'purchase' => 'Compra', 'purchases' => 'Compras', 'user' => 'Usuario', 'users' => 'Usuarios',
            'inventory_movement' => 'Movimiento de inventario', 'work_order' => 'Orden de trabajo', 'auth' => 'Autenticación'
        ];
        ?>

        <div class="panel-card audit-card">
            <header>
                <div class="audit-title">
                    <?php if (file_exists(__DIR__ . '/../../../assets/img/LogoPecosol.png')): ?>
                        <img src="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" alt="Logo Pecosol">
                    <?php endif; ?>
                    <h3>Auditoría (detallada)</h3>
                </div>
                <div class="audit-toolbar">
                    <div class="controls">
                        <form id="auditFiltersForm" method="get" action="index.php" style="display:flex;gap:8px;align-items:center;">
                            <input type="hidden" name="controller" value="dashboard">
                            <input type="hidden" name="action" value="auditPartial">
                            <input id="auditUserFilter" name="audit_user" class="form-control audit-search" value="<?php echo htmlspecialchars($_GET['audit_user'] ?? ''); ?>" placeholder="Usuario">
                            <select id="auditActionFilter" name="audit_action" class="form-control">
                                <option value="">Todas</option>
                                <option value="create" <?php if(($_GET['audit_action'] ?? '')==='create') echo 'selected'; ?>>Crear</option>
                                <option value="update" <?php if(($_GET['audit_action'] ?? '')==='update') echo 'selected'; ?>>Actualizar</option>
                                <option value="delete" <?php if(($_GET['audit_action'] ?? '')==='delete') echo 'selected'; ?>>Eliminar</option>
                                <option value="adjust" <?php if(($_GET['audit_action'] ?? '')==='adjust') echo 'selected'; ?>>Ajuste</option>
                                <option value="login" <?php if(($_GET['audit_action'] ?? '')==='login') echo 'selected'; ?>>Inicio de sesión</option>
                                <option value="logout" <?php if(($_GET['audit_action'] ?? '')==='logout') echo 'selected'; ?>>Cierre de sesión</option>
                            </select>
                            <input id="auditFrom" name="audit_from" type="date" class="form-control" value="<?php echo htmlspecialchars($_GET['audit_from'] ?? ''); ?>">
                            <input id="auditTo" name="audit_to" type="date" class="form-control" value="<?php echo htmlspecialchars($_GET['audit_to'] ?? ''); ?>">
                            <select name="audit_per_page" class="form-control audit-perpage">
                                <?php $per = (int)($_GET['audit_per_page'] ?? 25); $opts=[10,25,50,100]; foreach($opts as $o): ?>
                                    <option value="<?php echo $o; ?>" <?php if($per===$o) echo 'selected'; ?>>Mostrar <?php echo $o; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <button type="submit" class="btn btn-primary">Aplicar</button>
                                <button id="clearAuditFilters" type="button" class="btn btn-ghost">Limpiar</button>
                            </div>
                        </form>
                    </div>
                    <div class="export-group">
                        <a id="exportAuditXlsx" href="<?php echo 'index.php?controller=dashboard&action=exportAuditXlsx'; ?>" class="btn btn-secondary">Exportar XLSX</a>
                        <a id="exportAuditPdf" href="<?php echo 'index.php?controller=dashboard&action=exportAuditPdf'; ?>" class="btn btn-secondary">Exportar PDF</a>
                    </div>
                </div>
            </header>
            <div class="panel-body">
                <div class="audit-table-wrap">
                    <table id="auditTable" class="audit-table table-card">
                        <thead>
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>ID</th>
                                <th>Detalle</th>
                                <th style="text-align:right;">Nivel</th>
                            </tr>
                        </thead>
                        <tbody id="auditTableBody">
                            <!-- loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div id="auditPaginationContainer" class="audit-pagination" style="margin-top:12px;"></div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal and AJAX behaviour copied from supervisor panel -->
    <div id="auditModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Detalle de auditoría</h3>
                <button id="modalClose" class="modal-close" aria-label="Cerrar">✕</button>
            </div>
            <div id="modalMeta" class="modal-meta"></div>
            <div class="modal-body">
                <pre id="modalBody"></pre>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const form = document.getElementById('auditFiltersForm');
            const tbody = document.getElementById('auditTableBody');
            const paginationContainer = document.getElementById('auditPaginationContainer');
            const wrap = document.querySelector('.audit-table-wrap');
            const exportXlsxBtn = document.getElementById('exportAuditXlsx');
            const exportPdfBtn = document.getElementById('exportAuditPdf');

            function serializeForm(f){
                const params = new URLSearchParams(new FormData(f));
                params.set('controller','dashboard');
                params.set('action','auditPartial');
                return params.toString();
            }

            function updateExportLinks(page) {
                const params = new URLSearchParams(new FormData(form));
                params.set('controller', 'dashboard');
                params.delete('action');
                if (page) {
                    params.set('audit_page', page);
                }
                const perPage = document.querySelector('select[name="audit_per_page"]')?.value || '';
                if (perPage) {
                    params.set('audit_per_page', perPage);
                }
                const baseUrl = 'index.php?controller=dashboard&action=';
                if (exportXlsxBtn) {
                    exportXlsxBtn.href = baseUrl + 'exportAuditXlsx&' + params.toString();
                }
                if (exportPdfBtn) {
                    exportPdfBtn.href = baseUrl + 'exportAuditPdf&' + params.toString();
                }
            }

            function setLoading(on){ if(on) wrap.classList.add('loading'); else wrap.classList.remove('loading'); }

            async function fetchPage(page){
                setLoading(true);
                const params = new URLSearchParams(serializeForm(form));
                params.set('audit_page', page);
                try{
                    const res = await fetch('index.php?' + params.toString(), { credentials:'same-origin' });
                    const json = await res.json();
                    tbody.innerHTML = json.rows;
                    paginationContainer.innerHTML = json.pagination;
                    attachPaginationEvents();
                    attachModalButtons();
                    updateExportLinks(page);
                } catch (err){ console.error(err); }
                setLoading(false);
            }

            function attachPaginationEvents(){
                document.querySelectorAll('.audit-page-link').forEach(a=>{ a.addEventListener('click', function(e){ e.preventDefault(); const p = this.dataset.page; fetchPage(p); }); });
            }

            function attachModalButtons(){
                document.querySelectorAll('.btn-modal').forEach(btn=>{
                    btn.addEventListener('click', function(e){ e.preventDefault(); const details = this.dataset.details || ''; const user = this.dataset.user || ''; const action = this.dataset.action || ''; const created = this.dataset.created || ''; const entity = this.dataset.entity || ''; const entityid = this.dataset.entityid || ''; const meta = `<strong>Usuario:</strong> ${user} &nbsp; <strong>Acción:</strong> ${action} &nbsp; <strong>Fecha:</strong> ${created} &nbsp; <strong>Entidad:</strong> ${entity} #${entityid}`; const modal = document.getElementById('auditModal'); document.getElementById('modalMeta').innerHTML = meta; document.getElementById('modalBody').textContent = details; modal.style.display = 'flex'; });
                });
                const closeBtn = document.getElementById('modalClose'); if (closeBtn) closeBtn.addEventListener('click', function(){ document.getElementById('auditModal').style.display = 'none'; });
                window.addEventListener('click', function(e){ const modal = document.getElementById('auditModal'); if (e.target===modal) modal.style.display='none'; });
            }

            if (form) {
                form.addEventListener('submit', function(e){ e.preventDefault(); fetchPage(1); });
                const perPageSelect = document.querySelector('.audit-perpage');
                const actionSelect = document.getElementById('auditActionFilter');
                const fromInput = document.getElementById('auditFrom');
                const toInput = document.getElementById('auditTo');
                const userInput = document.getElementById('auditUserFilter');
                if (perPageSelect) perPageSelect.addEventListener('change', function(){ fetchPage(1); });
                if (actionSelect) actionSelect.addEventListener('change', function(){ fetchPage(1); });
                if (fromInput) fromInput.addEventListener('change', function(){ fetchPage(1); });
                if (toInput) toInput.addEventListener('change', function(){ fetchPage(1); });
                if (userInput) userInput.addEventListener('keydown', function(e){ if(e.key === 'Enter') { e.preventDefault(); fetchPage(1); } });

                const clearBtn = document.getElementById('clearAuditFilters');
                if (clearBtn) clearBtn.addEventListener('click', function(e){ e.preventDefault(); document.getElementById('auditUserFilter').value = ''; document.getElementById('auditActionFilter').selectedIndex = 0; document.getElementById('auditFrom').value = ''; document.getElementById('auditTo').value = ''; const per = document.querySelector('select[name="audit_per_page"]'); if(per) per.value = '25'; fetchPage(1); try { history.replaceState(null, '', 'index.php?controller=dashboard&action=supervisorReports'); } catch(e){} });

                // initial load
                fetchPage(1);
                updateExportLinks(1);
            }
        })();
    </script>
    </body>
    </html>
