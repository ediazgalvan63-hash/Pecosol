-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-06-2025 a las 03:49:32
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bodeshop_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hired_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `address`, `phone`, `hired_date`) VALUES
(1, 2, 'Av. Los Pinos 123, Lima', '999-123-456', '2025-01-15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'Producto A', 'Descripción de Producto A', 10.00, 96, '2025-06-04 09:19:49', '2025-06-04 10:56:42'),
(2, 'Producto B', 'Descripción de Producto B', 20.00, 49, '2025-06-04 09:19:49', '2025-06-04 09:19:49'),
(3, 'Producto C', 'Descripción de Producto C', 15.50, 70, '2025-06-04 09:19:49', '2025-06-04 09:19:49'),
(4, 'Gaseosa', 'Promocion', 1.50, 18, '2025-06-05 15:18:43', '2025-06-10 19:25:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `product_id`, `quantity`, `unit_price`, `total_price`, `description`, `sale_date`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 2, 10.00, 20.00, 'Venta de prueba por Empleado Uno', '2025-06-04 09:19:49', '2025-06-04 09:19:49', NULL),
(2, 1, 2, 1, 20.00, 20.00, 'Venta demo creada por Admin', '2025-05-30 10:00:00', '2025-06-04 09:19:49', NULL),
(3, 2, 3, 5, 15.50, 77.50, 'Venta demo (Empleado vende 5 de Producto C)', '2025-06-01 14:30:00', '2025-06-04 09:19:49', NULL),
(4, 2, 1, 1, 10.00, 10.00, 'cliente especial', '2025-06-04 10:50:39', '2025-06-04 10:50:39', NULL),
(5, 2, 1, 1, 10.00, 10.00, 'cliente especial', '2025-06-04 10:53:14', '2025-06-04 10:53:14', NULL),
(6, 2, 1, 1, 10.00, 10.00, 'cliente especial', '2025-06-04 10:56:42', '2025-06-04 10:56:42', NULL),
(7, 3, 4, 2, 1.50, 3.00, '', '2025-06-10 19:25:29', '2025-06-10 19:25:29', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `movement_type` enum('ingreso','salida') NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `movement_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `quantity_change`, `movement_type`, `notes`, `movement_date`) VALUES
(1, 1, 1, 100, 'ingreso', 'Stock inicial de Producto A', '2025-06-04 09:19:49'),
(2, 2, 1, 50, 'ingreso', 'Stock inicial de Producto B', '2025-06-04 09:19:49'),
(3, 3, 1, 75, 'ingreso', 'Stock inicial de Producto C', '2025-06-04 09:19:49'),
(4, 1, 2, -2, 'salida', 'Venta de prueba (2 unidades)', '2025-06-04 09:19:49'),
(5, 2, 1, -1, 'salida', 'Venta demo por Admin', '2025-05-30 10:00:00'),
(6, 3, 2, -5, 'salida', 'Venta demo (Empleado vende 5 de C)', '2025-06-01 14:30:00'),
(7, 1, 2, -1, 'salida', 'Venta ID: 5', '2025-06-04 10:53:14'),
(8, 1, 2, -1, 'salida', 'Venta ID: 6', '2025-06-04 10:56:42'),
(9, 4, 3, -2, 'salida', 'Venta ID: 7', '2025-06-10 19:25:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$cz9xvE8suOhw/qmycy2.6OCOnObJm7rZtenF.LyB7TupW61QPMzui', 'Administrador Principal', 'admin@bodeshop.com', 'admin', '2025-06-04 09:19:49', '2025-06-04 10:30:14'),
(2, 'empleado1', '$2b$12$kf.iUPnCWsz83F30Jt7k6.FY7igB33m7yNEL.Kh68PN4WkOUiLQEm', 'Empleado Uno', 'empleado1@bodeshop.com', 'employee', '2025-06-04 09:19:49', NULL),
(3, 'Ale', '$2y$10$Dcxp0te4jcSxIU/pCv3SUuTcW0/1iALTIWJCjxerUKPfxIv86rJ86', 'Ale Peres', 'aleperes@gmail.com', 'employee', '2025-06-05 15:18:04', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Ajuste para control de stock minimo
--
ALTER TABLE `products`
  ADD COLUMN `stock_minimum` int(11) NOT NULL DEFAULT 0 AFTER `stock`;

--
-- Indices de la tabla `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Filtros para la tabla `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
