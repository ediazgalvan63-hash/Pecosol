<?php
require_once __DIR__ . '/../config/database.php';

class AuditLog {
    private $conn;
    private $table = 'audit_logs';
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

    public function create(int $userId, string $action, string $entity, ?int $entityId, string $details = ''): bool {
        if (!$this->existsTable()) {
            return false;
        }

        $sql = "
            INSERT INTO {$this->table} (user_id, action, entity, entity_id, details)
            VALUES (:user_id, :action, :entity, :entity_id, :details)
        ";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':entity', $entity);
            if ($entityId === null) {
                $stmt->bindValue(':entity_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':entity_id', $entityId, PDO::PARAM_INT);
            }
            $stmt->bindParam(':details', $details);
            return $stmt->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getRecent(int $limit = 100): array {
        if (!$this->existsTable()) {
            return [];
        }

        $sql = "
            SELECT a.*, u.full_name AS user_name
            FROM {$this->table} a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.created_at DESC, a.id DESC
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
