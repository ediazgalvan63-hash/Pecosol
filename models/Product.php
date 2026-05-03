<?php
// models/Product.php

require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * getAll()
     * - Devuelve todos los productos como arreglo de objetos.
     * - Ordenados por ID de forma ascendente.
     */
    public function getAll(): array {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * findById($id)
     * - Encuentra un producto por su ID.
     * - Devuelve un objeto o false si no existe.
     */
    public function findById(int $id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * updateStock($productId, $newStock)
     * - Actualiza únicamente la columna `stock` de un producto.
     * - Recibe el ID del producto y la nueva cantidad de stock.
     * - Devuelve true si la actualización fue exitosa, false en caso contrario.
     */
    public function updateStock(int $productId, int $newStock): bool {
        $sql = "
            UPDATE {$this->table}
            SET stock = :stock
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':stock', $newStock, PDO::PARAM_INT);
        $stmt->bindParam(':id',    $productId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function increaseStock(int $productId, int $quantity): bool {
        $sql = "UPDATE {$this->table} SET stock = stock + :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function decreaseStockIfAvailable(int $productId, int $quantity): bool {
        $sql = "
            UPDATE {$this->table}
            SET stock = stock - :quantity
            WHERE id = :id
              AND stock >= :quantity
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * create($name, $description, $price, $stock)
     * - Inserta un nuevo producto en la BD.
     * - Devuelve true si la inserción fue exitosa, false en caso contrario.
     */
    public function create(string $name, string $description, float $price, int $stock, int $stockMinimum = 0): bool {
        $sql = "
            INSERT INTO {$this->table} (name, description, price, stock, stock_minimum)
            VALUES (:name, :description, :price, :stock, :stock_minimum)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price',       $price);
        $stmt->bindParam(':stock',       $stock);
        $stmt->bindParam(':stock_minimum', $stockMinimum, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * update($id, $name, $description, $price, $stock)
     * - Actualiza un producto existente (nombre, descripción, precio, stock).
     * - Devuelve true si la actualización fue exitosa, false en caso contrario.
     */
    public function update(
        int $id,
        string $name,
        string $description,
        float $price,
        int $stock,
        int $stockMinimum = 0
    ): bool {
        $sql = "
            UPDATE {$this->table}
            SET name        = :name,
                description = :description,
                price       = :price,
                stock       = :stock,
                stock_minimum = :stock_minimum
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price',       $price);
        $stmt->bindParam(':stock',       $stock);
        $stmt->bindParam(':stock_minimum', $stockMinimum, PDO::PARAM_INT);
        $stmt->bindParam(':id',          $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * delete($id)
     * - Elimina un producto por su ID.
     * - Devuelve true si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * getTotalStock()
     * - Devuelve la sumatoria de la columna `stock` en la tabla products.
     * - Útil, por ejemplo, en el dashboard de administrador.
     */
    public function getTotalStock(): int {
        $sql = "SELECT COALESCE(SUM(stock), 0) AS total_stock FROM {$this->table}";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int)$row->total_stock;
    }

    public function countProducts(): int {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int)$row->total;
    }

    public function countLowStockProducts(): int {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE stock <= stock_minimum";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return (int)$row->total;
    }

    public function getLowStockProducts(): array {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE stock <= stock_minimum
            ORDER BY stock ASC, name ASC
        ";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
