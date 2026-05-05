<?php
require_once __DIR__ . '/../config/database.php';

class WorkOrder {
    private $conn;
    private $table = 'work_orders';
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

    public function create(
        string $clientName,
        string $serviceType,
        string $technicianName,
        string $materialsUsed,
        string $status,
        ?int $saleId,
        string $notes,
        int $createdBy
    ): bool {
        if (!$this->existsTable()) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->table}
                (client_name, service_type, technician_name, materials_used, status, sale_id, notes, created_by)
            VALUES
                (:client_name, :service_type, :technician_name, :materials_used, :status, :sale_id, :notes, :created_by)
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':client_name', $clientName);
            $stmt->bindParam(':service_type', $serviceType);
            $stmt->bindParam(':technician_name', $technicianName);
            $stmt->bindParam(':materials_used', $materialsUsed);
            $stmt->bindParam(':status', $status);
            if ($saleId === null) {
                $stmt->bindValue(':sale_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':sale_id', $saleId, PDO::PARAM_INT);
            }
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':created_by', $createdBy, PDO::PARAM_INT);
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
                wo.*,
                u.full_name AS created_by_name
            FROM {$this->table} wo
            LEFT JOIN users u ON u.id = wo.created_by
            ORDER BY wo.created_at DESC, wo.id DESC
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

    public function updateStatus(int $id, string $status): bool {
        if (!$this->existsTable()) {
            return false;
        }
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Throwable $e) {
            return false;
        }
    }
}
