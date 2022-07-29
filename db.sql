-- MySQL dump 10.13  Distrib 8.0.29, for macos12.2 (x86_64)
--
-- Host: localhost    Database: world_vote
-- ------------------------------------------------------
-- Server version	8.0.29

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `prompts`
--

DROP TABLE IF EXISTS `prompts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prompts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `caption` text,
  `option0` varchar(32) DEFAULT NULL,
  `option1` varchar(32) DEFAULT NULL,
  `is_mapped` tinyint(1) NOT NULL DEFAULT '0',
  `colors` json DEFAULT NULL,
  `sum` int NOT NULL,
  `count` int NOT NULL,
  `last_mean` double DEFAULT NULL,
  `count_ratios` json DEFAULT NULL,
  `n_steps` int DEFAULT '10',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prompts`
--

LOCK TABLES `prompts` WRITE;
/*!40000 ALTER TABLE `prompts` DISABLE KEYS */;
INSERT INTO `prompts` VALUES (1,'P1','No','Yes',1,'[]',0,0,NULL,'[0.9933053133085992, 0.9940856755609708, 0.9947975849841874, 0.9925386416220584, 0.997453554755418, 0.998411894363594, 0.9901564831674492, 0.994742822720863, 0.9976999849403776, 1.0, 0.999041660391824]',10),(2,'P2','1','2',1,'[]',0,0,NULL,'[0.9894425906679064, 1.0, 0.9921639954050654, 0.9916716809802528, 0.9879929981948472, 0.9967179038345824, 0.9929298178436629, 0.9994803347738088, 0.997497401673869, 0.9947759969367104, 0.9934905092719216]',10),(3,'P3','False','True',0,'[]',0,0,NULL,'[1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0]',10),(4,'P4','A','B',1,'[]',0,0,0,'[0.9922863735033516, 0.9953854765382882, 0.9929280380083826, 0.9932830013515912, 0.992573074665174, 1.0, 0.9935150927683044, 0.9941294524007808, 0.9903204226794272, 0.9865113929580732, 0.9915627943806572]',10),(5,'P5','Down','Up',1,'[]',0,0,0,'[0.9957921772750248, 0.9993988824678608, 1.0, 0.9973222946295612, 0.9909832370179106, 0.9849310764102354, 0.9948085304042515, 0.9943303687309588, 0.9880869434539666, 0.991625339836332, 0.9899176195745728]',10);
/*!40000 ALTER TABLE `prompts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `slug` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'Male','s_m'),(2,'Female','s_f'),(3,'Non-binary','s_nb'),(4,'Atheist','r_at'),(5,'Agnostic','r_ag'),(6,'Spiritual','r_sp'),(7,'Christian','r_ch'),(8,'Jewish','r_jw'),(9,'Muslim','r_ms');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `access_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `share_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `grid_row` int NOT NULL,
  `grid_col` int NOT NULL,
  `tags` json NOT NULL,
  `responses` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1100033 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1100032,'62e26c5db5f99','s_62e26c5db5f9c',61,90,'[\"s_nb\", \"r_ms\"]','{\"1\": 8}','2022-07-28 01:00:45','2022-07-28 01:01:09');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-07-28 21:03:29
