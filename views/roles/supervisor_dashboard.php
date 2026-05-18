<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auditActionLabels = [
    'create' => 'Crear',
    'update' => 'Actualizar',
    'delete' => 'Eliminar',
    'adjust' => 'Ajuste',
    'login' => 'Inicio de sesión',
    'logout' => 'Cierre de sesión',
    'add' => 'Agregar',
    'remove' => 'Eliminar',
    'send' => 'Enviar',
    'receive' => 'Recibir',
    'approve' => 'Aprobar',
    'reject' => 'Rechazar',
];
// map internal entity names to Spanish labels
$auditEntityLabels = [
  'product' => 'Producto',
  'products' => 'Productos',
  'sale' => 'Venta',
  'sales' => 'Ventas',
  'purchase' => 'Compra',
  'purchases' => 'Compras',
  'user' => 'Usuario',
  'users' => 'Usuarios',
  'inventory_movement' => 'Movimiento de inventario',
  'work_order' => 'Orden de trabajo',
  'auth' => 'Autenticación',
  'audit_log' => 'Registro de auditoría'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Supervisor | Pecosol</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css" />
  <script src="<?php echo BASE_URL; ?>assets/js/chart.umd.js"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: radial-gradient(circle at top, rgba(0, 255, 240, 0.12), transparent 34%),
                  linear-gradient(180deg, #07111e 0%, #03080f 100%);
      color: #e8f4ff;
    }
    .page-shell {
      max-width: 1220px;
      margin: 0 auto;
      padding: 28px 24px 40px;
    }
    .header-panel {
      padding: 30px 24px;
      border-radius: 22px;
      background: rgba(9, 18, 41, 0.95);
      border: 1px solid rgba(125, 231, 255, 0.14);
      box-shadow: 0 20px 40px rgba(0,0,0,0.18);
      margin-bottom: 28px;
    }
    .header-panel h1 {
      margin: 0;
      font-size: 2.6rem;
      color: #7ef4ff;
    }
    .header-panel p {
      margin: 14px 0 0;
      color: #b4d8ff;
      line-height: 1.75;
      max-width: 860px;
    }
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .kpi-card {
      background: rgba(10, 20, 42, 0.92);
      border: 1px solid rgba(126, 247, 255, 0.16);
      border-radius: 22px;
      padding: 24px;
      box-shadow: 0 18px 32px rgba(0,0,0,0.16);
    }
    .kpi-card h3 {
      margin: 0;
      font-size: 0.9rem;
      color: #96c9ff;
      letter-spacing: 0.12em;
      text-transform: uppercase;
    }
    .kpi-card strong {
      display: block;
      margin: 16px 0 8px;
      font-size: 2.8rem;
      color: #ffffff;
      line-height: 1;
    }
    .kpi-card small {
      color: #c5d9ff;
      line-height: 1.6;
    }
    .action-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-top: 28px;
    }
    .action-card {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 22px;
      border-radius: 22px;
      background: rgba(9, 18, 41, 0.96);
      border: 1px solid rgba(126, 247, 255, 0.14);
      color: #e8f4ff;
      transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
      text-decoration: none;
    }
    .action-card:hover {
      transform: translateY(-2px);
      border-color: rgba(126, 247, 255, 0.26);
      box-shadow: 0 24px 36px rgba(0,0,0,0.22);
    }
    .action-card h4 {
      margin: 0 0 10px;
      font-size: 1.05rem;
      color: #80f2ff;
    }
    .action-card p {
      margin: 0;
      color: #cbe0ff;
      line-height: 1.7;
    }
    .grid-two {
      display: grid;
      grid-template-columns: 1.75fr 1fr;
      gap: 18px;
      margin-top: 28px;
    }
    .panel-card {
      background: rgba(10, 20, 42, 0.96);
      border: 1px solid rgba(126, 247, 255, 0.14);
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 18px 34px rgba(0,0,0,0.16);
    }
    .panel-card header {
      padding: 18px 22px;
      background: rgba(126, 247, 255, 0.08);
      color: #d7f7ff;
      font-weight: 700;
    }
    .panel-body {
      padding: 20px 24px;
    }
    .panel-body ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }
    .panel-body li {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      color: #cfeeff;
    }
    .panel-body li:last-child { border-bottom: none; }
    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(126, 247, 255, 0.16);
      color: #efffff;
      font-weight: 700;
      font-size: 0.9rem;
    }
    .sales-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
    }
    .sales-table th,
    .sales-table td {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      color: #dbeeff;
      text-align: left;
    }
    .sales-table thead { background: rgba(126, 247, 255, 0.06); }
    .sales-table tbody tr:hover { background: rgba(126, 247, 255, 0.06); }
    .sales-table td:last-child { text-align: right; }
    .note {
      margin-top: 16px;
      color: #a8c8e9;
      font-size: 0.95rem;
      line-height: 1.6;
    }
    @media (max-width: 980px) {
      .grid-two { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../employee/partials/header.php'; ?>
  <main class="page-shell">
    <section class="header-panel">
      <h1>Panel Supervisor</h1>
      <p>Visión unificada para ventas, inventario, compras, productos y control operativo con alertas en tiempo real.</p>
    </section>

    <section class="kpi-grid">
      <article class="kpi-card">
        <h3>Ventas hoy</h3>
        <strong>S/. <?php echo number_format($totalSalesToday, 2, '.', ','); ?></strong>
        <small>Ventas registradas en el día.</small>
      </article>
      <article class="kpi-card">
        <h3>Ventas últimos 7 días</h3>
        <strong>S/. <?php echo number_format($totalSalesWeek, 2, '.', ','); ?></strong>
        <small>Rendimiento comercial semanal.</small>
      </article>
      <article class="kpi-card">
        <h3>Compras registradas</h3>
        <strong><?php echo number_format($totalPurchases); ?></strong>
        <small>Compras totales en el sistema.</small>
      </article>
      <article class="kpi-card">
        <h3>Órdenes activas</h3>
        <strong><?php echo number_format($activeWorkOrders); ?></strong>
        <small>Órdenes pendientes o en proceso.</small>
      </article>
      <article class="kpi-card">
        <h3>Productos totales</h3>
        <strong><?php echo number_format($totalProducts); ?></strong>
        <small>Artículos registrados en catálogo.</small>
      </article>
      <article class="kpi-card">
        <h3>Alertas de stock</h3>
        <strong><?php echo number_format($lowStockCount); ?></strong>
        <small>Productos con stock crítico.</small>
      </article>
    </section>

    <!-- Acciones rápidas eliminadas (ya presentes en el header) -->

    <!-- Main content: left recent sales and right supporting panels -->
    <section class="grid-two">
      <article class="panel-card">
        <header>Ventas recientes</header>
        <div class="panel-body">
          <?php if (!empty($recentSales)): ?>
            <table class="sales-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Producto</th>
                  <th>Monto</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentSales as $sale): ?>
                  <tr>
                    <td><?php echo (int)$sale->id; ?></td>
                    <td><?php echo htmlspecialchars($sale->client_name); ?></td>
                    <td><?php echo htmlspecialchars($sale->product_name); ?></td>
                    <td>S/. <?php echo number_format((float)$sale->total_price, 2, '.', ','); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="note">No hay ventas recientes registradas.</p>
          <?php endif; ?>
        </div>
      </article>
      <div>
        <article class="panel-card">
          <header>Productos en alerta</header>
          <div class="panel-body">
            <ul>
              <?php if (!empty($lowStockAlerts)): ?>
                <?php foreach ($lowStockAlerts as $product): ?>
                  <li>
                    <span><?php echo htmlspecialchars($product->name); ?></span>
                    <span class="badge">Stock: <?php echo (int)$product->stock; ?></span>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li>No hay productos con stock crítico.</li>
              <?php endif; ?>
            </ul>
          </div>
        </article>

        <article class="panel-card" style="margin-top:18px;">
          <header>Ventas semanales</header>
          <div class="panel-body" style="min-height: 220px;">
            <canvas id="salesTrendChart" width="100%" height="220"></canvas>
          </div>
        </article>
      </div>
    </section>

    <!-- Auditoría centralizada: panel amplio para supervisores -->
    <article class="panel-card audit-card" style="margin-top:22px;">
      <header>
        <div class="audit-title">
          <?php if (file_exists(__DIR__ . '/../../assets/img/LogoPecosol.png')): ?>
            <img src="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png" alt="Logo Pecosol">
          <?php endif; ?>
          <h3>Auditoría (detallada)</h3>
        </div>
        <div class="audit-toolbar">
          <div class="controls">
            <form id="auditFiltersForm" method="get" action="index.php" style="display:flex;gap:8px;align-items:center;">
              <input type="hidden" name="controller" value="dashboard">
              <input type="hidden" name="action" value="supervisorHome">
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
            <a href="<?php echo 'index.php?controller=dashboard&action=exportAuditXlsx' . (!empty($_GET['audit_user']) ? '&audit_user='.urlencode($_GET['audit_user']):'') . (!empty($_GET['audit_action']) ? '&audit_action='.urlencode($_GET['audit_action']):'') . (!empty($_GET['audit_from']) ? '&audit_from='.urlencode($_GET['audit_from']):'') . (!empty($_GET['audit_to']) ? '&audit_to='.urlencode($_GET['audit_to']):'') . (!empty($_GET['audit_per_page']) ? '&audit_per_page='.urlencode($_GET['audit_per_page']):''); ?>" class="btn btn-secondary">Exportar XLSX</a>
            <a href="<?php echo 'index.php?controller=dashboard&action=exportAuditPdf' . (!empty($_GET['audit_user']) ? '&audit_user='.urlencode($_GET['audit_user']):'') . (!empty($_GET['audit_action']) ? '&audit_action='.urlencode($_GET['audit_action']):'') . (!empty($_GET['audit_from']) ? '&audit_from='.urlencode($_GET['audit_from']):'') . (!empty($_GET['audit_to']) ? '&audit_to='.urlencode($_GET['audit_to']):'') . (!empty($_GET['audit_per_page']) ? '&audit_per_page='.urlencode($_GET['audit_per_page']):''); ?>" class="btn btn-secondary">Exportar PDF</a>
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
                <?php if (!empty($recentAudits)): ?>
                  <?php foreach ($recentAudits as $audit): ?>
                      <?php
                        $rawAction = $audit->action ?? '';
                        $actionLabel = $auditActionLabels[$rawAction] ?? ucfirst(str_replace('_', ' ', $rawAction));
                        $userName = $audit->user_name ?? ('#' . ($audit->user_id ?? ''));
                        $entity = $audit->entity ?? '';
                        $entityId = $audit->entity_id ?? '';
                        $details = $audit->details ?? '';
                        $created = date('d/m H:i', strtotime($audit->created_at));
                        $severity = in_array($rawAction, ['delete','adjust']) ? 'critical' : 'info';
                        $detailsSafe = htmlspecialchars($details);
                      ?>
                      <tr class="audit-row" data-user="<?php echo htmlspecialchars(strtolower($userName)); ?>" data-action="<?php echo htmlspecialchars($rawAction); ?>" data-created="<?php echo htmlspecialchars(date('Y-m-d', strtotime($audit->created_at))); ?>">
                        <td class="td-date"><?php echo $created; ?></td>
                        <td class="td-user"><?php echo htmlspecialchars($userName); ?></td>
                        <td class="td-action"><?php echo htmlspecialchars($actionLabel); ?></td>
                        <?php $entityLabel = $auditEntityLabels[strtolower($entity)] ?? ucfirst($entity); ?>
                        <td class="td-entity"><?php echo htmlspecialchars($entityLabel); ?></td>
                        <td class="td-id"><?php echo htmlspecialchars($entityId); ?></td>
                        <td class="td-summary"><?php echo strlen($details) > 80 ? substr($detailsSafe,0,80) . '...' : $detailsSafe; ?></td>
                        <td class="td-severity">
                          <?php if ($severity === 'critical'): ?>
                            <span class="severity-critical">ELIMINACIÓN</span>
                          <?php else: ?>
                            <span class="severity-info">INFO</span>
                          <?php endif; ?>
                          <button class="btn btn-sm btn-modal" data-details="<?php echo $detailsSafe; ?>" data-user="<?php echo htmlspecialchars($userName); ?>" data-action="<?php echo htmlspecialchars($actionLabel); ?>" data-created="<?php echo htmlspecialchars($audit->created_at); ?>" data-entity="<?php echo htmlspecialchars($entity); ?>" data-entityid="<?php echo htmlspecialchars($entityId); ?>">Ver</button>
                        </td>
                      </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="7" style="padding:12px;color:#a0a0a0;">No hay eventos recientes en la bitácora.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
            <!-- Modal for audit details -->
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
          <div id="auditPaginationContainer"></div>
          </div>
        </div>
      </article>
    </section>

    <!-- Right column panels moved into the grid-two above -->
  </main>

  <script>
    const ctx = document.getElementById('salesTrendChart');
    if (ctx) {
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?php echo json_encode($salesTrendLabels); ?>,
          datasets: [{
            label: 'Ventas',
            data: <?php echo json_encode($salesTrendData); ?>,
            borderColor: '#50e2ff',
            backgroundColor: 'rgba(80,226,255,0.18)',
            tension: 0.35,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#7ef4ff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: { ticks: { color: '#cfdfff' }, grid: { display: false } },
            y: { ticks: { color: '#cfdfff' }, grid: { color: 'rgba(126, 247, 255, 0.12)' } }
          },
          plugins: { legend: { display: false } }
        }
      });
    }
  </script>
    <script>
      // Modal details and interactions for audit rows
      (function(){
        const modal = document.getElementById('auditModal');
        const modalBody = document.getElementById('modalBody');
        const modalMeta = document.getElementById('modalMeta');
        const modalTitle = document.getElementById('modalTitle');
        const closeBtn = document.getElementById('modalClose');

        function openModal(metaHtml, details){
          modalMeta.innerHTML = metaHtml;
          modalBody.textContent = details || '';
          modal.style.display = 'flex';
        }
        function closeModal(){ modal.style.display = 'none'; }

        document.querySelectorAll('.btn-modal').forEach(btn => {
          btn.addEventListener('click', function(e){
            e.preventDefault();
            const details = this.dataset.details || '';
            const user = this.dataset.user || '';
            const action = this.dataset.action || '';
            const created = this.dataset.created || '';
            const entity = this.dataset.entity || '';
            const entityid = this.dataset.entityid || '';
            const meta = `<strong>Usuario:</strong> ${user} &nbsp; <strong>Acción:</strong> ${action} &nbsp; <strong>Fecha:</strong> ${created} &nbsp; <strong>Entidad:</strong> ${entity} #${entityid}`;
            openModal(meta, details);
          });
        });
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        window.addEventListener('click', function(e){ if (e.target===modal) closeModal(); });
      })();
    </script>
    <script>
      // AJAX pagination and filters for audit table
      (function(){
        const form = document.getElementById('auditFiltersForm');
        const tbody = document.getElementById('auditTableBody');
        const paginationContainer = document.getElementById('auditPaginationContainer');
        const wrap = document.querySelector('.audit-table-wrap');

        function serializeForm(f){
          const params = new URLSearchParams(new FormData(f));
          params.set('controller','dashboard');
          params.set('action','auditPartial');
          return params.toString();
        }

        function setLoading(on){
          if (on) wrap.classList.add('loading'); else wrap.classList.remove('loading');
        }

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
          } catch (err){ console.error(err); }
          setLoading(false);
        }

        function attachPaginationEvents(){
          document.querySelectorAll('.audit-page-link').forEach(a=>{
            a.addEventListener('click', function(e){ e.preventDefault(); const p = this.dataset.page; fetchPage(p); });
          });
        }

        function attachModalButtons(){
          document.querySelectorAll('.btn-modal').forEach(btn=>{
            btn.addEventListener('click', function(e){ e.preventDefault(); const details = this.dataset.details || ''; const user = this.dataset.user || ''; const action = this.dataset.action || ''; const created = this.dataset.created || ''; const entity = this.dataset.entity || ''; const entityid = this.dataset.entityid || ''; const meta = `<strong>Usuario:</strong> ${user} &nbsp; <strong>Acción:</strong> ${action} &nbsp; <strong>Fecha:</strong> ${created} &nbsp; <strong>Entidad:</strong> ${entity} #${entityid}`; const modal = document.getElementById('auditModal'); document.getElementById('modalMeta').innerHTML = meta; document.getElementById('modalBody').textContent = details; modal.style.display = 'flex'; });
          });
        }

        form.addEventListener('submit', function(e){ e.preventDefault(); fetchPage(1); });
        // live-change behaviors: when user changes per-page or filters, refresh table
        const perPageSelect = document.querySelector('.audit-perpage');
        const actionSelect = document.getElementById('auditActionFilter');
        const fromInput = document.getElementById('auditFrom');
        const toInput = document.getElementById('auditTo');
        const userInput = document.getElementById('auditUserFilter');
        if (perPageSelect) perPageSelect.addEventListener('change', function(){ fetchPage(1); });
        if (actionSelect) actionSelect.addEventListener('change', function(){ fetchPage(1); });
        if (fromInput) fromInput.addEventListener('change', function(){ fetchPage(1); });
        if (toInput) toInput.addEventListener('change', function(){ fetchPage(1); });
        // optional: trigger on Enter in user input
        if (userInput) userInput.addEventListener('keydown', function(e){ if(e.key === 'Enter') { e.preventDefault(); fetchPage(1); } });

        // Clear button behavior: reset form fields and reload table
        const clearBtn = document.getElementById('clearAuditFilters');
        if (clearBtn) {
          clearBtn.addEventListener('click', function(e){
            e.preventDefault();
            // reset known inputs
            document.getElementById('auditUserFilter').value = '';
            document.getElementById('auditActionFilter').selectedIndex = 0;
            document.getElementById('auditFrom').value = '';
            document.getElementById('auditTo').value = '';
            const per = document.querySelector('select[name="audit_per_page"]'); if(per) per.value = '25';
            fetchPage(1);
            // remove query params from URL to reflect cleared filters
            try { history.replaceState(null, '', 'index.php?controller=dashboard&action=supervisorHome'); } catch(e){}
          });
        }

        // initial attach
        attachPaginationEvents();
        attachModalButtons();
      })();
    </script>
</body>
</html>
