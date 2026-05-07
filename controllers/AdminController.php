<?php
// controllers/AdminController.php

class AdminController {
    private $productModel;
    private $userModel;
    private $saleModel;
    private $inventoryMovementModel;
    private $purchaseModel;
    private $workOrderModel;
    private $auditLogModel;

    private function authorizeAction(string $role, string $action): void {
        $allowedActions = [
            'admin' => ['*'],  // Control total del sistema
            'gerencia' => ['reports'],  // Solo reportes ejecutivos
            'comercial' => ['listSalesAdmin', 'addSaleAdminForm', 'storeSaleAdmin'],  // Gestión de ventas
            'logistica' => ['listInventoryMovements', 'inventoryRecountForm', 'processInventoryRecount', 'listWorkOrders', 'addWorkOrderForm', 'storeWorkOrder', 'updateWorkOrderStatus', 'listPurchases', 'addPurchaseForm', 'storePurchase', 'editPurchaseForm', 'updatePurchase', 'deletePurchase'],  // Operación de almacén y compras
            'finanzas' => ['listPurchases', 'listSalesAdmin', 'editSaleAdminForm', 'updateSaleAdmin', 'deleteSaleAdmin', 'reports', 'exportCurrentInventoryCsv', 'exportMovementsCsv', 'exportSalesCsv'],  // Control financiero y CxP/CxC
            'estrategico' => ['listProducts', 'reports'],  // Datos maestros y análisis
        ];

        if ($role === 'admin') {
            return;
        }

        if (empty($action) || !isset($allowedActions[$role]) || (!in_array('*', $allowedActions[$role], true) && !in_array($action, $allowedActions[$role], true))) {
            header('Location: index.php?controller=dashboard&action=home');
            exit;
        }
    }

    public function __construct() {
        // 1) Arrancar sesión si no existe
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2) Si no hay usuario logueado, redirigir a login
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 3) Autorizar el acceso según rol y acción solicitada
        $role   = $_SESSION['role'] ?? '';
        $action = $_GET['action'] ?? '';
        $this->authorizeAction($role, $action);

        // 4) Instanciar modelos necesarios
        require_once __DIR__ . '/../models/Product.php';
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Sale.php';
        require_once __DIR__ . '/../models/InventoryMovement.php';
        require_once __DIR__ . '/../models/Purchase.php';
        require_once __DIR__ . '/../models/WorkOrder.php';
        require_once __DIR__ . '/../models/AuditLog.php';

        $this->productModel = new Product();
        $this->userModel    = new User();
        $this->saleModel    = new Sale();
        $this->inventoryMovementModel = new InventoryMovement();
        $this->purchaseModel = new Purchase();
        $this->workOrderModel = new WorkOrder();
        $this->auditLogModel = new AuditLog();
    }

    /**************************************************************************
     * PRODUCTOS: CRUD completo
     **************************************************************************/

    public function listProducts() {
        $productos = $this->productModel->getAll();
        $lowStockCount = $this->productModel->countLowStockProducts();
        require_once __DIR__ . '/../views/admin/productos/list_products.php';
    }

    public function addProductForm() {
        require_once __DIR__ . '/../views/admin/productos/add_product.php';
    }

    public function storeProduct() {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $stock       = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $stockMinimum = isset($_POST['stock_minimum']) ? (int)$_POST['stock_minimum'] : 0;

        $error = '';
        if ($name === '' || $price <= 0 || $stock < 0 || $stockMinimum < 1) {
            $error = 'Completa nombre, precio, stock y stock mínimo con valores válidos. El stock mínimo debe ser mayor que cero.';
        }

        if (!empty($error)) {
            require_once __DIR__ . '/../views/admin/productos/add_product.php';
            return;
        }

        $creado = $this->productModel->create($name, $description, $price, $stock, $stockMinimum);
        if (!$creado) {
            $error = 'No se pudo agregar el producto. Intenta nuevamente.';
            require_once __DIR__ . '/../views/admin/productos/add_product.php';
            return;
        }

        header('Location: index.php?controller=admin&action=listProducts');
        exit;
    }

    public function editProductForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        $producto = $this->productModel->findById($id);
        if (!$producto) {
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        require_once __DIR__ . '/../views/admin/productos/edit_product.php';
    }

    public function updateProduct() {
        $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $stock       = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $stockMinimum = isset($_POST['stock_minimum']) ? (int)$_POST['stock_minimum'] : 0;

        $error = '';
        if ($id <= 0 || $name === '' || $price <= 0 || $stock < 0 || $stockMinimum < 1) {
            $error = 'ID inválido o valores de producto incorrectos. El stock mínimo debe ser mayor que cero.';
        } else {
            $productoExistente = $this->productModel->findById($id);
            if (!$productoExistente) {
                $error = 'El producto que intentas editar no existe.';
            }
        }

        if (!empty($error)) {
            $producto = (object)[
                'id'          => $id,
                'name'        => $name,
                'description' => $description,
                'price'       => $price,
                'stock'       => $stock,
                'stock_minimum' => $stockMinimum
            ];
            require_once __DIR__ . '/../views/admin/productos/edit_product.php';
            return;
        }

        $actualizado = $this->productModel->update($id, $name, $description, $price, $stock, $stockMinimum);
        if (!$actualizado) {
            $error = 'No se pudo actualizar el producto. Intenta nuevamente.';
            $producto = (object)[
                'id'          => $id,
                'name'        => $name,
                'description' => $description,
                'price'       => $price,
                'stock'       => $stock,
                'stock_minimum' => $stockMinimum
            ];
            require_once __DIR__ . '/../views/admin/productos/edit_product.php';
            return;
        }

        header('Location: index.php?controller=admin&action=listProducts');
        exit;
    }

    public function deleteProduct() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        $numVentas = $this->saleModel->countSalesByProduct($id);
        if ($numVentas > 0) {
            $_SESSION['error_product_delete'] = "No se puede eliminar el producto porque tiene {$numVentas} venta(s) asociada(s).";
            header('Location: index.php?controller=admin&action=listProducts');
            exit;
        }

        $this->productModel->delete($id);
        header('Location: index.php?controller=admin&action=listProducts');
        exit;
    }

    /**************************************************************************
     * EMPLEADOS: CRUD completo
     **************************************************************************/

    public function listEmployees() {
        $empleados = $this->userModel->getAllEmployees();
        require_once __DIR__ . '/../views/admin/employee/list_employees.php';
    }

    public function addEmployeeForm() {
        require_once __DIR__ . '/../views/admin/employee/add_employee.php';
    }

  public function storeEmployee() {
    $username  = trim($_POST['username']  ?? '');
    $password  = trim($_POST['password']  ?? '');
    $fullName  = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $role      = trim($_POST['role']      ?? '');

    $error = '';
    // Validar campos
    if ($username === '' || $password === '' || $fullName === '' || $email === '' || $role === '') {
        $error = 'Todos los campos son obligatorios, incluyendo el rol.';
    } elseif (!in_array($role, ['admin','gerencia','comercial','logistica','finanzas','estrategico'], true)) {
        $error = 'Rol no válido.';
    } else {
        // Verificar usuario existente
        $existe = $this->userModel->findByUsername($username);
        if ($existe) {
            $error = 'El nombre de usuario ya existe.';
        }
    }

    if (!empty($error)) {
        // Reenviar a la vista con el error y valores ya escritos
        require_once __DIR__ . '/../views/admin/employee/add_employee.php';
        return;
    }

    // Crear el usuario con el rol elegido
    $creado = $this->userModel->create($username, $password, $fullName, $email, $role);
    if (!$creado) {
        $error = 'No se pudo crear el usuario. Intenta nuevamente.';
        require_once __DIR__ . '/../views/admin/employee/add_employee.php';
        return;
    }

    // Redirigir de vuelta al listado
    header('Location: index.php?controller=admin&action=listEmployees');
    exit;
    }


    public function editEmployeeForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        $empleado = $this->userModel->findById($id);
        if (!$empleado || $empleado->role === 'admin') {
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        require_once __DIR__ . '/../views/admin/employee/edit_employee.php';

    }

    public function updateEmployee() {
        $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $fullName  = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $role      = trim($_POST['role'] ?? '');
        $changePwd = isset($_POST['change_password']) && $_POST['change_password'] === 'on';
        $newPwd    = trim($_POST['new_password'] ?? '');

        $error = '';
        $empleadoExistente = null;

        if ($id <= 0 || $fullName === '' || $email === '' || $role === '') {
            $error = 'ID inválido o campos obligatorios vacíos.';
        } elseif (!in_array($role, ['admin','gerencia','comercial','logistica','finanzas','estrategico'], true)) {
            $error = 'Rol no válido.';
        } else {
            $empleadoExistente = $this->userModel->findById($id);
            if (!$empleadoExistente || $empleadoExistente->role === 'admin') {
                $error = 'El empleado no existe.';
            }
            if (empty($error) && $changePwd && $newPwd === '') {
                $error = 'Para cambiar contraseña, ingresa la nueva contraseña.';
            }
        }

        if (!empty($error)) {
            $empleado = (object)[
                'id'        => $id,
                'username'  => $empleadoExistente->username ?? '',
                'full_name' => $fullName,
                'email'     => $email,
                'role'      => $role ?: ($empleadoExistente->role ?? 'employee')
            ];
            require_once __DIR__ . '/../views/admin/employee/edit_employee.php';
            return;
        }

        $actualizado = $this->userModel->update($id, $fullName, $email, $role);
        if (!$actualizado) {
            $error = 'Error al actualizar los datos del empleado. Intenta nuevamente.';
            $empleado = (object)[
                'id'        => $id,
                'username'  => $empleadoExistente->username,
                'full_name' => $fullName,
                'email'     => $email,
                'role'      => 'employee'
            ];
            require_once __DIR__ . '/../views/admin/employee/edit_employee.php';
            return;
        }

        if ($changePwd) {
            $pwdActualizado = $this->userModel->updatePassword($id, $newPwd);
            if (!$pwdActualizado) {
                $_SESSION['error_employee_pwd'] = 'No se pudo cambiar la contraseña.';
            }
        }

        header('Location: index.php?controller=admin&action=listEmployees');
        exit;
    }

    public function deleteEmployee() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        $numVentas = $this->saleModel->countSalesByUser($id);
        $numMovimientos = $this->userModel->countInventoryMovementsByUser($id);
        if ($numVentas > 0 || $numMovimientos > 0) {
            $messages = [];
            if ($numVentas > 0) {
                $messages[] = "{$numVentas} venta(s)";
            }
            if ($numMovimientos > 0) {
                $messages[] = "{$numMovimientos} movimiento(s) de inventario";
            }
            $_SESSION['error_employee_delete'] = 'No se puede eliminar al empleado porque tiene ' . implode(' y ', $messages) . '.';
            header('Location: index.php?controller=admin&action=listEmployees');
            exit;
        }

        $this->userModel->delete($id);
        header('Location: index.php?controller=admin&action=listEmployees');
        exit;
    }

    /**************************************************************************
     * VENTAS (ADMIN): CRUD completo
     **************************************************************************/

    public function listSalesAdmin() {
        // Obtener todas las ventas (sin límite) ordenadas por fecha descendente
        $ventas = $this->saleModel->getAllSales();
        $hoy = date('Y-m-d');
        $totalHoy = $this->saleModel->getTotalSalesByDate($hoy, $hoy);
        require_once __DIR__ . '/../views/admin/ventas/list_sales.php';
    }

    public function addSaleAdminForm() {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'comercial'], true)) {
            header('Location: index.php?controller=dashboard&action=home');
            exit;
        }
        // Obtener solo admin y comercial para registrar ventas
        $empleados = $this->userModel->getAdminAndCommercial();
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/ventas/add_sale_admin.php';
    }

    public function storeSaleAdmin() {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'comercial'], true)) {
            header('Location: index.php?controller=dashboard&action=home');
            exit;
        }
        $userId      = isset($_POST['user_id'])    ? (int)$_POST['user_id']    : 0;
        $productId   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity    = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 0;
        $clientName  = trim($_POST['client_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $error = '';
        if ($userId <= 0 || $productId <= 0 || $quantity <= 0 || $clientName === '') {
            $error = 'Empleado, producto, cantidad y cliente son obligatorios.';
        } else {
            $empleado = $this->userModel->findById($userId);
            if (!$empleado || !in_array($empleado->role, ['admin', 'comercial'], true)) {
                $error = 'El empleado seleccionado no es válido.';
            }
            $producto = $this->productModel->findById($productId);
            if (!$producto) {
                $error = 'El producto seleccionado no existe.';
            } elseif ($quantity > $producto->stock) {
                $error = 'La cantidad supera el stock disponible.';
            }
        }

        if (!empty($error)) {
            $empleados = $this->userModel->getAdminAndCommercial();
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/ventas/add_sale_admin.php';
            return;
        }

        $unitPrice  = (float)$producto->price;
        $totalPrice = $unitPrice * $quantity;

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            $discounted = $this->productModel->decreaseStockIfAvailable($productId, $quantity);
            if (!$discounted) {
                throw new RuntimeException('Stock insuficiente al confirmar la venta.');
            }

            $newSaleId = $this->saleModel->createSale(
                $userId,
                $productId,
                $quantity,
                $unitPrice,
                $totalPrice,
                $clientName,
                $description
            );
            if (!$newSaleId) {
                throw new RuntimeException('No se pudo crear la venta.');
            }

            $movementNote = $description !== '' ? $description : "Venta ID: {$newSaleId}";
            $okMovement = $this->saleModel->registerStockMovement(
                $productId,
                $userId,
                -$quantity,
                'salida',
                $movementNote
            );
            if (!$okMovement) {
                throw new RuntimeException('No se pudo registrar el movimiento de salida.');
            }

            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'create',
                'sale',
                (int)$newSaleId,
                "Venta registrada para cliente {$clientName} por S/. " . number_format($totalPrice, 2, '.', ',')
            );

            $conn->commit();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'Error al registrar venta: ' . $e->getMessage();
            $empleados = $this->userModel->getAdminAndCommercial();
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/ventas/add_sale_admin.php';
            return;
        }

        header('Location: index.php?controller=' . ($role === 'comercial' ? 'employee' : 'admin') . '&action=' . ($role === 'comercial' ? 'listSalesEmployee' : 'listSalesAdmin') . '&created_sale_id=' . $newSaleId);
        exit;
    }

    public function editSaleAdminForm() {
        $saleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $role = $_SESSION['role'] ?? '';
        $listRedirect = $role === 'finanzas'
            ? 'index.php?controller=dashboard&action=financeSales'
            : 'index.php?controller=admin&action=listSalesAdmin';

        if ($saleId <= 0) {
            header('Location: ' . $listRedirect);
            exit;
        }

        // Obtener la venta con detalles y renombrar a $venta
        $venta = $this->saleModel->findByIdWithDetails($saleId);
        if (!$venta) {
            header('Location: ' . $listRedirect);
            exit;
        }

        $role = $_SESSION['role'] ?? '';
        $empleados = $role === 'finanzas'
            ? $this->userModel->getAdminAndCommercial()
            : $this->userModel->getAllEmployees();
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/ventas/edit_sale_admin.php';
    }

    public function updateSaleAdmin() {
        $id          = isset($_POST['id'])         ? (int)$_POST['id']         : 0;
        $userId      = isset($_POST['user_id'])    ? (int)$_POST['user_id']    : 0;
        $productId   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity    = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 0;
        $clientName  = trim($_POST['client_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $error = '';
        $venta = null;
        if ($id <= 0 || $userId <= 0 || $productId <= 0 || $quantity <= 0 || $clientName === '') {
            $error = 'Todos los campos (empleado, producto, cantidad y cliente) son obligatorios.';
        } else {
            // Traer la venta original (antes de editar)
            $venta = $this->saleModel->findByIdWithDetails($id);
            if (!$venta) {
                $error = 'La venta que intentas editar no existe.';
            }
            $empleado = $this->userModel->findById($userId);
            if (!$empleado || !in_array($empleado->role, ['admin', 'comercial'], true)) {
                $error = 'El empleado seleccionado no es válido.';
            }
            $producto = $this->productModel->findById($productId);
            if (!$producto) {
                $error = 'El producto seleccionado no existe.';
            }
        }

        if (!empty($error)) {
            // Si hay error, recargar vista usando la variable $venta
            $role = $_SESSION['role'] ?? '';
            $empleados = $role === 'finanzas'
                ? $this->userModel->getAdminAndCommercial()
                : $this->userModel->getAllEmployees();
            $productos = $this->productModel->getAll();

            // Si no existe, crear un objeto fallback con los datos mínimos
            if (!$venta) {
                $venta = (object)[
                    'id'            => $id,
                    'user_id'       => $userId,
                    'product_id'    => $productId,
                    'quantity'      => $quantity,
                    'unit_price'    => 0,
                    'total_price'   => 0,
                    'client_name'   => $clientName,
                    'description'   => $description,
                    'sale_date'     => '',
                    'user_name'     => '',
                    'product_name'  => '',
                    'current_stock' => 0
                ];
            }

            require_once __DIR__ . '/../views/admin/ventas/edit_sale_admin.php';
            return;
        }

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            $sameProduct = ((int)$venta->product_id === $productId);
            $quantityDiff = $quantity - (int)$venta->quantity;
            $movementNote = $description !== '' ? $description : "Edición Venta ID: {$id}";

            if (!$sameProduct || $quantityDiff !== 0) {
                if ($sameProduct) {
                    if ($quantityDiff > 0) {
                        $discounted = $this->productModel->decreaseStockIfAvailable($productId, $quantityDiff);
                        if (!$discounted) {
                            throw new RuntimeException('La nueva cantidad supera el stock disponible.');
                        }

                        $okMovement = $this->saleModel->registerStockMovement(
                            $productId,
                            $userId,
                            -$quantityDiff,
                            'salida',
                            $movementNote
                        );
                        if (!$okMovement) {
                            throw new RuntimeException('No se pudo registrar el movimiento de edición.');
                        }
                    } else {
                        $restored = $this->productModel->increaseStock($productId, abs($quantityDiff));
                        if (!$restored) {
                            throw new RuntimeException('No se pudo restaurar stock de la venta anterior.');
                        }

                        $okMovement = $this->saleModel->registerStockMovement(
                            $productId,
                            $userId,
                            abs($quantityDiff),
                            'ingreso',
                            $movementNote
                        );
                        if (!$okMovement) {
                            throw new RuntimeException('No se pudo registrar el movimiento de edición.');
                        }
                    }
                } else {
                    $restored = $this->productModel->increaseStock((int)$venta->product_id, (int)$venta->quantity);
                    if (!$restored) {
                        throw new RuntimeException('No se pudo restaurar stock de la venta anterior.');
                    }

                    $discounted = $this->productModel->decreaseStockIfAvailable($productId, $quantity);
                    if (!$discounted) {
                        throw new RuntimeException('La nueva cantidad supera el stock disponible.');
                    }

                    $okRestore = $this->saleModel->registerStockMovement(
                        (int)$venta->product_id,
                        $userId,
                        (int)$venta->quantity,
                        'ingreso',
                        $movementNote
                    );
                    if (!$okRestore) {
                        throw new RuntimeException('No se pudo registrar el movimiento de restauración durante la edición.');
                    }

                    $okMovement = $this->saleModel->registerStockMovement(
                        $productId,
                        $userId,
                        -$quantity,
                        'salida',
                        $movementNote
                    );
                    if (!$okMovement) {
                        throw new RuntimeException('No se pudo registrar el movimiento de edición.');
                    }
                }
            }

            $prodNew = $this->productModel->findById($productId);
            $unitPrice  = (float)$prodNew->price;
            $totalPrice = $unitPrice * $quantity;

            $actualizado = $this->saleModel->updateSale(
                $id,
                $userId,
                $productId,
                $quantity,
                $unitPrice,
                $totalPrice,
                $clientName,
                $description
            );
            if (!$actualizado) {
                throw new RuntimeException('No se pudo actualizar la venta.');
            }

            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'update',
                'sale',
                $id,
                "Venta actualizada para cliente {$clientName}"
            );

            if ($description !== '') {
                $this->saleModel->updateSaleMovementNoteBySaleId($id, $description);
            }

            $conn->commit();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'No se pudo actualizar la venta: ' . $e->getMessage();
            $empleados = $this->userModel->getAllEmployees();
            $productos = $this->productModel->getAll();
            $venta = $this->saleModel->findByIdWithDetails($id);
            require_once __DIR__ . '/../views/admin/ventas/edit_sale_admin.php';
            return;
        }

        $role = $_SESSION['role'] ?? '';
        $redirectTo = $role === 'finanzas'
            ? 'index.php?controller=dashboard&action=financeSales'
            : 'index.php?controller=admin&action=listSalesAdmin';

        header('Location: ' . $redirectTo);
        exit;
    }

    public function deleteSaleAdmin() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        $venta = $this->saleModel->findByIdWithDetails($id);
        if (!$venta) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            $restored = $this->productModel->increaseStock((int)$venta->product_id, (int)$venta->quantity);
            if (!$restored) {
                throw new RuntimeException('No se pudo restaurar stock al eliminar la venta.');
            }

            $deleted = $this->saleModel->deleteSale($id);
            if (!$deleted) {
                throw new RuntimeException('No se pudo eliminar la venta.');
            }

            $okMovement = $this->saleModel->registerStockMovement(
                $venta->product_id,
                $venta->user_id,
                $venta->quantity,
                'ingreso',
                "Eliminación Venta ID: {$id}"
            );
            if (!$okMovement) {
                throw new RuntimeException('No se pudo registrar movimiento de eliminación.');
            }

            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'delete',
                'sale',
                $id,
                'Venta eliminada y stock restaurado'
            );

            $conn->commit();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error_sale_delete'] = 'No se pudo eliminar la venta: ' . $e->getMessage();
        }

        $role = $_SESSION['role'] ?? '';
        $redirectTo = $role === 'finanzas'
            ? 'index.php?controller=dashboard&action=financeSales'
            : 'index.php?controller=admin&action=listSalesAdmin';

        header('Location: ' . $redirectTo);
        exit;
    }

    public function downloadSaleInvoicePdf() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        $venta = $this->saleModel->findByIdWithDetails($id);
        if (!$venta) {
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        if (!class_exists(\Dompdf\Dompdf::class)) {
            $_SESSION['error_sale_delete'] = 'No se pudo generar PDF. Instala dompdf con composer.';
            header('Location: index.php?controller=admin&action=listSalesAdmin');
            exit;
        }

        $logoPath = __DIR__ . '/../assets/img/LogoPecosol.png';
        $logoData = '';
        if (is_file($logoPath)) {
            $content = @file_get_contents($logoPath);
            if ($content !== false) {
                $logoData = 'data:image/png;base64,' . base64_encode($content);
            }
        }

        $clientName = trim((string)($venta->client_name ?? 'Cliente General'));
        $html = '
            <html>
            <body style="font-family: DejaVu Sans, sans-serif; color:#1a1a2e;">
                <div style="border:1px solid #ddd; padding:18px;">
                    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
                        <tr>
                            <td style="vertical-align:top;">
                                ' . ($logoData !== '' ? '<img src="' . $logoData . '" style="height:55px;">' : '') . '
                                <h2 style="margin:10px 0 4px;">Perú Cold Solutions S.A.C.</h2>
                                <div>RUC: 20603016000</div>
                            </td>
                            <td style="text-align:right; vertical-align:top;">
                                <h3 style="margin:0;">Factura de Venta</h3>
                                <div>Nro: ' . (int)$venta->id . '</div>
                                <div>Fecha: ' . htmlspecialchars((string)formatSaleDate((string)$venta->sale_date, 'd-m-Y H:i')) . '</div>
                            </td>
                        </tr>
                    </table>
                    <p><strong>Cliente:</strong> ' . htmlspecialchars($clientName) . '</p>
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #ccc; padding:8px;">Producto</th>
                                <th style="border:1px solid #ccc; padding:8px;">Cantidad</th>
                                <th style="border:1px solid #ccc; padding:8px;">Precio</th>
                                <th style="border:1px solid #ccc; padding:8px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border:1px solid #ccc; padding:8px;">' . htmlspecialchars((string)$venta->product_name) . '</td>
                                <td style="border:1px solid #ccc; padding:8px; text-align:center;">' . (int)$venta->quantity . '</td>
                                <td style="border:1px solid #ccc; padding:8px; text-align:right;">S/. ' . number_format((float)$venta->unit_price, 2, '.', ',') . '</td>
                                <td style="border:1px solid #ccc; padding:8px; text-align:right;">S/. ' . number_format((float)$venta->total_price, 2, '.', ',') . '</td>
                            </tr>
                        </tbody>
                    </table>
                    <h3 style="text-align:right; margin-top:14px;">TOTAL: S/. ' . number_format((float)$venta->total_price, 2, '.', ',') . '</h3>
                </div>
            </body>
            </html>
        ';

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('factura_venta_' . (int)$venta->id . '.pdf', ['Attachment' => true]);
        exit;
    }

    /**************************************************************************
     * INVENTARIO: Movimientos, Kardex y Reportes
     **************************************************************************/
    public function listInventoryMovements() {
        $startDate    = trim($_GET['start_date'] ?? '');
        $endDate      = trim($_GET['end_date'] ?? '');
        $productId    = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        $movementType = trim($_GET['movement_type'] ?? '');

        $productos    = $this->productModel->getAll();
        $movimientos  = $this->inventoryMovementModel->getFiltered(
            $startDate !== '' ? $startDate : null,
            $endDate !== '' ? $endDate : null,
            $productId > 0 ? $productId : null,
            $movementType !== '' ? $movementType : null,
            500
        );

        require_once __DIR__ . '/../views/admin/inventario/list_movements.php';
    }

    public function addInventoryMovementForm() {
        $productos = $this->productModel->getAll();
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
        require_once __DIR__ . '/../views/admin/inventario/add_movement.php';
    }

    public function lowStockAlerts() {
        $productosConBajoStock = $this->productModel->getLowStockProducts();
        require_once __DIR__ . '/../views/admin/inventario/low_stock_alerts.php';
    }

    public function storeInventoryMovement() {
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $movementType = trim($_POST['movement_type'] ?? '');
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $reason = trim($_POST['reason'] ?? '');
        $userId = (int)$_SESSION['user_id'];

        $error = '';
        if ($productId <= 0 || !in_array($movementType, ['ingreso', 'salida'], true) || $quantity <= 0) {
            $error = 'Producto, tipo y cantidad validos son obligatorios.';
        }

        $producto = $this->productModel->findById($productId);
        if (!$producto) {
            $error = 'El producto seleccionado no existe.';
        } elseif ($movementType === 'salida' && $quantity > (int)$producto->stock) {
            $error = 'No hay stock suficiente para registrar esta salida.';
        }

        if (!empty($error)) {
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/inventario/add_movement.php';
            return;
        }

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            if ($movementType === 'ingreso') {
                $adjusted = $this->productModel->increaseStock($productId, $quantity);
            } else {
                $adjusted = $this->productModel->decreaseStockIfAvailable($productId, $quantity);
            }
            if (!$adjusted) {
                throw new RuntimeException('No fue posible ajustar stock para este movimiento.');
            }

            $okMovement = $this->inventoryMovementModel->create(
                $productId,
                $userId,
                $quantity,
                $movementType,
                $reason !== '' ? $reason : 'Movimiento manual'
            );
            if (!$okMovement) {
                throw new RuntimeException('No se pudo registrar el movimiento.');
            }

            $conn->commit();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'No se pudo registrar el movimiento: ' . $e->getMessage();
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/inventario/add_movement.php';
            return;
        }

        header('Location: index.php?controller=admin&action=listInventoryMovements');
        exit;
    }

    public function reports() {
        $productos = $this->productModel->getAll();
        $auditorias = $this->auditLogModel->getRecent(80);
        require_once __DIR__ . '/../views/admin/reportes/index.php';
    }

    public function exportCurrentInventoryCsv() {
        $productos = $this->productModel->getAll();
        $filename = 'inventario_actual_' . date('Ymd_His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventario Actual');

        $headers = ['ID', 'Nombre', 'Descripción', 'Precio', 'Stock', 'Stock Mínimo', 'Estado'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($productos as $prod) {
            $estado = $prod->stock <= $prod->stock_minimum ? 'Bajo Stock' : 'Normal';
            $sheet->fromArray([
                $prod->id,
                $prod->name,
                $prod->description,
                $prod->price,
                $prod->stock,
                $prod->stock_minimum,
                $estado
            ], null, 'A' . $row);
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0F3460');
        $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E2:F' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportMovementsCsv() {
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        $movementType = trim($_GET['movement_type'] ?? '');

        $movements = $this->inventoryMovementModel->getFiltered($startDate, $endDate, $productId, $movementType);
        $filename = 'movimientos_inventario_' . date('Ymd_His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Movimientos');

        $headers = ['Fecha', 'Producto', 'Usuario', 'Tipo', 'Cantidad', 'Motivo'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($movements as $mov) {
            $dateValue = !empty($mov->movement_date)
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel((new \DateTime($mov->movement_date))->getTimestamp())
                : '';
            $sheet->fromArray([
                $dateValue,
                $mov->product_name,
                $mov->user_name,
                ucfirst($mov->movement_type),
                abs((int)$mov->quantity_change),
                $mov->notes
            ], null, 'A' . $row);
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0F3460');
        $sheet->getStyle('A2:A' . $lastRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $sheet->getStyle('D2:D' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportSalesCsv() {
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');

        $ventas = $this->saleModel->getAllSales($startDate, $endDate);
        $filename = 'ventas_' . date('Ymd_His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ventas');

        $headers = ['ID', 'Fecha', 'Empleado', 'Producto', 'Cantidad', 'Precio Unitario', 'Total', 'Cliente', 'Descripción'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($ventas as $venta) {
            $dateValue = $venta->sale_date ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel((new \DateTime($venta->sale_date))->getTimestamp()) : '';
            $sheet->fromArray([
                $venta->id,
                $dateValue,
                $venta->user_name,
                $venta->product_name,
                $venta->quantity,
                (float)$venta->unit_price,
                (float)$venta->total_price,
                $venta->client_name,
                $venta->description
            ], null, 'A' . $row);
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0F3460');
        $sheet->getStyle('B2:B' . $lastRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $sheet->getStyle('F2:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E2:E' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**************************************************************************
     * RECONTEO DE INVENTARIO
     **************************************************************************/
    public function inventoryRecountForm() {
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/inventario/recount.php';
    }

    public function processInventoryRecount() {
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $physicalStock = isset($_POST['physical_stock']) ? (int)$_POST['physical_stock'] : -1;
        $userId = (int)$_SESSION['user_id'];

        $error = '';
        $success = '';
        if ($productId <= 0 || $physicalStock < 0) {
            $error = 'Selecciona un producto y un stock físico válido.';
        }

        $producto = $this->productModel->findById($productId);
        if (!$producto) {
            $error = 'El producto seleccionado no existe.';
        }

        if (!empty($error)) {
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/inventario/recount.php';
            return;
        }

        $systemStock = (int)$producto->stock;
        $difference = $physicalStock - $systemStock;
        if ($difference === 0) {
            $success = 'No hay diferencias entre stock del sistema y stock físico.';
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/inventario/recount.php';
            return;
        }

        $movementType = $difference > 0 ? 'ingreso' : 'salida';
        $quantity = abs($difference);
        $reason = 'Ajuste por reconteo';

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            if ($movementType === 'ingreso') {
                $adjusted = $this->productModel->increaseStock($productId, $quantity);
            } else {
                $adjusted = $this->productModel->decreaseStockIfAvailable($productId, $quantity);
            }
            if (!$adjusted) {
                throw new RuntimeException('No se pudo ajustar el stock durante el reconteo.');
            }

            $okMovement = $this->inventoryMovementModel->create(
                $productId,
                $userId,
                $quantity,
                $movementType,
                $reason
            );
            if (!$okMovement) {
                throw new RuntimeException('No se pudo registrar el movimiento de reconteo.');
            }

            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'adjust',
                'inventory',
                $productId,
                "Reconteo aplicado. Diferencia: {$difference}"
            );

            $conn->commit();
            $success = 'Reconteo aplicado correctamente. Se registró movimiento de ' . strtoupper($movementType) . '.';
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'Error al ajustar inventario: ' . $e->getMessage();
        }

        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/inventario/recount.php';
    }

    /**************************************************************************
     * COMPRAS / ABASTECIMIENTO
     **************************************************************************/
    public function listPurchases() {
        $compras = $this->purchaseModel->getAll(300);
        require_once __DIR__ . '/../views/admin/compras/list_purchases.php';
    }

    public function addPurchaseForm() {
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/compras/add_purchase.php';
    }

    public function storePurchase() {
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
        $supplier = trim($_POST['supplier'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $userId = (int)$_SESSION['user_id'];

        $error = '';
        if ($productId <= 0 || $quantity <= 0 || $supplier === '') {
            $error = 'Producto, cantidad y proveedor son obligatorios.';
        }
        $producto = $this->productModel->findById($productId);
        if (!$producto) {
            $error = 'El producto seleccionado no existe.';
        }

        if (!empty($error)) {
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/compras/add_purchase.php';
            return;
        }

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            $adjusted = $this->productModel->increaseStock($productId, $quantity);
            if (!$adjusted) {
                throw new RuntimeException('No se pudo aumentar stock.');
            }

            $saved = $this->purchaseModel->create($productId, $userId, $quantity, $supplier, $notes, $price);
            if (!$saved) {
                throw new RuntimeException('No se pudo guardar la compra. Verifica tabla purchases en BD.');
            }

            $movementReason = 'Compra proveedor: ' . $supplier;
            if ($notes !== '') {
                $movementReason .= ' - ' . $notes;
            }
            $okMovement = $this->inventoryMovementModel->create(
                $productId,
                $userId,
                $quantity,
                'ingreso',
                $movementReason
            );
            if (!$okMovement) {
                throw new RuntimeException('No se pudo registrar el ingreso en Kardex.');
            }

            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'create',
                'purchase',
                null,
                "Compra registrada. Proveedor: {$supplier}, Cantidad: {$quantity}"
            );

            $conn->commit();
            header('Location: index.php?controller=admin&action=listPurchases');
            exit;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'No se pudo registrar la compra: ' . $e->getMessage();
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/admin/compras/add_purchase.php';
            return;
        }
    }

    public function editPurchaseForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $compra = $this->purchaseModel->getById($id);
        if (!$compra) {
            header('Location: index.php?controller=admin&action=listPurchases&error=Compra no encontrada');
            exit;
        }
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/admin/compras/edit_purchase.php';
    }

    public function updatePurchase() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
        $supplier = trim($_POST['supplier'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $error = '';
        if ($productId <= 0 || $quantity <= 0 || $supplier === '') {
            $error = 'Producto, cantidad y proveedor son obligatorios.';
        }
        $producto = $this->productModel->findById($productId);
        if (!$producto) {
            $error = 'El producto seleccionado no existe.';
        }
        $compra = $this->purchaseModel->getById($id);
        if (!$compra) {
            $error = 'Compra no encontrada.';
        }

        if (!empty($error)) {
            $productos = $this->productModel->getAll();
            $compra = $this->purchaseModel->getById($id);
            require_once __DIR__ . '/../views/admin/compras/edit_purchase.php';
            return;
        }

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            // Ajustar stock: restar cantidad anterior y sumar nueva
            $adjusted1 = $this->productModel->decreaseStock($compra->product_id, $compra->quantity);
            if (!$adjusted1) {
                throw new RuntimeException('No se pudo ajustar stock anterior.');
            }
            $adjusted2 = $this->productModel->increaseStock($productId, $quantity);
            if (!$adjusted2) {
                throw new RuntimeException('No se pudo ajustar stock nuevo.');
            }

            $updated = $this->purchaseModel->update($id, $productId, $quantity, $supplier, $notes, $price);
            if (!$updated) {
                throw new RuntimeException('No se pudo actualizar la compra.');
            }

            // Actualizar movimiento de inventario (simplificado: eliminar y crear nuevo)
            // Nota: En producción, mejor actualizar el movimiento existente
            $this->inventoryMovementModel->deleteByNotes("Compra ID: {$id}");
            $movementReason = 'Compra actualizada proveedor: ' . $supplier;
            if ($notes !== '') {
                $movementReason .= ' - ' . $notes;
            }
            $okMovement = $this->inventoryMovementModel->create(
                $productId,
                (int)$_SESSION['user_id'],
                $quantity,
                'ingreso',
                $movementReason
            );
            if (!$okMovement) {
                throw new RuntimeException('No se pudo registrar el ingreso en Kardex.');
            }

            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'update',
                'purchase',
                $id,
                "Compra actualizada. Proveedor: {$supplier}, Cantidad: {$quantity}"
            );

            $conn->commit();
            header('Location: index.php?controller=admin&action=listPurchases&updated=1');
            exit;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'No se pudo actualizar la compra: ' . $e->getMessage();
            $productos = $this->productModel->getAll();
            $compra = $this->purchaseModel->getById($id);
            require_once __DIR__ . '/../views/admin/compras/edit_purchase.php';
            return;
        }
    }

    public function deletePurchase() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $compra = $this->purchaseModel->getById($id);
        if (!$compra) {
            header('Location: index.php?controller=admin&action=listPurchases&error=Compra no encontrada');
            exit;
        }

        $conn = $this->saleModel->getConnection();
        try {
            $conn->beginTransaction();

            // Ajustar stock: restar cantidad
            $adjusted = $this->productModel->decreaseStock($compra->product_id, $compra->quantity);
            if (!$adjusted) {
                throw new RuntimeException('No se pudo ajustar stock.');
            }

            $deleted = $this->purchaseModel->delete($id);
            if (!$deleted) {
                throw new RuntimeException('No se pudo eliminar la compra.');
            }

            // Eliminar movimiento de inventario
            $this->inventoryMovementModel->deleteByNotes("Compra ID: {$id}");

            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'delete',
                'purchase',
                $id,
                "Compra eliminada. Proveedor: {$compra->supplier}, Cantidad: {$compra->quantity}"
            );

            $conn->commit();
            header('Location: index.php?controller=admin&action=listPurchases&deleted=1');
            exit;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            header('Location: index.php?controller=admin&action=listPurchases&error=' . urlencode('No se pudo eliminar: ' . $e->getMessage()));
            exit;
        }
    }

    /**************************************************************************
     * ORDENES DE TRABAJO
     **************************************************************************/
    public function listWorkOrders() {
        $ordenes = $this->workOrderModel->getAll(300);
        require_once __DIR__ . '/../views/admin/ordenes/list_work_orders.php';
    }

    public function addWorkOrderForm() {
        $ventas = $this->saleModel->getAllSales();
        require_once __DIR__ . '/../views/admin/ordenes/add_work_order.php';
    }

    public function storeWorkOrder() {
        $clientName = trim($_POST['client_name'] ?? '');
        $serviceType = trim($_POST['service_type'] ?? '');
        $technicianName = trim($_POST['technician_name'] ?? '');
        $materialsUsed = trim($_POST['materials_used'] ?? '');
        $status = trim($_POST['status'] ?? 'pendiente');
        $saleId = isset($_POST['sale_id']) && $_POST['sale_id'] !== '' ? (int)$_POST['sale_id'] : null;
        $notes = trim($_POST['notes'] ?? '');
        $userId = (int)$_SESSION['user_id'];

        $allowedStatuses = ['pendiente', 'en_proceso', 'finalizado'];
        $error = '';
        if ($clientName === '' || $serviceType === '' || $technicianName === '' || !in_array($status, $allowedStatuses, true)) {
            $error = 'Cliente, tipo de servicio, técnico y estado válido son obligatorios.';
        }
        if ($saleId !== null) {
            $venta = $this->saleModel->findByIdWithDetails($saleId);
            if (!$venta) {
                $error = 'La venta vinculada no existe.';
            }
        }

        if (!empty($error)) {
            $ventas = $this->saleModel->getAllSales();
            require_once __DIR__ . '/../views/admin/ordenes/add_work_order.php';
            return;
        }

        $saved = $this->workOrderModel->create(
            $clientName,
            $serviceType,
            $technicianName,
            $materialsUsed,
            $status,
            $saleId,
            $notes,
            $userId
        );
        if (!$saved) {
            $error = 'No se pudo registrar la orden de trabajo. Verifica que la tabla work_orders exista (ejecuta scripts/tesis_upgrade.sql).';
            $ventas = $this->saleModel->getAllSales();
            require_once __DIR__ . '/../views/admin/ordenes/add_work_order.php';
            return;
        }

        $this->auditLogModel->create(
            (int)$_SESSION['user_id'],
            'create',
            'work_order',
            null,
            "Orden de trabajo registrada para cliente {$clientName}"
        );

        header('Location: index.php?controller=admin&action=listWorkOrders');
        exit;
    }

    public function updateWorkOrderStatus() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = trim($_POST['status'] ?? '');
        $allowedStatuses = ['pendiente', 'en_proceso', 'finalizado'];

        if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
            $_SESSION['error_work_order'] = 'Datos inválidos para actualizar estado.';
            header('Location: index.php?controller=admin&action=listWorkOrders');
            exit;
        }

        $updated = $this->workOrderModel->updateStatus($id, $status);
        if (!$updated) {
            $_SESSION['error_work_order'] = 'No se pudo actualizar el estado de la orden.';
        } else {
            $this->auditLogModel->create(
                (int)$_SESSION['user_id'],
                'update',
                'work_order',
                $id,
                "Estado actualizado a {$status}"
            );
            $_SESSION['success_work_order'] = 'Estado de la orden actualizado.';
        }
        header('Location: index.php?controller=admin&action=listWorkOrders');
        exit;
    }



    public function profile() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        require_once __DIR__ . '/../views/admin/profile.php';
    }

    public function updateProfile() {
        $userId = $_SESSION['user_id'];
        
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validaciones básicas
        if (empty($username) || empty($fullName) || empty($email)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios (excepto contraseña).';
            header('Location: index.php?controller=admin&action=profile');
            exit;
        }

        // Actualizar información básica
        $updated = $this->userModel->updateProfile($userId, $username, $fullName, $email);
        
        if (!$updated) {
            $_SESSION['error'] = 'No se pudo actualizar el perfil.';
            header('Location: index.php?controller=admin&action=profile');
            exit;
        }

        // Actualizar sesión con nuevo username
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;

        // Si desea cambiar contraseña
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Las contraseñas nuevas no coinciden.';
                header('Location: index.php?controller=admin&action=profile');
                exit;
            }

            // Verificar contraseña actual
            $user = $this->userModel->findById($userId);
            if (!$this->userModel->verifyPassword($currentPassword, $user->password)) {
                $_SESSION['error'] = 'La contraseña actual es incorrecta.';
                header('Location: index.php?controller=admin&action=profile');
                exit;
            }

            // Actualizar contraseña
            $this->userModel->updatePassword($userId, $newPassword);
        }

        $_SESSION['success'] = 'Perfil actualizado exitosamente.';
        header('Location: index.php?controller=admin&action=profile');
        exit;
    }
}

