-- ============================================================
-- EXPORTACIÓN DE RECONTEO: Productos + Stock Movements
-- Generado: 2026-06-13 02:11:14
-- ============================================================

-- TRUNCATE TABLE products;
-- TRUNCATE TABLE stock_movements;

-- ============================================================
-- TABLA: products
-- ============================================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimum` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (id, name, description, price, stock, stock_minimum) VALUES
(7, 'Midea 12000 F/S', 'Equipo de Aire Acondicionado marca Midea de 12000 BTU 220v/1F/R410A Frio solo', 1500, 35, 3),
(8, 'LG 18000 F/S', 'Equipo de Aire Acondicionado marca LG de 18000 BTU 220v/1F/R410A Frio solo', 2500, 50, 1),
(9, 'York 24000 F/C', 'Equipo de Aire acondicionado marca York de 24000 BTU 220v/1F/R32 Frio calor', 7999, 37, 1),
(10, 'Samsung 12000 Inverter', 'Aire acondicionado Inverter 12000 BTU', 2100, 18, 2),
(11, 'Daikin 18000 Inverter', 'Aire acondicionado Inverter 18000 BTU', 3200, 12, 2),
(12, 'Gas Refrigerante R410A', 'Cilindro de gas refrigerante 11.3 kg', 450, 8, 3),
(13, 'Gas Refrigerante R32', 'Cilindro de gas refrigerante 9 kg', 520, 6, 2),
(14, 'Compresor Scroll 24000 BTU', 'Compresor para aire acondicionado', 950, 4, 2),
(15, 'Capacitor 35uF', 'Capacitor para unidad condensadora', 15, 25, 5),
(16, 'Termostato Digital Universal', 'Control electrónico de temperatura', 80, 1, 3);

-- ============================================================
-- TABLA: stock_movements (solo reconteos)
-- ============================================================
CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `movement_type` enum('ingreso','salida') NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `movement_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO stock_movements (id, product_id, user_id, quantity_change, movement_type, notes, movement_date) VALUES
(125, 7, 1, 2, 'ingreso', 'Ajuste por reconteo', '2026-05-17 23:04:37'),
(126, 8, 1, 30, 'ingreso', 'Ajuste por reconteo', '2026-05-17 23:04:44'),
(127, 9, 1, 17, 'ingreso', 'Ajuste por reconteo', '2026-05-17 23:04:50'),
(137, 7, 1, -9, 'salida', 'Ajuste por reconteo', '2026-06-08 12:40:50'),
(138, 7, 1, -1, 'salida', 'Ajuste por reconteo', '2026-06-08 12:41:01'),
(139, 7, 1, 10, 'ingreso', 'Ajuste por reconteo', '2026-06-08 12:42:51');

-- Fin de exportación
