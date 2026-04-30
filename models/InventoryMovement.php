<?php
require_once __DIR__ . '/../config/database.php';

class InventoryMovement {
    private $conn;
    private $table = 'stock_movements';

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function create(
        int $productId,
        int $userId,
        int $quantity,
        string $movementType,
        string $reason
    ): bool {
        $signedQuantity = $movementType === 'salida' ? -abs($quantity) : abs($quantity);

        $sql = "
            INSERT INTO {$this->table} (product_id, user_id, quantity_change, movement_type, notes)
            VALUES (:product_id, :user_id, :quantity_change, :movement_type, :notes)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity_change', $signedQuantity, PDO::PARAM_INT);
        $stmt->bindParam(':movement_type', $movementType);
        $stmt->bindParam(':notes', $reason);
        return $stmt->execute();
    }

    public function getAllWithDetails(int $limit = 200): array {
        $sql = "
            SELECT
                sm.id,
                sm.product_id,
                p.name AS product_name,
                sm.user_id,
                u.full_name AS user_name,
                sm.quantity_change,
                sm.movement_type,
                sm.notes,
                sm.movement_date
            FROM {$this->table} sm
            JOIN products p ON sm.product_id = p.id
            JOIN users u ON sm.user_id = u.id
            ORDER BY sm.movement_date DESC, sm.id DESC
            LIMIT :limit
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getFiltered(
        ?string $startDate,
        ?string $endDate,
        ?int $productId,
        ?string $movementType,
        int $limit = 200
    ): array {
        $sql = "
            SELECT
                sm.id,
                sm.product_id,
                p.name AS product_name,
                sm.user_id,
                u.full_name AS user_name,
                sm.quantity_change,
                sm.movement_type,
                sm.notes,
                sm.movement_date
            FROM {$this->table} sm
            JOIN products p ON sm.product_id = p.id
            JOIN users u ON sm.user_id = u.id
            WHERE 1=1
        ";

        $params = [];
        if (!empty($startDate)) {
            $sql .= " AND DATE(sm.movement_date) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $sql .= " AND DATE(sm.movement_date) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        if (!empty($productId)) {
            $sql .= " AND sm.product_id = :product_id";
            $params[':product_id'] = $productId;
        }
        if (!empty($movementType) && in_array($movementType, ['ingreso', 'salida'], true)) {
            $sql .= " AND sm.movement_type = :movement_type";
            $params[':movement_type'] = $movementType;
        }

        $sql .= " ORDER BY sm.movement_date DESC, sm.id DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getByProduct(int $productId): array {
        $sql = "
            SELECT
                sm.*,
                u.full_name AS user_name
            FROM {$this->table} sm
            JOIN users u ON sm.user_id = u.id
            WHERE sm.product_id = :product_id
            ORDER BY sm.movement_date DESC, sm.id DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSummaryInOut(): array {
        $sql = "
            SELECT
                COALESCE(SUM(CASE WHEN movement_type = 'ingreso' THEN ABS(quantity_change) ELSE 0 END), 0) AS total_entradas,
                COALESCE(SUM(CASE WHEN movement_type = 'salida' THEN ABS(quantity_change) ELSE 0 END), 0) AS total_salidas
            FROM {$this->table}
        ";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return [
            'entradas' => (int)($row->total_entradas ?? 0),
            'salidas' => (int)($row->total_salidas ?? 0),
        ];
    }

    public function countMovementsByDateRange(?string $startDate, ?string $endDate): int {
        $sql = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE 1=1
        ";
        $params = [];
        if (!empty($startDate)) {
            $sql .= " AND DATE(movement_date) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $sql .= " AND DATE(movement_date) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int)($row->total ?? 0);
    }

    public function getLastMovements(int $limit = 8): array {
        return $this->getAllWithDetails($limit);
    }
}
