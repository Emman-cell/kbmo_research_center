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
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `service_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `file_path` varchar(500) DEFAULT NULL,
  `content` longtext,
  `word_count` int DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `status` enum('pending','in_progress','completed','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,2,8,'business plan','two weeks from now','doc_1759165076_68daba94c354f.docx',NULL,NULL,NULL,'in_progress','2025-09-29 16:57:56','2025-09-29 17:05:36'),(2,2,7,'dest','two weeks','doc_1759409459_68de75336f307.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 12:50:59','2025-10-02 12:50:59'),(3,2,7,'dest','two weeks','doc_1759409559_68de7597caa22.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 12:52:39','2025-10-02 12:52:39'),(4,2,7,'dest','two weeks','doc_1759409654_68de75f693925.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 12:54:14','2025-10-02 12:54:14'),(5,2,7,'dest','two weeks','doc_1759410002_68de7752771c9.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 13:00:02','2025-10-02 13:00:02'),(6,2,7,'dest','two weeks','doc_1759410037_68de77759cdae.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 13:00:37','2025-10-02 13:00:37'),(7,2,1,'text','3days','doc_1759410534_68de7966d5d7e.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 13:08:54','2025-10-02 13:08:54'),(8,2,1,'text','3days','doc_1759410620_68de79bc27cb7.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 13:10:20','2025-10-02 13:10:20'),(9,2,1,'text','3days','doc_1759410638_68de79ceb2ab5.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 13:10:38','2025-10-02 13:10:38'),(10,2,1,'text','3days','doc_1759410670_68de79ee00352.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 13:11:10','2025-10-02 13:11:10'),(11,2,1,'text','3days','doc_1759410941_68de7afd0aa90.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'pending','2025-10-02 13:15:41','2025-10-02 13:15:41'),(12,2,1,'text','3days','doc_1759411299_68de7c63c5aa7.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'completed','2025-10-02 13:21:39','2025-10-03 10:48:15'),(13,2,1,'text','3days','doc_1759411332_68de7c847d7c7.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'in_progress','2025-10-02 13:22:12','2025-10-03 10:58:23'),(14,2,1,'text','3days','doc_1759411366_68de7ca6e3aac.doc','[Word Document - Content extraction requires PHPWord library]',8,173568,'completed','2025-10-02 13:22:46','2025-10-03 10:25:56');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-06  9:03:32
