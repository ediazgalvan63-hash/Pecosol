<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $this->conn = Database::connect();
        $this->ensureDefaultUsers();
    }

    /**
     * findByUsername($username)
     * - Busca en la tabla 'users' un usuario cuyo 'username' coincida con $username.
     * - Devuelve un objeto (PDO::FETCH_OBJ) con las columnas de la fila, o false si no existe.
     */
    public function findByUsername(string $username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * findById($id)
     * - Busca un usuario por su ID.
     * - Devuelve un objeto (PDO::FETCH_OBJ) con las columnas de la fila, o false si no existe.
     */
    public function findById(int $id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * getAllEmployees()
     * - Devuelve todos los usuarios que no son admin, ordenados por ID asc.
     * - Incluye roles de empleados, comercial, gerencia, logística, finanzas y estratégico.
     */
    public function getAllEmployees(): array {
        $sql  = "SELECT * FROM {$this->table} WHERE role != 'admin' ORDER BY id ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * getAdminAndCommercial()
     * - Devuelve solo usuarios con rol 'admin' o 'comercial', ordenados por ID asc.
     * - Útil para seleccionar quién registra una venta.
     */
    public function getAdminAndCommercial(): array {
        $sql  = "SELECT * FROM {$this->table} WHERE role IN ('admin', 'comercial') ORDER BY id ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * create($username, $passwordPlain, $fullName, $email, $role)
     * - Crea un nuevo usuario en la tabla 'users'.
     * - Genera un hash BCRYPT de la contraseña en texto plano.
     * - Devuelve true si la inserción fue exitosa, o false en caso contrario.
     */
    public function create(
        string $username,
        string $passwordPlain,
        string $fullName,
        string $email,
        string $role
    ): bool {
        $hashedPassword = password_hash($passwordPlain, PASSWORD_BCRYPT);

        $sql = "
            INSERT INTO {$this->table} 
              (username, password, full_name, email, role)
            VALUES 
              (:username, :password, :full_name, :email, :role)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username',  $username);
        $stmt->bindParam(':password',  $hashedPassword);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':email',     $email);
        $stmt->bindParam(':role',      $role);
        return $stmt->execute();
    }

    /**
     * updateProfile($id, $username, $fullName, $email)
     * - Actualiza username, full_name, email de un usuario.
     * - Devuelve true si exitoso, false en caso contrario.
     */
    public function updateProfile(int $id, string $username, string $fullName, string $email): bool {
        $sql = "
            UPDATE {$this->table}
            SET username = :username,
                full_name = :full_name,
                email = :email
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * updatePassword($id, $passwordPlain)
     * - Cambia la contraseña de un usuario existente.
     * - Genera un nuevo hash BCRYPT para la contraseña en texto plano.
     * - Devuelve true si la actualización fue exitosa, o false en caso contrario.
     */
    public function updatePassword(int $id, string $passwordPlain): bool {
        $hashedPassword = password_hash($passwordPlain, PASSWORD_BCRYPT);

        $sql = "
            UPDATE {$this->table}
            SET password = :password
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id',       $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * delete($id)
     * - Elimina un usuario por su ID.
     * - Devuelve true si la eliminación fue exitosa, o false en caso contrario.
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * countSalesByUser($userId)
     * - Devuelve la cantidad de ventas que tiene un usuario específico.
     * - Útil para evitar eliminar empleado que ya registró ventas.
     */
    public function countSalesByUser(int $userId): int {
        // Asume que existe la tabla 'sales' con columna user_id
        $sql = "
            SELECT COUNT(*) AS total
            FROM sales
            WHERE user_id = :user_id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int) $row->total;
    }

    /**
     * countInventoryMovementsByUser($userId)
     * - Devuelve la cantidad de movimientos de inventario relacionados con un usuario.
     * - Previene la eliminación de empleados con registros en stock_movements.
     */
    public function countInventoryMovementsByUser(int $userId): int {
        $sql = "
            SELECT COUNT(*) AS total
            FROM stock_movements
            WHERE user_id = :user_id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int) $row->total;
    }

    /**
     * verifyPassword($plainPassword, $hashedPassword)
     * - Compara la contraseña en texto plano con el hash almacenado usando password_verify.
     * - Devuelve true si coinciden, false en caso contrario.
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool {
        // Compatibilidad con hashes bcrypt actuales
        if (password_verify($plainPassword, $hashedPassword)) {
            return true;
        }

        // Compatibilidad legacy: algunas migraciones antiguas guardaron texto plano
        return hash_equals($hashedPassword, $plainPassword);
    }

    /**
     * ensureDefaultUsers()
     * - Si la tabla users está vacía en un despliegue nuevo (p.ej. Railway),
     *   crea usuarios base para permitir acceso inmediato.
     */
    private function ensureDefaultUsers(): void {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM {$this->table}");
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $totalUsers = (int)($row->total ?? 0);

            if ($totalUsers > 0) {
                return;
            }

            $defaultPassword = password_hash('123456', PASSWORD_BCRYPT);
            $insert = $this->conn->prepare("
                INSERT INTO {$this->table} (username, password, full_name, email, role)
                VALUES (:username, :password, :full_name, :email, :role)
            ");

            $users = [
                ['admin', 'Administrador Principal', 'admin@pecosol.com', 'admin'],
                ['empleado1', 'Empleado Uno', 'empleado1@pecosol.com', 'employee'],
                ['Ale', 'Ale Peres', 'ale@pecosol.com', 'employee'],
            ];

            foreach ($users as $user) {
                $insert->execute([
                    ':username' => $user[0],
                    ':password' => $defaultPassword,
                    ':full_name' => $user[1],
                    ':email' => $user[2],
                    ':role' => $user[3],
                ]);
            }
        } catch (Throwable $e) {
            // No interrumpir el flujo de login por fallo de seeding
        }
    }

    /**
     * updateUserProfile($id, $username, $fullName, $email)
     * - Actualiza datos del perfil de usuario (username, full_name, email).
     * - Devuelve true si la actualización fue exitosa, o false en caso contrario.
     */
    public function updateUserProfile(int $id, string $username, string $fullName, string $email): bool {
        $sql = "
            UPDATE {$this->table}
            SET username  = :username,
                full_name = :full_name,
                email     = :email
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username',  $username);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':email',     $email);
        $stmt->bindParam(':id',        $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
