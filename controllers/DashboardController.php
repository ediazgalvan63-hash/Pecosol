<?php
// controllers/DashboardController.php

class DashboardController {
    private $saleModel;
    private $productModel;
    private $inventoryMovementModel;
    private $purchaseModel;
    private $workOrderModel;
    private $auditLogModel;

    private function requireRole(array $allowedRoles): void {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, $allowedRoles, true)) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function __construct() {
        // 1) Asegurar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2) Si no hay usuario logueado, redirigir a login
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 3) Instanciar modelos que usaremos en el dashboard
        require_once __DIR__ . '/../models/Sale.php';
        require_once __DIR__ . '/../models/Product.php';
        require_once __DIR__ . '/../models/InventoryMovement.php';
        require_once __DIR__ . '/../models/Purchase.php';
        require_once __DIR__ . '/../models/WorkOrder.php';
        require_once __DIR__ . '/../models/AuditLog.php';
        $this->saleModel    = new Sale();
        $this->productModel = new Product();
        $this->inventoryMovementModel = new InventoryMovement();
        $this->purchaseModel = new Purchase();
        $this->workOrderModel = new WorkOrder();
        $this->auditLogModel = new AuditLog();
    }

    /**
     * home()
     * - Punto único de entrada después del login.
     * - Redirige al panel según rol.
     */
    public function home() {
        $role = $_SESSION['role'] ?? '';
        switch ($role) {
            case 'admin':
                header('Location: index.php?controller=dashboard&action=adminHome');
                exit;
            case 'gerencia':
                header('Location: index.php?controller=dashboard&action=managementHome');
                exit;
            case 'comercial':
                header('Location: index.php?controller=dashboard&action=commercialHome');
                exit;
            case 'logistica':
                header('Location: index.php?controller=dashboard&action=logisticsHome');
                exit;
            case 'finanzas':
                header('Location: index.php?controller=dashboard&action=financeHome');
                exit;
            case 'estrategico':
                header('Location: index.php?controller=dashboard&action=strategyHome');
                exit;
            case 'supervisor':
                header('Location: index.php?controller=dashboard&action=supervisorHome');
                exit;
            case 'employee':
            default:
                header('Location: index.php?controller=dashboard&action=employeeHome');
                exit;
        }
    }

    /**
     * supervisorHome()
     * - Panel para supervisores con visión de compras, órdenes, ventas y auditoría.
     */
    public function supervisorHome() {
        $this->requireRole(['supervisor']);

        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('-6 days', strtotime($today)));

        $totalSalesToday = $this->saleModel->getTotalSalesByDate($today, $today);
        $salesTrendLabels = [];
        $salesTrendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $salesTrendLabels[] = date('d/m', strtotime($date));
            $salesTrendData[] = $this->saleModel->getTotalSalesByDate($date, $date);
        }
        $totalSalesWeek = array_sum($salesTrendData);

        $totalPurchases = $this->purchaseModel->countPurchases();
        $activeWorkOrders = $this->workOrderModel->countActive();
        $totalStock = $this->productModel->getTotalStock();
        $totalProducts = $this->productModel->countProducts();
        $lowStockCount = $this->productModel->countLowStockProducts();
        $lowStockAlerts = array_slice($this->productModel->getLowStockProducts(), 0, 5);
        $recentSales = $this->saleModel->getLastSales(6);
        // server-side filters & pagination for audit log
        $auditPage = max(1, (int)($_GET['audit_page'] ?? 1));
        $auditPerPage = max(10, min(100, (int)($_GET['audit_per_page'] ?? 20)));
        $auditOffset = ($auditPage - 1) * $auditPerPage;

        $auditFilters = [
            'user' => trim($_GET['audit_user'] ?? ''),
            'action' => trim($_GET['audit_action'] ?? ''),
            'from' => trim($_GET['audit_from'] ?? ''),
            'to' => trim($_GET['audit_to'] ?? ''),
        ];

        $auditResult = $this->auditLogModel->getFiltered($auditFilters, $auditPerPage, $auditOffset);
        $recentAudits = $auditResult['rows'];
        $auditTotal = $auditResult['total'];
        $auditCurrentPage = $auditPage;
        $auditPerPage = $auditPerPage;

        require __DIR__ . '/../views/roles/supervisor_dashboard.php';
    }

    /**
     * adminHome()
     * - Solo accesible si rol == 'admin'
     * - Obtiene datos para mostrar en el dashboard de administrador:
     *   - Total de ventas del día
     *   - Total de ventas del mes
     *   - Total de productos en stock
     *   - Lista de últimas 5 ventas
     *   - Arreglo con ventas de los últimos 7 días para la gráfica
     */
    public function adminHome() {
        // Verificar que el usuario sea admin
        $this->requireRole(['admin']);

        // 1) Calcular fecha de hoy y primer día del mes
        $hoy = date('Y-m-d');
        $primerDiaMes = date('Y-m-01');

        // 2) Obtener suma de total_price para ventas de hoy
        $ventasHoy = $this->saleModel->getTotalSalesByDate($hoy, $hoy);

        // 3) Obtener suma de total_price para ventas del mes
        $ventasMes = $this->saleModel->getTotalSalesByDate($primerDiaMes, $hoy);

        // 4) Obtener total de stock de todos los productos
        $totalStock = $this->productModel->getTotalStock();
        $totalProductos = $this->productModel->countProducts();
        $productosBajoStock = $this->productModel->countLowStockProducts();

        // 5) Traer últimas 5 ventas recientes (con JOIN para nombre de usuario y producto)
        $ultimasVentas = $this->saleModel->getLastSales(5);
        $ultimosMovimientos = $this->inventoryMovementModel->getLastMovements(6);
        $resumenMovimientos = $this->inventoryMovementModel->getSummaryInOut();
        $totalEntradas = $resumenMovimientos['entradas'];
        $totalSalidas = $resumenMovimientos['salidas'];
        $movimientosHoy = $this->inventoryMovementModel->countMovementsByDateRange($hoy, $hoy);

        // 6) Generar arreglo con ventas de cada uno de los últimos 7 días
        $datosSemana = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-{$i} days"));
            $totalVentasDia = $this->saleModel->getTotalSalesByDate($dia, $dia);
            $datosSemana[] = [
                'etiqueta' => date('d/m', strtotime($dia)),
                'valor'    => $totalVentasDia
            ];
        }

        // 7) Cargar la vista pasando todas las variables
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    /**
     * employeeHome()
     * - Solo accesible si rol == 'employee'
     * - Obtiene datos para mostrar en el dashboard de empleado:
     *   - Total de ventas del día hechas por este empleado
     *   - Lista de sus propias últimas 5 ventas
     */
    public function employeeHome() {
        // Comercial también usa este panel como base (ventas / cartera)
        $this->requireRole(['employee', 'comercial']);

        $userId = $_SESSION['user_id'];
        $hoy    = date('Y-m-d');

        // Total de ventas hoy filtradas por user_id
        $ventasHoyEmpleado = $this->saleModel->getTotalSalesByDateAndUser($hoy, $hoy, $userId);

        // Últimas 5 ventas de este empleado
        $ultimasVentasEmpleado = $this->saleModel->getLastSalesByUser($userId, 5);

        // Reutilizamos dashboard de empleado para comercial por ahora
        require_once __DIR__ . '/../views/employee/dashboard.php';
    }

    public function managementHome() {
        $this->requireRole(['gerencia']);

        $today = date('Y-m-d');

        $totalProducts = $this->productModel->countProducts();
        $totalStock = $this->productModel->getTotalStock();
        $lowStockCount = $this->productModel->countLowStockProducts();
        $lowStockAlerts = array_slice($this->productModel->getLowStockProducts(), 0, 5);

        $salesTrendLabels = [];
        $salesTrendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $salesTrendLabels[] = date('d M', strtotime($date));
            $salesTrendData[] = $this->saleModel->getTotalSalesByDate($date, $date);
        }
        $totalSalesWeek = array_sum($salesTrendData);
        $averageDailySales = $totalSalesWeek / 7;

        $recentSales = $this->saleModel->getLastSales(6);
        $recentAudits = $this->auditLogModel->getRecent(6);

        require __DIR__ . '/../views/roles/management_dashboard.php';
    }

    public function managementReports() {
        $this->requireRole(['gerencia']);
        // Gerencia solo ve auditoría y reportes ejecutivos, sin detalles operativos
        $productos = $this->productModel->getAll();
        $auditorias = $this->auditLogModel->getRecent(80);
        $dashboardMode = true;
        $reportsAction = 'managementReports';
        $restrictedView = true;  // Limita opciones de exportación
        require __DIR__ . '/../views/admin/reportes/index.php';
    }

    public function commercialHome() {
        $this->requireRole(['comercial']);

        $userId = $_SESSION['user_id'];
        $today = date('Y-m-d');
        $startViewDate = date('Y-m-d', strtotime('-6 days', strtotime($today)));

        $totalSalesToday = $this->saleModel->getTotalSalesByDateAndUser($today, $today, $userId);
        $salesCountToday = $this->saleModel->countSalesByDateAndUser($today, $today, $userId);
        $salesTrend = $this->saleModel->getSalesTrendByUser($userId, 7);
        $salesTrendLabels = array_map(function ($date) {
            return date('d/m', strtotime($date));
        }, array_keys($salesTrend));
        $salesTrendData = array_values($salesTrend);
        $totalSalesWeek = array_sum($salesTrendData);
        $averageSaleValue = $salesCountToday > 0 ? $totalSalesToday / max($salesCountToday, 1) : 0;
        $topClients = $this->saleModel->getTopClientsByUser($userId, 4);
        $topProducts = $this->saleModel->getTopProductsByUser($userId, 4);
        $recentSales = $this->saleModel->getLastSalesByUser($userId, 6);
        $activeClients = count($topClients);

        require __DIR__ . '/../views/roles/commercial_dashboard.php';
    }

    public function logisticsHome() {
        $this->requireRole(['logistica']);

        // KPIs para logística
        $totalProducts = count($this->productModel->getAll());
        $totalMovements = $this->inventoryMovementModel->countMovements();
        $activeWorkOrders = $this->workOrderModel->countActive();
        $recentPurchases = $this->purchaseModel->getAll(5); // Últimas 5 compras

        require __DIR__ . '/../views/roles/logistics_dashboard.php';
    }

    public function financeHome() {
        $this->requireRole(['finanzas']);

        $today = date('Y-m-d');
        $startWeek = date('Y-m-d', strtotime('-6 days', strtotime($today)));

        // CxP: Total compras pendientes (asumiendo todas son CxP)
        $totalCxP = $this->purchaseModel->getTotalPurchasesByDate($startWeek, $today);
        $countPurchases = $this->purchaseModel->countPurchases();

        // CxC: Total ventas (asumiendo todas son CxC)
        $totalCxC = $this->saleModel->getTotalSalesByDate($startWeek, $today);
        $countSales = $this->saleModel->countSales();

        // Flujo de caja semanal: CxC - CxP
        $cashFlow = $totalCxC - $totalCxP;

        // Órdenes de trabajo activas
        $activeWorkOrders = $this->workOrderModel->countActive();

        // Tendencia de CxC semanal
        $cxcTrendLabels = [];
        $cxcTrendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $cxcTrendLabels[] = date('d M', strtotime($date));
            $cxcTrendData[] = $this->saleModel->getTotalSalesByDate($date, $date);
        }

        // Últimas compras y ventas
        $recentPurchases = $this->purchaseModel->getAll(5);
        $recentSales = $this->saleModel->getLastSales(5);

        require __DIR__ . '/../views/roles/finance_dashboard.php';
    }

    public function logisticsInventory() {
        $this->requireRole(['logistica','supervisor','comercial']);
        $startDate    = trim($_GET['start_date'] ?? '');
        $endDate      = trim($_GET['end_date'] ?? '');
        $productId    = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        $movementType = trim($_GET['movement_type'] ?? '');

        $productos = $this->productModel->getAll();
        $movimientos = $this->inventoryMovementModel->getFiltered(
            $startDate !== '' ? $startDate : null,
            $endDate !== '' ? $endDate : null,
            $productId > 0 ? $productId : null,
            $movementType !== '' ? $movementType : null,
            500
        );

        require __DIR__ . '/../views/admin/inventario/list_movements.php';
    }

    public function logisticsRecount() {
        $this->requireRole(['logistica','supervisor']);
        $productos = $this->productModel->getAll();
        require __DIR__ . '/../views/admin/inventario/recount.php';
    }

    /**
     * logisticsPurchases() - Compras para logística (abastecimiento)
     */
    public function logisticsPurchases() {
        $this->requireRole(['logistica','supervisor']);
        $compras = $this->purchaseModel->getAll(300);
        require __DIR__ . '/../views/admin/compras/list_purchases.php';
    }

    public function logisticsWorkOrders() {
        $this->requireRole(['logistica','comercial','supervisor']);
        $ordenes = $this->workOrderModel->getAll(300);
        require __DIR__ . '/../views/admin/ordenes/list_work_orders.php';
    }

    public function supervisorLowStockAlerts() {
        $this->requireRole(['supervisor']);
        $productosConBajoStock = $this->productModel->getLowStockProducts();
        require __DIR__ . '/../views/admin/inventario/low_stock_alerts.php';
    }

    public function financeSales() {
        $this->requireRole(['finanzas']);
        $ventas = $this->saleModel->getAllSales();
        $hoy = date('Y-m-d');
        $totalHoy = $this->saleModel->getTotalSalesByDate($hoy, $hoy);
        require __DIR__ . '/../views/admin/ventas/list_sales.php';
    }

    public function financePurchases() {
        $this->requireRole(['finanzas']);
        $compras = $this->purchaseModel->getAll(300);
        require __DIR__ . '/../views/admin/compras/list_purchases.php';
    }

    public function financeReports() {
        $this->requireRole(['finanzas']);
        $productos = $this->productModel->getAll();
        $auditorias = $this->auditLogModel->getRecent(80);
        require __DIR__ . '/../views/admin/reportes/index.php';
    }

    public function supervisorReports() {
        $this->requireRole(['supervisor']);
        $productos = $this->productModel->getAll();
        $auditorias = $this->auditLogModel->getRecent(80);
        $dashboardMode = true;
        $reportsAction = 'supervisorReports';
        require __DIR__ . '/../views/admin/reportes/index.php';
    }

    public function strategyReports() {
        $this->requireRole(['estrategico']);
        $productos = $this->productModel->getAll();
        $auditorias = $this->auditLogModel->getRecent(80);
        $dashboardMode = true;
        $reportsAction = 'strategyReports';
        require __DIR__ . '/../views/admin/reportes/index.php';
    }

    public function strategyDataMaster() {
        $this->requireRole(['estrategico']);
        $productos = $this->productModel->getAll();
        require __DIR__ . '/../views/admin/productos/list_products.php';
    }

    public function strategyHome() {
        $this->requireRole(['estrategico']);

        $today = date('Y-m-d');
        $yearStart = date('Y-01-01');
        $monthStart = date('Y-m-01');
        $weekStart = date('Y-m-d', strtotime('-6 days'));

        // Datos anuales para ROI y rotación
        $totalSalesYear = $this->saleModel->getTotalSalesByDate($yearStart, $today);
        $totalPurchasesYear = $this->purchaseModel->getTotalPurchasesByDate($yearStart, $today);
        $currentStock = $this->productModel->getTotalStock();

        // ROI: (Ingresos - Costos) / Costos
        $roi = $totalPurchasesYear > 0 ? (($totalSalesYear - $totalPurchasesYear) / $totalPurchasesYear) * 100 : 0;

        // Rotación de inventario anual: Ventas / Inventario promedio (aprox con stock actual)
        $inventoryTurnover = $currentStock > 0 ? $totalSalesYear / $currentStock : 0;

        // Comparativa con metas: Asumir meta mensual de ventas (ejemplo: 10000)
        $metaVentasMensual = 10000; // Esto podría venir de BD
        $ventasMesActual = $this->saleModel->getTotalSalesByDate($monthStart, $today);
        $porcentajeMeta = $metaVentasMensual > 0 ? ($ventasMesActual / $metaVentasMensual) * 100 : 0;

        // Análisis predictivo: Proyección mensual basada en promedio semanal
        $ventasSemana = $this->saleModel->getTotalSalesByDate($weekStart, $today);
        $proyeccionMensual = $ventasSemana * 4; // Aprox 4 semanas por mes

        // Tendencia de ventas para gráfica
        $salesTrendLabels = [];
        $salesTrendData = [];
        for ($i = 11; $i >= 0; $i--) { // Últimos 12 meses
            $month = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime($month));
            $salesTrendLabels[] = date('M Y', strtotime($month));
            $salesTrendData[] = $this->saleModel->getTotalSalesByDate($month, $monthEnd);
        }

        // KPIs adicionales
        $totalProducts = $this->productModel->countProducts();
        $lowStockCount = $this->productModel->countLowStockProducts();
        $totalStock = $this->productModel->getTotalStock();
        $lowStockAlerts = array_slice($this->productModel->getLowStockProducts(), 0, 6);

        $recentSales = $this->saleModel->getLastSales(6);
        $recentPurchases = $this->purchaseModel->getAll(6);
        $recentAudits = $this->auditLogModel->getRecent(6);

        require __DIR__ . '/../views/roles/strategy_dashboard.php';
    }

    /**
     * exportAuditCsv
     * Export filtered audit rows as CSV for supervisor/authorized roles.
     */
    public function exportAuditCsv() {
        $this->requireRole(['supervisor','admin','finanzas','gerencia']);

        $auditFilters = [
            'user' => trim($_GET['audit_user'] ?? ''),
            'action' => trim($_GET['audit_action'] ?? ''),
            'from' => trim($_GET['audit_from'] ?? ''),
            'to' => trim($_GET['audit_to'] ?? ''),
        ];

        // cap export size to avoid memory issues
        $maxExport = 5000;
        $page = isset($_GET['audit_page']) ? max(1, (int)$_GET['audit_page']) : 0;
        $perPage = max(5, min(100, (int)($_GET['audit_per_page'] ?? 25)));
        if ($page > 0) {
            $limit = $perPage;
            $offset = ($page - 1) * $perPage;
        } else {
            $limit = $maxExport;
            $offset = 0;
        }
        $result = $this->auditLogModel->getFiltered($auditFilters, $limit, $offset);
        $rows = $result['rows'];

        // Prepare mappings for nicer labels (Spanish)
        $actionLabels = [
            'create' => 'Crear', 'update' => 'Actualizar', 'delete' => 'Eliminar',
            'adjust' => 'Ajuste', 'login' => 'Inicio de sesión', 'logout' => 'Cierre de sesión',
            'add' => 'Agregar', 'remove' => 'Eliminar', 'send' => 'Enviar', 'receive' => 'Recibir',
            'approve' => 'Aprobar', 'reject' => 'Rechazar'
        ];
        $entityLabels = [
            'product' => 'Producto', 'products' => 'Productos', 'sale' => 'Venta', 'sales' => 'Ventas',
            'purchase' => 'Compra', 'purchases' => 'Compras', 'user' => 'Usuario', 'users' => 'Usuarios',
            'inventory_movement' => 'Movimiento de inventario', 'work_order' => 'Orden de trabajo',
            'auth' => 'Autenticación', 'audit_log' => 'Registro de auditoría'
        ];

        // Stream CSV with UTF-8 BOM for Excel and semicolon delimiter (common in locales using comma decimal)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_export_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row (Spanish)
        $headers = ['ID','Fecha','Usuario','Acción','Entidad','ID Entidad','Detalle'];
        fputcsv($out, $headers, ';');

        foreach ($rows as $r) {
            // format date
            $created = $r->created_at ?? '';
            if (!empty($created)) {
                try { $dt = new DateTime($created); $created = $dt->format('d/m/Y H:i'); } catch (Exception $e) { /* keep raw */ }
            }
            $user = $r->user_name ?? ('#'.($r->user_id ?? ''));
            $act = $r->action ?? '';
            $ent = $r->entity ?? '';
            $actLabel = $actionLabels[strtolower($act)] ?? ucwords(str_replace('_',' ',$act));
            $entLabel = $entityLabels[strtolower($ent)] ?? ucwords(str_replace('_',' ',$ent));

            // clean detail (remove newlines that break CSV rows)
            $detail = isset($r->details) ? preg_replace("/\r?\n/", ' ', $r->details) : '';

            $row = [
                $r->id ?? '',
                $created,
                $user,
                $actLabel,
                $entLabel,
                $r->entity_id ?? '',
                $detail
            ];
            fputcsv($out, $row, ';');
        }
        fclose($out);
        exit;
    }

    /**
     * exportAuditXlsx
     * Generate an XLSX export using PhpSpreadsheet for better Excel formatting.
     */
    public function exportAuditXlsx() {
        $this->requireRole(['supervisor','admin','finanzas','gerencia']);

        $auditFilters = [
            'user' => trim($_GET['audit_user'] ?? ''),
            'action' => trim($_GET['audit_action'] ?? ''),
            'from' => trim($_GET['audit_from'] ?? ''),
            'to' => trim($_GET['audit_to'] ?? ''),
        ];

        $maxExport = 5000;
        $page = isset($_GET['audit_page']) ? max(1, (int)$_GET['audit_page']) : 0;
        $perPage = max(5, min(100, (int)($_GET['audit_per_page'] ?? 25)));
        if ($page > 0) {
            $limit = $perPage;
            $offset = ($page - 1) * $perPage;
        } else {
            $limit = $maxExport;
            $offset = 0;
        }
        $result = $this->auditLogModel->getFiltered($auditFilters, $limit, $offset);
        $rows = $result['rows'];

        // mappings
        $actionLabels = [
            'create' => 'Crear', 'update' => 'Actualizar', 'delete' => 'Eliminar',
            'adjust' => 'Ajuste', 'login' => 'Inicio de sesión', 'logout' => 'Cierre de sesión',
            'add' => 'Agregar', 'remove' => 'Eliminar', 'send' => 'Enviar', 'receive' => 'Recibir',
            'approve' => 'Aprobar', 'reject' => 'Rechazar'
        ];
        $entityLabels = [
            'product' => 'Producto', 'products' => 'Productos', 'sale' => 'Venta', 'sales' => 'Ventas',
            'purchase' => 'Compra', 'purchases' => 'Compras', 'user' => 'Usuario', 'users' => 'Usuarios',
            'inventory_movement' => 'Movimiento de inventario', 'work_order' => 'Orden de trabajo',
            'auth' => 'Autenticación', 'audit_log' => 'Registro de auditoría'
        ];

        require_once __DIR__ . '/../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Auditoría');

        // Header
        $headers = ['ID','Fecha','Usuario','Acción','Entidad','ID Entidad','Detalle'];
        $col = 1;
        foreach ($headers as $h) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
            $sheet->setCellValue($cell, $h);
            $col++;
        }

        // Rows
        $rowNum = 2;
        foreach ($rows as $r) {
            $created = $r->created_at ?? '';
            if (!empty($created)) {
                try { $dt = new DateTime($created); $created = $dt->format('d/m/Y H:i'); } catch (Exception $e) {}
            }
            $user = $r->user_name ?? ('#'.($r->user_id ?? ''));
            $act = $r->action ?? '';
            $ent = $r->entity ?? '';
            $actLabel = $actionLabels[strtolower($act)] ?? ucwords(str_replace('_',' ',$act));
            $entLabel = $entityLabels[strtolower($ent)] ?? ucwords(str_replace('_',' ',$ent));
            $detail = isset($r->details) ? preg_replace("/\r?\n/", ' ', $r->details) : '';

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1) . $rowNum, $r->id ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2) . $rowNum, $created);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3) . $rowNum, $user);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4) . $rowNum, $actLabel);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5) . $rowNum, $entLabel);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(6) . $rowNum, $r->entity_id ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7) . $rowNum, $detail);
            $rowNum++;
        }

        // Style header bold
        $headerRange = 'A1:G1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);

        // Header background and font color
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('083745');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('CFEFFF');

        // Add borders for the whole range
        $lastRow = $rowNum - 1;
        $fullRange = "A1:G{$lastRow}";
        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('2E4D56');

        // Apply alternating row fill for readability and set readable font color for data
        for ($r = 2; $r <= $lastRow; $r++) {
            $fillColor = ($r % 2 === 0) ? '071226' : '0B2A35';
            $sheet->getStyle("A{$r}:G{$r}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle("A{$r}:G{$r}")->getFill()->getStartColor()->setRGB($fillColor);
            // make text lighter for readability on dark rows
            $sheet->getStyle("A{$r}:G{$r}")->getFont()->getColor()->setRGB('DFF7FB');
        }

        // Ensure detail column wraps text
        if ($lastRow >= 2) {
            $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setWrapText(true);
        }

        // Set autofilter and freeze header row so Excel shows filtering on open
        $sheet->setAutoFilter($fullRange);
        $sheet->freezePane('A2');

        // Autosize columns
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Prepare writer and send
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'audit_export_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        // no caching
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * exportAuditPdf
     * Render filtered audit rows into a PDF (Dompdf) and stream to browser.
     */
    public function exportAuditPdf() {
        $this->requireRole(['supervisor','admin','finanzas','gerencia']);

        $auditFilters = [
            'user' => trim($_GET['audit_user'] ?? ''),
            'action' => trim($_GET['audit_action'] ?? ''),
            'from' => trim($_GET['audit_from'] ?? ''),
            'to' => trim($_GET['audit_to'] ?? ''),
        ];

        // cap export size
        $maxExport = 2000;
        $page = isset($_GET['audit_page']) ? max(1, (int)$_GET['audit_page']) : 0;
        $perPage = max(5, min(100, (int)($_GET['audit_per_page'] ?? 25)));
        if ($page > 0) {
            $limit = $perPage;
            $offset = ($page - 1) * $perPage;
        } else {
            $limit = $maxExport;
            $offset = 0;
        }
        $result = $this->auditLogModel->getFiltered($auditFilters, $limit, $offset);
        $rows = $result['rows'];

        // build HTML
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,Helvetica,sans-serif;font-size:12px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ddd;padding:6px 8px;text-align:left;}th{background:#f4f6f8} .logo{max-height:60px;margin-bottom:8px;}</style></head><body>';
        // try include logo if exists
        $logoPath = __DIR__ . '/../assets/img/LogoPecosol.png';
        if (file_exists($logoPath)) {
            $imgData = base64_encode(file_get_contents($logoPath));
            $src = 'data:image/png;base64,' . $imgData;
            $html .= '<img class="logo" src="' . $src . '" alt="Logo">';
        } else {
            $html .= '<h1>Empresa</h1>';
        }
        $html .= '<h2>Exportación de auditoría</h2>';
        $html .= '<table><thead><tr><th>ID</th><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>ID Entidad</th><th>Detalle</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($r->id ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($r->created_at ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($r->user_name ?? ('#'.($r->user_id ?? ''))) . '</td>';
            $html .= '<td>' . htmlspecialchars($r->action ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($r->entity ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($r->entity_id ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($r->details ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        // render PDF via Dompdf
        require_once __DIR__ . '/../vendor/autoload.php';
        try {
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', false);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $filename = 'audit_export_' . date('Ymd_His') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
        } catch (Throwable $e) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "Error generando PDF: " . $e->getMessage();
        }
        exit;
    }

    /**
     * auditPartial
     * Returns JSON with table rows html and pagination html for AJAX updates.
     */
    public function auditPartial() {
        $this->requireRole(['supervisor','admin','finanzas','gerencia']);

        $page = max(1, (int)($_GET['audit_page'] ?? 1));
        $perPage = max(5, min(100, (int)($_GET['audit_per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $filters = [
            'user' => trim($_GET['audit_user'] ?? ''),
            'action' => trim($_GET['audit_action'] ?? ''),
            'from' => trim($_GET['audit_from'] ?? ''),
            'to' => trim($_GET['audit_to'] ?? ''),
        ];

        $result = $this->auditLogModel->getFiltered($filters, $perPage, $offset);
        $rows = $result['rows'];
        $total = $result['total'];

        // small maps for labels
        $actionLabels = [
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
            'archive' => 'Archivar',
            'restore' => 'Restaurar',
            'cancel' => 'Cancelar',
            'enable' => 'Activar',
            'disable' => 'Desactivar',
            'export' => 'Exportar',
            'import' => 'Importar',
            'print' => 'Imprimir'
        ];
        $entityLabels = [
            'product' => 'Producto',
            'products' => 'Productos',
            'sale' => 'Venta',
            'sales' => 'Ventas',
            'purchase' => 'Compra',
            'purchases' => 'Compras',
            'user' => 'Usuario',
            'users' => 'Usuarios',
            'inventory_movement' => 'Movimiento de inventario',
            'inventory_movements' => 'Movimientos de inventario',
            'work_order' => 'Orden de trabajo',
            'work_orders' => 'Órdenes de trabajo',
            'auth' => 'Autenticación',
            'audit_log' => 'Registro de auditoría',
            'product_category' => 'Categoría de producto',
            'supplier' => 'Proveedor',
            'order' => 'Orden'
        ];

        $rowsHtml = '';
        if (!empty($rows)) {
            foreach ($rows as $audit) {
                $rawAction = $audit->action ?? '';
                $actionLabel = $actionLabels[$rawAction] ?? ucfirst(str_replace('_',' ',$rawAction));
                $userName = $audit->user_name ?? ('#'.($audit->user_id ?? ''));
                $entity = $audit->entity ?? '';
                $entityLabel = $entityLabels[strtolower($entity)] ?? ucfirst($entity);
                $entityId = $audit->entity_id ?? '';
                $details = $audit->details ?? '';
                $created = date('d/m H:i', strtotime($audit->created_at));
                $severity = 'info';
                $detailsSafe = htmlspecialchars($details);

                $rowsHtml .= '<tr>';
                $rowsHtml .= '<td class="td-date">' . $created . '</td>';
                $rowsHtml .= '<td class="td-user">' . htmlspecialchars($userName) . '</td>';
                $rowsHtml .= '<td class="td-action">' . htmlspecialchars($actionLabel) . '</td>';
                $rowsHtml .= '<td class="td-entity">' . htmlspecialchars($entityLabel) . '</td>';
                $rowsHtml .= '<td class="td-id">' . htmlspecialchars($entityId) . '</td>';
                $rowsHtml .= '<td class="td-summary">' . (strlen($details) > 80 ? substr($detailsSafe,0,80) . '...' : $detailsSafe) . '</td>';
                $rowsHtml .= '<td class="td-severity">';
                $rowsHtml .= '<span class="severity-info">INFO</span>';
                $rowsHtml .= ' <button class="btn btn-sm btn-modal" data-details="' . $detailsSafe . '" data-user="' . htmlspecialchars($userName) . '" data-action="' . htmlspecialchars($actionLabel) . '" data-created="' . htmlspecialchars($audit->created_at) . '" data-entity="' . htmlspecialchars($entityLabel) . '" data-entityid="' . htmlspecialchars($entityId) . '">Ver</button>';
                $rowsHtml .= '</td>';
                $rowsHtml .= '</tr>';
            }
        } else {
            $rowsHtml = '<tr><td colspan="7" style="padding:12px;color:#a0a0a0;">No hay eventos recientes en la bitácora.</td></tr>';
        }

        // pagination html
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        $current = $page;
        $paginationHtml = '<div class="audit-pagination" style="margin-top:10px;display:flex;gap:8px;align-items:center;">';
        $paginationHtml .= '<div style="font-size:0.9rem;color:#cfeeff">Página ' . $current . ' de ' . max(1,$totalPages) . '</div>';
        $paginationHtml .= '<div style="margin-left:auto;display:flex;gap:8px;">';
        if ($current > 1) {
            $paginationHtml .= '<a class="btn audit-page-link" data-page="' . ($current-1) . '" href="#">&larr; Anterior</a>';
        }
        if ($current < $totalPages) {
            $paginationHtml .= '<a class="btn audit-page-link" data-page="' . ($current+1) . '" href="#">Siguiente &rarr;</a>';
        }
        $paginationHtml .= '</div></div>';

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['rows' => $rowsHtml, 'pagination' => $paginationHtml]);
        exit;
    }
}
