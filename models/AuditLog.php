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

    /**
     * getFiltered
     * Returns rows and total count applying optional filters and pagination.
     * Filters supported: user (partial name), action, from (YYYY-MM-DD), to (YYYY-MM-DD)
     */
    public function getFiltered(array $filters = [], int $limit = 20, int $offset = 0): array {
        if (!$this->existsTable()) {
            return ['rows' => [], 'total' => 0];
        }

        $where = [];
        $params = [];

        if (!empty($filters['user'])) {
            $where[] = 'u.full_name LIKE :user';
            $params[':user'] = '%' . $filters['user'] . '%';
        }
        if (!empty($filters['action'])) {
            $where[] = 'a.action = :action';
            $params[':action'] = $filters['action'];
        }
        if (!empty($filters['from'])) {
            $where[] = "DATE(a.created_at) >= :from";
            $params[':from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = "DATE(a.created_at) <= :to";
            $params[':to'] = $filters['to'];
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        try {
            // Total count
            $countSql = "SELECT COUNT(*) AS cnt FROM {$this->table} a LEFT JOIN users u ON u.id = a.user_id {$whereSql}";
            $countStmt = $this->conn->prepare($countSql);
            foreach ($params as $k => $v) { $countStmt->bindValue($k, $v); }
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn(0);

            // Rows
            $sql = "SELECT a.*, u.full_name AS user_name FROM {$this->table} a LEFT JOIN users u ON u.id = a.user_id {$whereSql} ORDER BY a.created_at DESC, a.id DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

            return ['rows' => $rows, 'total' => $total];
        } catch (Throwable $e) {
            return ['rows' => [], 'total' => 0];
        }
    }
}
