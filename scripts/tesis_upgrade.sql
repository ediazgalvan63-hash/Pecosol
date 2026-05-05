-- Mejoras para presentacion de tesis: compras y ordenes de trabajo
-- Ejecutar en la base de datos activa del proyecto.

CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    quantity INT NOT NULL,
    supplier VARCHAR(120) NOT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    purchase_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchases_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_purchases_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS work_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(120) NOT NULL,
    service_type VARCHAR(120) NOT NULL,
    technician_name VARCHAR(120) NOT NULL,
    materials_used TEXT DEFAULT NULL,
    status ENUM('pendiente', 'en_proceso', 'finalizado') NOT NULL DEFAULT 'pendiente',
    sale_id INT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_work_orders_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    CONSTRAINT fk_work_orders_user FOREIGN KEY (created_by) REFERENCES users(id)
);

ALTER TABLE sales
    ADD COLUMN IF NOT EXISTS client_name VARCHAR(120) NOT NULL DEFAULT 'Cliente General' AFTER total_price;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(30) NOT NULL,
    entity VARCHAR(40) NOT NULL,
    entity_id INT NULL,
    details VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
);
