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

    public function create(int $productId, int $userId, int $quantity, string $supplier, string $notes = ''): bool {
        if (!$this->existsTable()) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->table} (product_id, user_id, quantity, supplier, notes)
            VALUES (:product_id, :user_id, :quantity, :supplier, :notes)
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':supplier', $supplier);
            $stmt->bindParam(':notes', $notes);
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
}
