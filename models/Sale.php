<?php
// models/Sale.php

require_once __DIR__ . '/../config/database.php';

class Sale {
    private $conn;
    private $table = 'sales';
    private $hasClientNameColumn = null;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    private function hasClientNameColumn(): bool {
        if ($this->hasClientNameColumn !== null) {
            return $this->hasClientNameColumn;
        }

        try {
            // En algunos hosts/roles (producción) SHOW COLUMNS puede estar restringido.
            // INFORMATION_SCHEMA suele ser más compatible.
            $sql = "
                SELECT 1
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = :table_name
                  AND COLUMN_NAME = 'client_name'
                LIMIT 1
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':table_name' => $this->table]);
            $this->hasClientNameColumn = (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            $this->hasClientNameColumn = false;
        }

        return $this->hasClientNameColumn;
    }

    /**
     * createSale(...)
     * - Inserta una nueva venta en la tabla `sales`.
     * - Devuelve el ID de la venta recién creada, o false en caso de error.
     */
    public function createSale(
        int $userId,
        int $productId,
        int $quantity,
        float $unitPrice,
        float $totalPrice,
        string $clientName = '',
        string $description = '',
        ?string $saleDate = null
    ) {
        if ($saleDate === null) {
            $tz = defined('DB_TIMEZONE') ? DB_TIMEZONE : (getenv('DB_TIMEZONE') ?: 'UTC');
            $dt = new DateTime('now', new DateTimeZone($tz));
            $saleDate = $dt->format('Y-m-d H:i:s');
        }

        if ($this->hasClientNameColumn()) {
            $sql = "
                INSERT INTO {$this->table}
                  (user_id, product_id, quantity, unit_price, total_price, client_name, description, sale_date)
                VALUES
                  (:user_id, :product_id, :quantity, :unit_price, :total_price, :client_name, :description, :sale_date)
            ";
        } else {
            $sql = "
                INSERT INTO {$this->table}
                  (user_id, product_id, quantity, unit_price, total_price, description, sale_date)
                VALUES
                  (:user_id, :product_id, :quantity, :unit_price, :total_price, :description, :sale_date)
            ";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id',    $userId,     PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId,  PDO::PARAM_INT);
        $stmt->bindParam(':quantity',   $quantity,   PDO::PARAM_INT);
        $stmt->bindParam(':unit_price', $unitPrice);
        $stmt->bindParam(':total_price',$totalPrice);
        if ($this->hasClientNameColumn()) {
            $stmt->bindParam(':client_name',$clientName);
        }
        $stmt->bindParam(':description',$description);
        $stmt->bindParam(':sale_date',   $saleDate);
        $success = $stmt->execute();
        if ($success) {
            return (int) $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * findByIdWithDetails($saleId)
     * - Recupera una venta por su ID, incluyendo nombre de usuario y nombre de producto.
     * - Devuelve un objeto o false si no existe.
     */
    public function findByIdWithDetails(int $saleId) {
        $sql = "
            SELECT 
              s.*,
              u.full_name AS user_name,
              p.name      AS product_name,
              p.stock     AS current_stock
            FROM {$this->table} AS s
            JOIN users    AS u ON s.user_id    = u.id
            JOIN products AS p ON s.product_id = p.id
            WHERE s.id = :sale_id
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * getAllSales()
     * - Recupera todas las ventas de todos los usuarios,
     *   incluyendo nombre de usuario y nombre de producto, ordenadas por fecha descendente.
     * - Devuelve un arreglo de objetos.
     */
    public function getAllSales(?string $startDate = null, ?string $endDate = null): array {
        $clientNameSelect = $this->hasClientNameColumn()
            ? "COALESCE(NULLIF(TRIM(s.client_name), ''), 'Cliente General') AS client_name"
            : "'Cliente General' AS client_name";
        $sql = "
            SELECT 
              s.id,
              s.user_id,
              u.full_name    AS user_name,
              s.product_id,
              p.name         AS product_name,
              s.quantity,
              s.unit_price,
              s.total_price,
              {$clientNameSelect},
              s.description,
              s.sale_date
            FROM {$this->table} AS s
            JOIN users    AS u ON s.user_id    = u.id
            JOIN products AS p ON s.product_id = p.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($startDate)) {
            $sql .= " AND DATE(s.sale_date) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $sql .= " AND DATE(s.sale_date) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        $sql .= " ORDER BY s.sale_date DESC";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * updateSale(...)
     * - Actualiza una venta existente.
     * - Devuelve true si tuvo éxito, false en caso contrario.
     */
    public function updateSale(
        int $saleId,
        int $userId,
        int $productId,
        int $quantity,
        float $unitPrice,
        float $totalPrice,
        string $clientName,
        string $description
    ): bool {
        if ($this->hasClientNameColumn()) {
            $sql = "
                UPDATE {$this->table}
                SET user_id     = :user_id,
                    product_id  = :product_id,
                    quantity    = :quantity,
                    unit_price  = :unit_price,
                    total_price = :total_price,
                    client_name = :client_name,
                    description = :description
                WHERE id = :sale_id
            ";
        } else {
            $sql = "
                UPDATE {$this->table}
                SET user_id     = :user_id,
                    product_id  = :product_id,
                    quantity    = :quantity,
                    unit_price  = :unit_price,
                    total_price = :total_price,
                    description = :description
                WHERE id = :sale_id
            ";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id',    $userId,      PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId,   PDO::PARAM_INT);
        $stmt->bindParam(':quantity',   $quantity,    PDO::PARAM_INT);
        $stmt->bindParam(':unit_price', $unitPrice);
        $stmt->bindParam(':total_price',$totalPrice);
        if ($this->hasClientNameColumn()) {
            $stmt->bindParam(':client_name',$clientName);
        }
        $stmt->bindParam(':description',$description);
        $stmt->bindParam(':sale_id',    $saleId,      PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * deleteSale($saleId)
     * - Elimina una venta por su ID.
     * - Devuelve true si tuvo éxito, false en caso contrario.
     */
    public function deleteSale(int $saleId): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :sale_id");
        $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * registerStockMovement(...)
     * - Inserta un registro en `stock_movements`.
     * - Devuelve true si tuvo éxito, false en caso contrario.
     */
    public function registerStockMovement(
        int $productId,
        int $userId,
        int $quantityChange,
        string $movementType,
        string $notes = ''
    ): bool {
        $sql = "
            INSERT INTO stock_movements
              (product_id, user_id, quantity_change, movement_type, notes)
            VALUES
              (:product_id, :user_id, :quantity_change, :movement_type, :notes)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id',     $productId,      PDO::PARAM_INT);
        $stmt->bindParam(':user_id',        $userId,         PDO::PARAM_INT);
        $stmt->bindParam(':quantity_change',$quantityChange, PDO::PARAM_INT);
        $stmt->bindParam(':movement_type',  $movementType);
        $stmt->bindParam(':notes',          $notes);
        return $stmt->execute();
    }

    public function updateSaleMovementNoteBySaleId(int $saleId, string $newNotes): bool {
        $defaultNote = "Venta ID: {$saleId}";
        $editionNote = "Edición Venta ID: {$saleId}";

        $sql = "
            UPDATE stock_movements
            SET notes = :notes
            WHERE movement_type = 'salida'
              AND notes IN (:default_note, :edition_note)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':notes', $newNotes);
        $stmt->bindParam(':default_note', $defaultNote);
        $stmt->bindParam(':edition_note', $editionNote);
        return $stmt->execute();
    }

    /**
     * getTotalSalesByDate($desde, $hasta)
     * - Devuelve la suma de total_price de todas las ventas entre dos fechas.
     * - $desde y $hasta en formato 'YYYY-MM-DD'.
     * - Retorna un float (0 si no hay registros).
     */
    public function getTotalSalesByDate(string $desde, string $hasta): float {
        $sql = "
            SELECT COALESCE(SUM(total_price), 0) AS total
            FROM {$this->table}
            WHERE DATE(sale_date) BETWEEN :desde AND :hasta
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':desde', $desde);
        $stmt->bindParam(':hasta', $hasta);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (float) $row->total;
    }

    /**
     * getTotalSalesByDateAndUser($desde, $hasta, $userId)
     * - Suma de total_price entre dos fechas filtrando por un usuario específico.
     * - Devuelve un float (0 si no hay registros).
     */
    public function getTotalSalesByDateAndUser(string $desde, string $hasta, int $userId): float {
        $sql = "
            SELECT COALESCE(SUM(total_price), 0) AS total
            FROM {$this->table}
            WHERE DATE(sale_date) BETWEEN :desde AND :hasta
              AND user_id = :user_id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':desde',   $desde);
        $stmt->bindParam(':hasta',   $hasta);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (float) $row->total;
    }

    /**
     * getLastSales($limit = 5)
     * - Recupera las últimas $limit ventas de todos los usuarios, ordenadas por fecha descendente.
     * - Devuelve un array de objetos con columnas de sales, más user_name y product_name vía JOIN.
     */
    public function getLastSales(int $limit = 5): array {
        $clientNameSelect = $this->hasClientNameColumn()
            ? "COALESCE(NULLIF(TRIM(s.client_name), ''), 'Cliente General') AS client_name"
            : "'Cliente General' AS client_name";
        $sql = "
            SELECT 
              s.id,
              s.user_id,
              u.full_name    AS user_name,
              s.product_id,
              p.name         AS product_name,
              s.quantity,
              s.unit_price,
              s.total_price,
              {$clientNameSelect},
              s.sale_date
            FROM {$this->table} AS s
            JOIN users    AS u ON s.user_id    = u.id
            JOIN products AS p ON s.product_id = p.id
            ORDER BY s.sale_date DESC
            LIMIT :limit
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * getLastSalesByUser($userId, $limit = 5)
     * - Recupera las últimas $limit ventas de un usuario específico, ordenadas por fecha descendente.
     * - Devuelve un array de objetos con columnas de sales y el nombre del producto vía JOIN.
     */
    public function getLastSalesByUser(int $userId, int $limit = 5): array {
        $clientNameSelect = $this->hasClientNameColumn()
            ? "COALESCE(NULLIF(TRIM(s.client_name), ''), 'Cliente General') AS client_name"
            : "'Cliente General' AS client_name";
        $sql = "
            SELECT 
              s.id,
              s.user_id,
              u.full_name    AS user_name,
              s.product_id,
              p.name         AS product_name,
              s.quantity,
              s.unit_price,
              s.total_price,
              {$clientNameSelect},
              s.sale_date
            FROM {$this->table} AS s
            JOIN users    AS u ON s.user_id    = u.id
            JOIN products AS p ON s.product_id = p.id
            WHERE s.user_id = :user_id
            ORDER BY s.sale_date DESC
            LIMIT :limit
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit',   $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * getAllSalesByUser($userId)
     * - Devuelve todas las ventas de un empleado específico, ordenadas por fecha descendente.
     * - Útil para el listado completo si se requiere.
     */
    public function getAllSalesByUser(int $userId): array {
        $clientNameSelect = $this->hasClientNameColumn()
            ? "COALESCE(NULLIF(TRIM(s.client_name), ''), 'Cliente General') AS client_name"
            : "'Cliente General' AS client_name";
        $sql = "
            SELECT 
              s.id,
              s.product_id,
              p.name         AS product_name,
              s.quantity,
              s.unit_price,
              s.total_price,
              {$clientNameSelect},
              s.sale_date
            FROM {$this->table} AS s
            JOIN products AS p ON s.product_id = p.id
            WHERE s.user_id = :user_id
            ORDER BY s.sale_date DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * countSalesByProduct($productId)
     * - Devuelve la cantidad de ventas que tienen este producto en sales.
     * - Útil para saber si un producto puede eliminarse o no.
     */
    public function countSalesByProduct(int $productId): int {
        $sql = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE product_id = :product_id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int) $row->total;
    }

    /**
     * countSalesByUser($userId)
     * - Devuelve la cantidad de ventas que tiene un usuario específico.
     * - Útil para evitar eliminar un empleado que ya registró ventas.
     */
    public function countSalesByUser(int $userId): int {
        $sql = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE user_id = :user_id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int) $row->total;
    }
}

