-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: kbmocenter
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `analytics`
--

DROP TABLE IF EXISTS `analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(15,2) NOT NULL,
  `metric_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `analytics`
--

LOCK TABLES `analytics` WRITE;
/*!40000 ALTER TABLE `analytics` DISABLE KEYS */;
INSERT INTO `analytics` VALUES (1,'revenue',1500.00,'2025-09-22','2025-09-29 17:18:00'),(2,'revenue',2300.00,'2025-09-23','2025-09-29 17:18:00'),(3,'revenue',1800.00,'2025-09-24','2025-09-29 17:18:00'),(4,'revenue',3200.00,'2025-09-25','2025-09-29 17:18:00'),(5,'revenue',2700.00,'2025-09-26','2025-09-29 17:18:00'),(6,'revenue',4100.00,'2025-09-27','2025-09-29 17:18:00'),(7,'revenue',3800.00,'2025-09-28','2025-09-29 17:18:00'),(8,'new_users',5.00,'2025-09-22','2025-09-29 17:18:00'),(9,'new_users',8.00,'2025-09-23','2025-09-29 17:18:00'),(10,'new_users',12.00,'2025-09-24','2025-09-29 17:18:00'),(11,'new_users',15.00,'2025-09-25','2025-09-29 17:18:00'),(12,'new_users',18.00,'2025-09-26','2025-09-29 17:18:00'),(13,'new_users',22.00,'2025-09-27','2025-09-29 17:18:00'),(14,'new_users',25.00,'2025-09-28','2025-09-29 17:18:00');
/*!40000 ALTER TABLE `analytics` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-06  9:03:28
