<?php
require_once __DIR__ . '/../config/database.php';

class Purchase {
    private $conn;
    private $table = 'purchases';
    private $tableExists = null;

    public function __construct() {
        $this->conn = Database::connect();
    }

    private function existsTable(): bool {
        if ($this->tableExists !== null) {
            return $this->tableExists;
        }
        try {
            $stmt = $this->conn->query("SHOW TABLES LIKE '{$this->table}'");
            $this->tableExists = (bool)$stmt->fetch(PDO::FETCH_NUM);
        } catch (Throwable $e) {
            $this->tableExists = false;
        }
        return $this->tableExists;
    }

    public function create(int $productId, int $userId, int $quantity, string $supplier, string $notes = '', float $price = 0.0): bool {
        if (!$this->existsTable()) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->table} (product_id, user_id, quantity, supplier, notes, price)
            VALUES (:product_id, :user_id, :quantity, :supplier, :notes, :price)
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':supplier', $supplier);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':price', $price);
            return $stmt->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getAll(int $limit = 200): array {
        if (!$this->existsTable()) {
            return [];
        }
        $sql = "
            SELECT
                pu.id,
                pu.product_id,
                p.name AS product_name,
                pu.user_id,
                u.full_name AS user_name,
                pu.quantity,
                pu.price,
                pu.supplier,
                pu.notes,
                pu.purchase_date
            FROM {$this->table} pu
            JOIN products p ON p.id = pu.product_id
            JOIN users u ON u.id = pu.user_id
            ORDER BY pu.purchase_date DESC, pu.id DESC
            LIMIT :limit
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getTotalPurchasesByDate(string $desde, string $hasta): float {
        if (!$this->existsTable()) {
            return 0.0;
        }
        // Asumiendo que hay un campo total_price en purchases, si no, calcular quantity * unit_price de products
        $sql = "
            SELECT COALESCE(SUM(pu.quantity * p.price), 0) AS total
            FROM {$this->table} pu
            JOIN products p ON p.id = pu.product_id
            WHERE DATE(pu.purchase_date) BETWEEN :desde AND :hasta
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':desde', $desde);
            $stmt->bindParam(':hasta', $hasta);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            return (float) $row->total;
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    public function countPurchases(): int {
        if (!$this->existsTable()) {
            return 0;
        }
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            return (int) $row->total;
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function getById(int $id): ?object {
        if (!$this->existsTable()) {
            return null;
        }
        $sql = "
            SELECT
                pu.*,
                p.name AS product_name
            FROM {$this->table} pu
            JOIN products p ON p.id = pu.product_id
            WHERE pu.id = :id
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function update(int $id, int $productId, int $quantity, string $supplier, string $notes = '', float $price = 0.0): bool {
        if (!$this->existsTable()) {
            return false;
        }
        $sql = "
            UPDATE {$this->table}
            SET product_id = :product_id,
                quantity = :quantity,
                price = :price,
                supplier = :supplier,
                notes = :notes
            WHERE id = :id
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':supplier', $supplier);
            $stmt->bindParam(':notes', $notes);
            return $stmt->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function delete(int $id): bool {
        if (!$this->existsTable()) {
            return false;
        }
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Throwable $e) {
            return false;
        }
    }
}
