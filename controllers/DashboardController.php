<?php
// controllers/DashboardController.php

class DashboardController {
    private $saleModel;
    private $productModel;
    private $inventoryMovementModel;

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
        $this->saleModel    = new Sale();
        $this->productModel = new Product();
        $this->inventoryMovementModel = new InventoryMovement();
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
        if ($_SESSION['role'] !== 'admin') {
            // Si no es admin, redirigir al dashboard de empleado
            header('Location: index.php?controller=dashboard&action=employeeHome');
            exit;
        }

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
        // Verificar que el rol sea 'employee'
        if ($_SESSION['role'] !== 'employee') {
            header('Location: index.php?controller=dashboard&action=adminHome');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $hoy    = date('Y-m-d');

        // Total de ventas hoy filtradas por user_id
        $ventasHoyEmpleado = $this->saleModel->getTotalSalesByDateAndUser($hoy, $hoy, $userId);

        // Últimas 5 ventas de este empleado
        $ultimasVentasEmpleado = $this->saleModel->getLastSalesByUser($userId, 5);

        require_once __DIR__ . '/../views/employee/dashboard.php';
    }
}
