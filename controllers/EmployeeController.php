<?php
// controllers/EmployeeController.php

class EmployeeController {
    private $saleModel;
    private $productModel;

    public function __construct() {
        // 1) Arrancar sesión si no exista
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2) Si no hay usuario logueado, redirigir a login
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 3) Verificar que el rol sea 'employee'
        if ($_SESSION['role'] !== 'employee') {
            // Si no es empleado, enviarlo al dashboard de admin
            header('Location: index.php?controller=dashboard&action=adminHome');
            exit;
        }

        // 4) Instanciar modelos necesarios
        require_once __DIR__ . '/../models/Sale.php';
        require_once __DIR__ . '/../models/Product.php';
        $this->saleModel    = new Sale();
        $this->productModel = new Product();
    }

    /**
     * addSaleForm()
     * - Muestra el formulario para que el empleado agregue una venta.
     * - Necesita la lista de productos (filtrados por stock > 0 en la vista).
     */
    public function addSaleForm() {
        $productos = $this->productModel->getAll();
        require_once __DIR__ . '/../views/employee/ventas/add_sale.php';
    }

    /**
     * storeSale()
     * - Recibe el POST del formulario, valida datos, crea la venta,
     *   actualiza stock y registra movimiento en stock_movements.
     */
    public function storeSale() {
        $productId   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity    = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 0;
        $description = trim($_POST['description'] ?? '');

        $error = '';
        if ($productId <= 0 || $quantity <= 0) {
            $error = 'Debe seleccionar un producto y una cantidad válida.';
        } else {
            $producto = $this->productModel->findById($productId);
            if (!$producto) {
                $error = 'El producto seleccionado no existe.';
            } elseif ($quantity > $producto->stock) {
                $error = 'La cantidad solicitada supera el stock disponible.';
            }
        }

        if (!empty($error)) {
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/employee/ventas/add_sale.php';
            return;
        }

        $unitPrice  = (float) $producto->price;
        $totalPrice = $unitPrice * $quantity;
        $userId     = $_SESSION['user_id'];

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
                $description
            );
            if (!$newSaleId) {
                throw new RuntimeException('No se pudo registrar la venta.');
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
                throw new RuntimeException('No se pudo registrar el movimiento de stock.');
            }

            $conn->commit();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'No se pudo completar la venta: ' . $e->getMessage();
            $productos = $this->productModel->getAll();
            require_once __DIR__ . '/../views/employee/ventas/add_sale.php';
            return;
        }

        header('Location: index.php?controller=dashboard&action=employeeHome');
        exit;
    }

    /**
     * listSalesEmployee()
     * - Muestra todas las ventas que este empleado ha registrado.
     */
    public function listSalesEmployee() {
        // 1) Asegurar sesión y rol
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 2) Obtener las ventas de este empleado
        $userId = $_SESSION['user_id'];
        $ventas = $this->saleModel->getAllSalesByUser($userId);

        // 3) Cargar la vista CORRECTA dentro de employee/ventas/
        require_once __DIR__ . '/../views/employee/ventas/list_sales.php';
    }

    public function listProductsEmployee() {
        // 1) Validar sesión y rol
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // 2) Obtener todos los productos (podrás filtrarlos por stock en la vista)
        $productos = $this->productModel->getAll();

        // 3) Cargar la vista
        require_once __DIR__ . '/../views/employee/productos/list_products.php';
    }

    /**************************************************************************
     * PERFIL DE USUARIO
     **************************************************************************/

    public function profile() {
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        
        $userId = $_SESSION['user_id'];
        $user = $userModel->findById($userId);
        require_once __DIR__ . '/../views/employee/profile.php';
    }

    public function updateProfile() {
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        
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
            header('Location: index.php?controller=employee&action=profile');
            exit;
        }

        // Actualizar información básica
        $updated = $userModel->updateProfile($userId, $username, $fullName, $email);
        
        if (!$updated) {
            $_SESSION['error'] = 'No se pudo actualizar el perfil.';
            header('Location: index.php?controller=employee&action=profile');
            exit;
        }

        // Actualizar sesión con nuevo username
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;

        // Si desea cambiar contraseña
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Las contraseñas nuevas no coinciden.';
                header('Location: index.php?controller=employee&action=profile');
                exit;
            }

            // Verificar contraseña actual
            $user = $userModel->findById($userId);
            if (!$userModel->verifyPassword($currentPassword, $user->password)) {
                $_SESSION['error'] = 'La contraseña actual es incorrecta.';
                header('Location: index.php?controller=employee&action=profile');
                exit;
            }

            // Actualizar contraseña
            $userModel->updatePassword($userId, $newPassword);
        }

        $_SESSION['success'] = 'Perfil actualizado exitosamente.';
        header('Location: index.php?controller=employee&action=profile');
        exit;
    }
}

