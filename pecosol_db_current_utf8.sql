-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: pecosol_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(30) NOT NULL,
  `entity` varchar(40) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_audit_logs_user` (`user_id`),
  CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'create','sale',23,'Venta registrada para cliente pedro por S/. 15,998.00','2026-05-05 10:19:08'),(2,1,'create','work_order',NULL,'Orden de trabajo registrada para cliente pedro','2026-05-05 10:20:38'),(3,1,'update','work_order',1,'Estado actualizado a en_proceso','2026-05-05 10:20:45'),(4,1,'create','purchase',NULL,'Compra registrada. Proveedor: York, Cantidad: 2','2026-05-05 10:21:26'),(5,1,'create','purchase',NULL,'Compra registrada. Proveedor: York, Cantidad: 5','2026-05-05 10:23:07'),(6,1,'adjust','inventory',9,'Reconteo aplicado. Diferencia: -5','2026-05-05 10:23:29'),(7,1,'adjust','inventory',9,'Reconteo aplicado. Diferencia: 3','2026-05-05 10:29:17'),(8,1,'update','work_order',1,'Estado actualizado a finalizado','2026-05-05 10:30:10'),(9,1,'update','sale',23,'Venta actualizada para cliente pedro','2026-05-05 10:49:00'),(10,1,'create','sale',24,'Venta registrada para cliente maria por S/. 15,998.00','2026-05-05 10:49:20'),(11,1,'delete','sale',24,'Venta eliminada y stock restaurado','2026-05-05 10:51:29'),(12,1,'create','sale',25,'Venta registrada para cliente maria por S/. 15,998.00','2026-05-05 10:51:43'),(13,1,'delete','sale',25,'Venta eliminada y stock restaurado','2026-05-05 12:03:49'),(14,1,'create','sale',26,'Venta registrada para cliente maria por S/. 15,998.00','2026-05-05 12:04:03'),(15,1,'create','sale',27,'Venta registrada para cliente alex por S/. 5,000.00','2026-05-05 12:09:50'),(16,1,'delete','sale',23,'Venta eliminada y stock restaurado','2026-05-05 12:10:00'),(17,1,'create','sale',28,'Venta registrada para cliente pedro por S/. 5,000.00','2026-05-05 12:16:09'),(18,1,'delete','sale',28,'Venta eliminada y stock restaurado','2026-05-05 12:27:53'),(19,1,'delete','sale',27,'Venta eliminada y stock restaurado','2026-05-05 12:27:56'),(20,1,'create','sale',29,'Venta registrada para cliente pedro por S/. 15,998.00','2026-05-05 12:28:20'),(21,1,'create','purchase',NULL,'Compra registrada. Proveedor: midea, Cantidad: 2','2026-05-05 12:30:36'),(22,1,'create','work_order',NULL,'Orden de trabajo registrada para cliente maria','2026-05-05 12:36:18'),(23,1,'update','work_order',2,'Estado actualizado a pendiente','2026-05-05 12:36:32'),(24,1,'create','purchase',NULL,'Compra registrada. Proveedor: LG, Cantidad: 20','2026-05-05 12:37:05'),(25,1,'update','work_order',2,'Estado actualizado a finalizado','2026-05-07 07:33:53'),(26,1,'adjust','inventory',9,'Reconteo aplicado. Diferencia: 13','2026-05-07 07:34:08'),(27,1,'adjust','inventory',9,'Reconteo aplicado. Diferencia: 6','2026-05-07 07:34:18'),(28,1,'adjust','inventory',7,'Reconteo aplicado. Diferencia: 14','2026-05-07 07:34:23'),(29,1,'delete','sale',30,'Venta eliminada y stock restaurado','2026-05-07 14:28:50'),(30,1,'delete','sale',31,'Venta eliminada y stock restaurado','2026-05-07 14:34:17'),(32,1,'delete','sale',26,'Venta eliminada y stock restaurado','2026-05-07 15:44:34'),(33,1,'delete','sale',32,'Venta eliminada y stock restaurado','2026-05-07 15:45:23'),(34,1,'delete','sale',29,'Venta eliminada y stock restaurado','2026-05-07 15:45:26'),(35,1,'create','sale',34,'Venta registrada para cliente maria por S/. 31,996.00','2026-05-07 15:57:32'),(36,1,'create','sale',35,'Venta registrada para cliente lolo por S/. 7,500.00','2026-05-07 15:58:17'),(44,1,'create','purchase',NULL,'Compra registrada. Proveedor: York, Cantidad: 10','2026-05-07 17:28:24'),(45,1,'create','purchase',NULL,'Compra registrada. Proveedor: Midea, Cantidad: 5','2026-05-07 17:34:37'),(47,1,'update','purchase',8,'Compra actualizada. Proveedor: Midea, Cantidad: 5','2026-05-07 18:02:50'),(48,1,'delete','purchase',8,'Compra eliminada. Proveedor: Midea, Cantidad: 5','2026-05-07 18:03:05'),(55,1,'create','sale',41,'Venta registrada para cliente mario por S/. 15,998.00','2026-05-12 13:38:46'),(57,1,'create','work_order',NULL,'Orden de trabajo registrada para cliente mario','2026-05-17 20:09:29'),(58,1,'update','work_order',4,'Estado actualizado a finalizado','2026-05-17 20:09:50'),(59,14,'create','purchase',NULL,'Compra registrada. Proveedor: Midea, Cantidad: 5','2026-05-17 22:40:01'),(60,9,'create','work_order',NULL,'Orden de trabajo registrada para cliente lolaa','2026-05-17 22:47:10'),(61,1,'delete','purchase',10,'Compra eliminada. Proveedor: York, Cantidad: 5','2026-05-17 23:03:56'),(62,1,'delete','purchase',9,'Compra eliminada. Proveedor: York, Cantidad: 5','2026-05-17 23:03:59'),(63,1,'delete','purchase',6,'Compra eliminada. Proveedor: LG, Cantidad: 5','2026-05-17 23:04:03'),(64,1,'delete','purchase',5,'Compra eliminada. Proveedor: LG, Cantidad: 5','2026-05-17 23:04:06'),(65,1,'delete','purchase',11,'Compra eliminada. Proveedor: Midea, Cantidad: 5','2026-05-17 23:04:08'),(66,1,'delete','purchase',4,'Compra eliminada. Proveedor: LG, Cantidad: 20','2026-05-17 23:04:10'),(67,1,'delete','purchase',3,'Compra eliminada. Proveedor: midea, Cantidad: 2','2026-05-17 23:04:12'),(68,1,'delete','purchase',2,'Compra eliminada. Proveedor: York, Cantidad: 5','2026-05-17 23:04:15'),(69,1,'delete','purchase',1,'Compra eliminada. Proveedor: York, Cantidad: 2','2026-05-17 23:04:18'),(70,1,'adjust','inventory',7,'Reconteo aplicado. Diferencia: 2','2026-05-17 23:04:37'),(71,1,'adjust','inventory',8,'Reconteo aplicado. Diferencia: 30','2026-05-17 23:04:44'),(72,1,'adjust','inventory',9,'Reconteo aplicado. Diferencia: 17','2026-05-17 23:04:50'),(73,14,'create','work_order',NULL,'Orden de trabajo registrada para cliente lolaa','2026-05-18 00:11:20'),(74,1,'create','purchase',NULL,'Compra registrada. Proveedor: LG, Cantidad: 5','2026-05-18 00:29:16'),(75,14,'update','purchase',12,'Compra actualizada. Proveedor: LG, Cantidad: 10','2026-05-18 09:54:05'),(76,1,'delete','sale',42,'Venta eliminada y stock restaurado','2026-05-20 05:16:43'),(77,1,'delete','sale',41,'Venta eliminada y stock restaurado','2026-05-20 05:16:45'),(78,1,'delete','sale',40,'Venta eliminada y stock restaurado','2026-05-20 05:16:48'),(79,1,'delete','sale',38,'Venta eliminada y stock restaurado','2026-05-20 05:16:49'),(80,1,'delete','sale',37,'Venta eliminada y stock restaurado','2026-05-20 05:16:51'),(81,1,'delete','sale',36,'Venta eliminada y stock restaurado','2026-05-20 05:16:53'),(82,1,'adjust','inventory',7,'Reconteo aplicado. Diferencia: -9','2026-06-08 12:40:50'),(83,1,'adjust','inventory',7,'Reconteo aplicado. Diferencia: -1','2026-06-08 12:41:01'),(84,1,'adjust','inventory',7,'Reconteo aplicado. Diferencia: 10','2026-06-08 12:42:51');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hired_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (7,'Midea 12000 F/S','Equipo de Aire Acondicionado marca Midea de 12000 BTU 220v/1F/R410A Frio solo',1500.00,35,3,'2026-05-05 06:06:56','2026-06-08 12:42:51'),(8,'LG 18000 F/S','Equipo de Aire Acondicionado marca LG de 18000 BTU 220v/1F/R410A Frio solo',2500.00,50,1,'2026-05-05 06:07:34','2026-05-20 05:16:51'),(9,'York 24000 F/C','Equipo de Aire acondicionado marca York de 24000 BTU 220v/1F/R32 Frio calor',7999.00,37,1,'2026-05-05 06:08:03','2026-05-20 05:16:49'),(10,'Samsung 12000 Inverter','Aire acondicionado Inverter 12000 BTU',2100.00,18,2,'2026-06-08 11:46:50',NULL),(11,'Daikin 18000 Inverter','Aire acondicionado Inverter 18000 BTU',3200.00,12,2,'2026-06-08 11:47:16',NULL),(12,'Gas Refrigerante R410A','Cilindro de gas refrigerante 11.3 kg',450.00,8,3,'2026-06-08 11:47:53',NULL),(13,'Gas Refrigerante R32','Cilindro de gas refrigerante 9 kg',520.00,6,2,'2026-06-08 11:48:26',NULL),(14,'Compresor Scroll 24000 BTU','Compresor para aire acondicionado',950.00,4,2,'2026-06-08 11:48:49',NULL),(15,'Capacitor 35uF','Capacitor para unidad condensadora',15.00,25,5,'2026-06-08 11:49:06',NULL),(16,'Termostato Digital Universal','Control electr├│nico de temperatura',80.00,1,3,'2026-06-08 11:49:31',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `supplier` varchar(120) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `purchase_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_purchases_product` (`product_id`),
  KEY `fk_purchases_user` (`user_id`),
  CONSTRAINT `fk_purchases_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_purchases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (12,8,1,10,1500.00,'LG','promo','2026-05-18 00:29:16');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `client_name` varchar(120) NOT NULL DEFAULT 'Cliente General',
  `description` varchar(255) DEFAULT NULL,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (33,9,7,5,1500.00,7500.00,'maria','venta promo','2026-05-07 14:48:44','2026-05-07 14:48:44',NULL),(34,9,9,4,7999.00,31996.00,'maria','promo','2026-05-07 15:57:32','2026-05-07 15:57:32',NULL),(35,1,7,5,1500.00,7500.00,'lolo','promo','2026-05-07 15:58:17','2026-05-07 15:58:17',NULL);
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (121,8,9,-5,'salida','promocion','2026-05-12 13:32:11'),(122,9,9,-2,'salida','promo','2026-05-12 13:38:46'),(124,7,14,5,'ingreso','Compra proveedor: Midea - promo','2026-05-17 22:40:01'),(125,7,1,2,'ingreso','Ajuste por reconteo','2026-05-17 23:04:37'),(126,8,1,30,'ingreso','Ajuste por reconteo','2026-05-17 23:04:44'),(127,9,1,17,'ingreso','Ajuste por reconteo','2026-05-17 23:04:50'),(128,8,1,5,'ingreso','Compra proveedor: LG - promo','2026-05-18 00:29:16'),(129,7,9,-5,'salida','promo','2026-05-18 01:02:51'),(130,8,14,10,'ingreso','Compra actualizada proveedor: LG - promo','2026-05-18 09:54:05'),(131,7,9,5,'ingreso','Eliminaci├│n Venta ID: 42','2026-05-20 05:16:43'),(132,9,9,2,'ingreso','Eliminaci├│n Venta ID: 41','2026-05-20 05:16:45'),(133,8,9,5,'ingreso','Eliminaci├│n Venta ID: 40','2026-05-20 05:16:48'),(134,9,9,5,'ingreso','Eliminaci├│n Venta ID: 38','2026-05-20 05:16:49'),(135,8,9,5,'ingreso','Eliminaci├│n Venta ID: 37','2026-05-20 05:16:51'),(136,7,9,5,'ingreso','Eliminaci├│n Venta ID: 36','2026-05-20 05:16:53'),(137,7,1,-9,'salida','Ajuste por reconteo','2026-06-08 12:40:50'),(138,7,1,-1,'salida','Ajuste por reconteo','2026-06-08 12:41:01'),(139,7,1,10,'ingreso','Ajuste por reconteo','2026-06-08 12:42:51');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','comercial','supervisor') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$JyU2DewGfMlgm.Y0kb6DcOcaliJmTW/qws/KhspqgB4PGqwO4Zzwe','Administrador Principal','admin@bodeshop.com','admin','2025-06-04 09:19:49','2026-04-30 15:46:47'),(9,'lucho','$2y$10$w2EW7hSyESmBSZ8dFmN9pehHiGxzKldlx7EqwWG6p4nLnZEOMSZm6','lucho diaz','lucho@gmail.com','comercial','2026-05-07 14:44:47',NULL),(14,'pedrito','$2y$10$N2qyQO6TJDjpKR6uPiOpUeXWEO9.xUocjxeMADvwqy.E1DCC7zzQm','pedro diaz','pedrito@gmail.com','supervisor','2026-05-17 21:57:30','2026-05-17 22:17:23');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_orders`
--

DROP TABLE IF EXISTS `work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(120) NOT NULL,
  `service_type` varchar(120) NOT NULL,
  `technician_name` varchar(120) NOT NULL,
  `materials_used` text DEFAULT NULL,
  `status` enum('pendiente','en_proceso','finalizado') NOT NULL DEFAULT 'pendiente',
  `sale_id` int(11) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_work_orders_sale` (`sale_id`),
  KEY `fk_work_orders_user` (`created_by`),
  CONSTRAINT `fk_work_orders_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_work_orders_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_orders`
--

LOCK TABLES `work_orders` WRITE;
/*!40000 ALTER TABLE `work_orders` DISABLE KEYS */;
INSERT INTO `work_orders` VALUES (6,'lolaa','instalacion','pedro','equipos','pendiente',NULL,'',14,'2026-05-18 00:11:20',NULL);
/*!40000 ALTER TABLE `work_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-12 15:48:06

