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
            case 'employee':
            default:
                header('Location: index.php?controller=dashboard&action=employeeHome');
                exit;
        }
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
        $this->requireRole(['logistica']);
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
        $this->requireRole(['logistica']);
        $productos = $this->productModel->getAll();
        require __DIR__ . '/../views/admin/inventario/recount.php';
    }

    /**
     * logisticsPurchases() - Compras para logística (abastecimiento)
     */
    public function logisticsPurchases() {
        $this->requireRole(['logistica']);
        $compras = $this->purchaseModel->getAll(300);
        require __DIR__ . '/../views/admin/compras/list_purchases.php';
    }

    public function logisticsWorkOrders() {
        $this->requireRole(['logistica']);
        $ordenes = $this->workOrderModel->getAll(300);
        require __DIR__ . '/../views/admin/ordenes/list_work_orders.php';
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

    public function strategyReports() {
        $this->requireRole(['estrategico']);
        $productos = $this->productModel->getAll();
        $auditorias = $this->auditLogModel->getRecent(80);
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
}
