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
-- Table structure for table `chat_training_data`
--

DROP TABLE IF EXISTS `chat_training_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_training_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_pattern` text NOT NULL,
  `response` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `use_count` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_training_data`
--

LOCK TABLES `chat_training_data` WRITE;
/*!40000 ALTER TABLE `chat_training_data` DISABLE KEYS */;
INSERT INTO `chat_training_data` VALUES (1,'hello|hi|hey|greetings','Hello! Welcome to KBMO Center for Translational Research. How can I assist you with your research needs today?','greeting',0,1,'2025-10-01 08:55:30'),(2,'service|services|what do you offer|help with','We offer comprehensive research support including: Research Proposal Writing, Data Analysis, Thesis Writing, Manuscript Preparation, and Journal Submission support. Which service are you interested in?','services',0,1,'2025-10-01 08:55:30'),(3,'price|cost|how much|fee','Our prices vary based on the service and complexity. Research proposals start at $199, data analysis from $149, thesis writing from $299. Would you like detailed pricing for a specific service?','pricing',0,1,'2025-10-01 08:55:30'),(4,'contact|email|phone|address','You can reach us at: Phone: +256 771 200 234, Email: Kbmocenter@gmail.com, Location: Pece-Laroo, Gulu City, Uganda. We\'re available Mon-Fri 8AM-6PM.','contact',0,1,'2025-10-01 08:55:30'),(5,'deadline|time|how long|duration','Project timelines depend on complexity: Simple edits (2-3 days), Proposals (1-2 weeks), Full research projects (2-4 weeks). We always discuss timelines before starting.','timeline',0,1,'2025-10-01 08:55:30'),(6,'research|proposal|thesis|dissertation','We specialize in academic research support! Whether you need help with proposal development, data collection, analysis, or writing, our expert team can assist. What specific research help do you need?','research',0,1,'2025-10-01 08:55:30'),(7,'data analysis|statistics|spss|analysis','Our data analysis service includes statistical testing, data interpretation, visualization, and comprehensive reporting using SPSS, R, Python, or other tools as needed.','data_analysis',0,1,'2025-10-01 08:55:30'),(8,'thank you|thanks|appreciate','You\'re welcome! Is there anything else I can help you with regarding our research services?','gratitude',0,1,'2025-10-01 08:55:30'),(9,'bye|goodbye|see you','Thank you for contacting KBMO Center! Feel free to reach out anytime for research support. Have a great day!','closing',0,1,'2025-10-01 08:55:30');
/*!40000 ALTER TABLE `chat_training_data` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-06  9:03:26
