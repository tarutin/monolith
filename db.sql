-- MySQL dump 10.13  Distrib 5.7.23, for macos10.13 (x86_64)
--
-- Host: 127.0.0.1    Database: kvarteti
-- ------------------------------------------------------
-- Server version	5.6.49

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ki_content`
--

DROP TABLE IF EXISTS `ki_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ki_content` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) NOT NULL,
  `text` longtext,
  `status` enum('publish','delete') NOT NULL DEFAULT 'publish',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=196 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ki_content`
--

LOCK TABLES `ki_content` WRITE;
/*!40000 ALTER TABLE `ki_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `ki_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ki_files`
--

DROP TABLE IF EXISTS `ki_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ki_files` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `date` int(20) NOT NULL,
  `position` int(20) DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` text COLLATE utf8_unicode_ci,
  `additional` text COLLATE utf8_unicode_ci,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'publish',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ki_files`
--

LOCK TABLES `ki_files` WRITE;
/*!40000 ALTER TABLE `ki_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `ki_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ki_structure`
--

DROP TABLE IF EXISTS `ki_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ki_structure` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL DEFAULT '0',
  `level_id` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `position` int(20) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `update_date` datetime NOT NULL,
  `url` text,
  `name` varchar(250) NOT NULL,
  `sysname` varchar(250) NOT NULL,
  `title` text,
  `header` text,
  `keywords` text,
  `description` text,
  `module` varchar(250) NOT NULL,
  `template` varchar(100) NOT NULL,
  `isAuth` enum('0','1') NOT NULL DEFAULT '0',
  `isHide` enum('0','1') NOT NULL DEFAULT '0',
  `isClose` enum('0','1') NOT NULL DEFAULT '0',
  `status` enum('publish','delete') NOT NULL DEFAULT 'publish',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=791 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ki_structure`
--

LOCK TABLES `ki_structure` WRITE;
/*!40000 ALTER TABLE `ki_structure` DISABLE KEYS */;
INSERT INTO `ki_structure` VALUES (1,0,-1,0,0,'0000-00-00 00:00:00','2019-12-25 00:15:31','/','Главная','/','Главная','','','','','index','0','0','0','publish'),(44,1,0,0,12,'0000-00-00 00:00:00','2015-08-21 17:15:41','/back/','КП','back','КП','КП','','','','index','1','1','1','publish'),(49,44,1,0,1,'0000-00-00 00:00:00','2015-02-25 13:38:56','/back/users/','Пользователи','users','Пользователи','Пользователи','','','','users','1','1','1','publish'),(45,44,1,0,0,'0000-00-00 00:00:00','2016-03-29 12:26:51','/back/structure/','Структура','structure','Структура','Структура','','','','structure','1','1','1','publish'),(48,44,1,0,2,'0000-00-00 00:00:00','2015-02-25 13:40:00','/back/settings/','Настройки','settings','Настройки','Настройки','','','','settings','1','1','1','publish'),(50,44,1,0,3,'0000-00-00 00:00:00','2015-08-21 17:15:03','/back/content/','Контент','content','Контент','Контент','','','content','content','1','1','1','publish'),(787,1,0,0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00','/show/','Спектакль','show','Спектакль','Спектакль',NULL,NULL,'','show','0','0','0','publish'),(788,44,1,1000,4,'2019-12-25 00:19:04','2019-12-25 00:19:50','/back/cal/','Календарь','cal','Календарь','Календарь','','','','cal','1','1','1','publish'),(789,44,1,1000,5,'2019-12-25 00:19:22','2019-12-25 00:20:14','/back/show/','Спектакли','show','Спектакли','Спектакли','','','','show','1','1','1','publish'),(790,44,1,1000,6,'2019-12-25 00:29:28','2019-12-25 00:29:42','/back/contact/','Контакты','contact','Контакты','Контакты','','','','contact','1','1','1','publish');
/*!40000 ALTER TABLE `ki_structure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ki_structure_permission`
--

DROP TABLE IF EXISTS `ki_structure_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ki_structure_permission` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `structure_id` bigint(20) NOT NULL,
  `group_id` bigint(20) NOT NULL,
  `action` enum('auth','hide','close') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1616 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ki_structure_permission`
--

LOCK TABLES `ki_structure_permission` WRITE;
/*!40000 ALTER TABLE `ki_structure_permission` DISABLE KEYS */;
INSERT INTO `ki_structure_permission` VALUES (1604,49,2,'auth'),(910,47,2,'auth'),(1307,47,11,'hide'),(1314,47,11,'close'),(1558,48,14,'hide'),(1601,44,2,'auth'),(1614,50,2,'auth'),(1575,44,14,'hide'),(1583,45,14,'hide'),(1302,47,2,'hide'),(1557,48,11,'hide'),(1548,50,14,'hide'),(1547,50,11,'hide'),(1546,50,2,'hide'),(1615,50,0,'close'),(1556,48,2,'hide'),(1555,48,1,'hide'),(1613,48,11,'close'),(1612,48,2,'close'),(1308,47,12,'hide'),(1309,47,13,'hide'),(1315,47,12,'close'),(1316,47,13,'close'),(1609,49,11,'hide'),(1608,49,2,'hide'),(1610,49,11,'close'),(1582,45,11,'hide'),(1581,45,2,'hide'),(1580,45,14,'close'),(1579,45,11,'close'),(1574,44,11,'hide'),(1573,44,2,'hide'),(1572,44,1,'hide'),(1602,44,0,'close'),(1600,790,0,'close'),(1599,790,11,'hide'),(1598,790,2,'hide'),(1597,790,1,'hide'),(1596,790,2,'auth'),(1595,789,0,'close'),(1594,789,11,'hide'),(1593,789,2,'hide'),(1592,789,1,'hide'),(1591,789,2,'auth'),(1590,788,11,'hide'),(1589,788,2,'hide'),(1588,788,1,'hide'),(1587,788,2,'auth'),(1611,48,2,'auth'),(1603,45,2,'auth');
/*!40000 ALTER TABLE `ki_structure_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ki_users`
--

DROP TABLE IF EXISTS `ki_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ki_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` int(20) NOT NULL DEFAULT '0',
  `changed` int(20) DEFAULT NULL,
  `group_id` bigint(20) NOT NULL,
  `email` varchar(150) NOT NULL DEFAULT '',
  `phone` varchar(100) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `additional` text,
  `verify` varchar(100) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'publish',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1385 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ki_users`
--

LOCK TABLES `ki_users` WRITE;
/*!40000 ALTER TABLE `ki_users` DISABLE KEYS */;
INSERT INTO `ki_users` VALUES (10,0,NULL,2,'robot@','','Anonymous','b8654cc7e23606d834efd4f458ca94b5',NULL,NULL,'46.44.34.110',NULL,NULL,'publish'),(1000,1425498975,NULL,1,'alexeytarutin@gmail.com','+7 (926) 583–1741','tarutin','5288170880da798883fc79b627d390e7','Алексей','Тарутин','46.44.34.110',NULL,NULL,'publish');
/*!40000 ALTER TABLE `ki_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ki_users_groups`
--

DROP TABLE IF EXISTS `ki_users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ki_users_groups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` enum('publish','delete') NOT NULL DEFAULT 'publish',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ki_users_groups`
--

LOCK TABLES `ki_users_groups` WRITE;
/*!40000 ALTER TABLE `ki_users_groups` DISABLE KEYS */;
INSERT INTO `ki_users_groups` VALUES (1,'Admin','publish'),(2,'Unregistered','publish'),(11,'Moderator','publish');
/*!40000 ALTER TABLE `ki_users_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ki_users_sessions`
--

DROP TABLE IF EXISTS `ki_users_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ki_users_sessions` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `token` varchar(100) NOT NULL,
  `date` int(20) NOT NULL,
  `changed` int(20) DEFAULT NULL,
  `user_id` int(20) NOT NULL,
  `device` text,
  `geo` text,
  `ip` varchar(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=152 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ki_users_sessions`
--

LOCK TABLES `ki_users_sessions` WRITE;
/*!40000 ALTER TABLE `ki_users_sessions` DISABLE KEYS */;
INSERT INTO `ki_users_sessions` VALUES (151,'2aeb373adef5e3909567fe9ee93e0031',1582117782,NULL,1381,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15','{\"continent_code\":\"EU\",\"country_code\":\"RU\",\"country_code3\":\"RUS\",\"country_name\":\"Russian Federation\",\"region\":\"48\",\"city\":\"Moscow\",\"postal_code\":\"101752\",\"latitude\":55.752201080322266,\"longitude\":37.6156005859375,\"dma_code\":0,\"area_code\":0}','109.173.17.55','active');
/*!40000 ALTER TABLE `ki_users_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'kvarteti'
--
