-- MySQL dump 10.13  Distrib 9.2.0, for Linux (x86_64)
--
-- Host: localhost    Database: proj
-- ------------------------------------------------------
-- Server version	9.2.0

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
-- Table structure for table `progress`
--

DROP TABLE IF EXISTS `progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(256) NOT NULL,
  `stars_count` int DEFAULT '0',
  `step` tinyint DEFAULT '0',
  `file_github_url` varchar(300) DEFAULT NULL,
  `downloaded_file_name` varchar(1000) DEFAULT NULL,
  `is_paused` tinyint DEFAULT '0',
  `pause_reason` tinyint DEFAULT '0',
  `stuff_times` text,
  `semgrep_out` longtext,
  `is_local` tinyint DEFAULT '0',
  `is_vulnerable_to_dos` tinyint DEFAULT '0',
  `vector_string` varchar(512) DEFAULT NULL COMMENT 'CVSS Score Vector String',
  `base_score` decimal(10,5) DEFAULT NULL COMMENT 'CVSS base score',
  `severity` varchar(64) DEFAULT NULL COMMENT 'CVSS Score severity',
  `poc` varchar(2048) DEFAULT NULL,
  `run_method` varchar(128) DEFAULT NULL,
  `llm_try_count` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `exit_code` int DEFAULT NULL,
  `pull_request_link` varchar(512) DEFAULT NULL,
  `first_appeared_at` date DEFAULT NULL,
  `is_maintained` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_github_url` (`file_github_url`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progress`
--

LOCK TABLES `progress` WRITE;
/*!40000 ALTER TABLE `progress` DISABLE KEYS */;
INSERT INTO `progress` VALUES (146,'XOOPS/XoopsCore26',138,0,'https://raw.githubusercontent.com/XOOPS/XoopsCore26/HEAD/htdocs/modules/protector/class/protector.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:23:59','2025-11-30 14:23:59',NULL,NULL,NULL,NULL),(147,'gnuboard/gnuboard5',334,0,'https://raw.githubusercontent.com/gnuboard/gnuboard5/HEAD/lib/common.lib.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:23:59','2025-11-30 14:23:59',NULL,NULL,NULL,NULL),(148,'stefangabos/Zebra_Database',119,0,'https://raw.githubusercontent.com/stefangabos/Zebra_Database/HEAD/Zebra_Database.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:24:00','2025-11-30 14:24:00',NULL,NULL,NULL,NULL),(149,'My-Little-Forum/mylittleforum',146,0,'https://raw.githubusercontent.com/My-Little-Forum/mylittleforum/HEAD/includes/posting.inc.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:26:05','2025-11-30 14:26:05',NULL,NULL,NULL,NULL),(150,'jasonrohrer/OneLife',1073,0,'https://raw.githubusercontent.com/jasonrohrer/OneLife/HEAD/fitnessServer/server.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:26:06','2025-11-30 14:26:06',NULL,NULL,NULL,NULL),(151,'bfengj/CTF',414,0,'https://raw.githubusercontent.com/bfengj/CTF/HEAD/Web/%E6%AF%94%E8%B5%9B/2022-%E9%B9%8F%E5%9F%8E%E6%9D%AF-final/source/web2/action.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:26:08','2025-11-30 14:26:08',NULL,NULL,NULL,NULL),(152,'IssabelFoundation/issabelPBX',163,0,'https://raw.githubusercontent.com/IssabelFoundation/issabelPBX/HEAD/framework/amp_conf/htdocs/admin/libraries/php-upgrade/upgrade.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:26:10','2025-11-30 14:26:10',NULL,NULL,NULL,NULL),(153,'drshahizan/learn-php',194,0,'https://raw.githubusercontent.com/drshahizan/learn-php/HEAD/lab/php/lab4/download/msoWP/inc_phpExer.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:26:12','2025-11-30 14:26:12',NULL,NULL,NULL,NULL),(154,'0xs1riu5/vulawdhub',202,0,'https://raw.githubusercontent.com/0xs1riu5/vulawdhub/HEAD/ECshop/3.0.0/php-fpm/src/install/includes/lib_auto_installer.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 14:26:26','2025-11-30 14:26:26',NULL,NULL,NULL,NULL),(155,'php/web-php',1055,0,'https://raw.githubusercontent.com/php/web-php/HEAD/ChangeLog-4.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:51','2025-11-30 15:21:51',NULL,NULL,NULL,NULL),(156,'xl7dev/WebShell',1960,0,'https://raw.githubusercontent.com/xl7dev/WebShell/HEAD/Php/c99_locus7s.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:52','2025-11-30 15:21:52',NULL,NULL,NULL,NULL),(157,'ActiveState/code',1959,0,'https://raw.githubusercontent.com/ActiveState/code/HEAD/recipes/PHP/347808_DBeSessiPHP_class_facilitates_having_sessions/recipe-347808.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:53','2025-11-30 15:21:53',NULL,NULL,NULL,NULL),(158,'tutorial0/WebShell',395,0,'https://raw.githubusercontent.com/tutorial0/WebShell/HEAD/Php/c99_webshell.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:54','2025-11-30 15:21:54',NULL,NULL,NULL,NULL),(159,'OrayDev/tudu-web',242,0,'https://raw.githubusercontent.com/OrayDev/tudu-web/HEAD/htdocs/www.tudu.com/library/Tudu/Install/Function.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:55','2025-11-30 15:21:55',NULL,NULL,NULL,NULL),(160,'sh377c0d3/Payloads',913,0,'https://raw.githubusercontent.com/sh377c0d3/Payloads/HEAD/Web-Shells/PHP/iSHELL.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:56','2025-11-30 15:21:56',NULL,NULL,NULL,NULL),(161,'tanjiti/webshellSample',418,0,'https://raw.githubusercontent.com/tanjiti/webshellSample/HEAD/ASP/dama/r57.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:56','2025-11-30 15:21:56',NULL,NULL,NULL,NULL),(162,'Aabyss-Team/WebShell',552,0,'https://raw.githubusercontent.com/Aabyss-Team/WebShell/HEAD/PHP%20WebShell/php_mof%2BSHELL.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:21:58','2025-11-30 15:21:58',NULL,NULL,NULL,NULL),(163,'PinoyWH1Z/C99Shell-PHP7',149,0,'https://raw.githubusercontent.com/PinoyWH1Z/C99Shell-PHP7/HEAD/c99shell.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:22:01','2025-11-30 15:22:01',NULL,NULL,NULL,NULL),(164,'ryanmrestivo/red-team',151,0,'https://raw.githubusercontent.com/ryanmrestivo/red-team/HEAD/_Resources/Shell/php/shellr57/r57.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:22:02','2025-11-30 15:22:02',NULL,NULL,NULL,NULL),(165,'Kevil-hui/BestShell',110,0,'https://raw.githubusercontent.com/Kevil-hui/BestShell/HEAD/best_php_shell.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:22:03','2025-11-30 15:22:03',NULL,NULL,NULL,NULL),(166,'bediger4000/php-malware-analysis',140,0,'https://raw.githubusercontent.com/bediger4000/php-malware-analysis/HEAD/webshells/154.121.7.26-2018-08-07a/dc8.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:22:04','2025-11-30 15:22:04',NULL,NULL,NULL,NULL),(167,'kitezzzGrim/CTF-Note',195,0,'https://raw.githubusercontent.com/kitezzzGrim/CTF-Note/HEAD/Summary/Real/tools/PHP%E5%B8%B8%E7%94%A8%E4%B8%80%E5%8F%A5%E8%AF%9D%E6%9C%A8%E9%A9%AC/BestShell-master/best_php_shell.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:22:35','2025-11-30 15:22:35',NULL,NULL,NULL,NULL),(168,'ym2011/SecurityTechnique',325,0,'https://raw.githubusercontent.com/ym2011/SecurityTechnique/HEAD/10-%E5%BA%94%E6%80%A5%E5%93%8D%E5%BA%94/linux/MaskFindShell/test/PHP/d.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:25:24','2025-11-30 15:25:24',NULL,NULL,NULL,NULL),(169,'chyrp/chyrp',204,0,'https://raw.githubusercontent.com/chyrp/chyrp/HEAD/install.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:26:38','2025-11-30 15:26:38',NULL,NULL,NULL,NULL),(170,'Kitware/CDash',237,0,'https://raw.githubusercontent.com/Kitware/CDash/HEAD/app/cdash/include/common.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:26:39','2025-11-30 15:26:39',NULL,NULL,NULL,NULL),(171,'l3m0n/pentest_tools',621,0,'https://raw.githubusercontent.com/l3m0n/pentest_tools/HEAD/%E6%9D%83%E9%99%90%E6%94%BB%E9%98%B2/mysql/mysqlmoon.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:26:41','2025-11-30 15:26:41',NULL,NULL,NULL,NULL),(172,'ShopeX/ecshop',423,0,'https://raw.githubusercontent.com/ShopeX/ecshop/HEAD/upload/install/includes/lib_installer.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:26:43','2025-11-30 15:26:43',NULL,NULL,NULL,NULL),(173,'Casual-Ragnarok/ro-single-server',180,0,'https://raw.githubusercontent.com/Casual-Ragnarok/ro-single-server/HEAD/ROEmulator/docker/apache2/www/install/include/install_function.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:27:08','2025-11-30 15:27:08',NULL,NULL,NULL,NULL),(174,'fuhei/pentest-tools',219,0,'https://raw.githubusercontent.com/fuhei/pentest-tools/HEAD/%E6%9D%83%E9%99%90%E6%94%BB%E9%98%B2/mysql/mysqlmoon.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:27:12','2025-11-30 15:27:12',NULL,NULL,NULL,NULL),(175,'BugFor-Pings/PHPwebshell',158,0,'https://raw.githubusercontent.com/BugFor-Pings/PHPwebshell/HEAD/4-9%20404%E9%A1%B5%E9%9D%A2%E5%A4%A7%E9%A9%AC/404%E9%A1%B5%E9%9D%A2%E5%A4%A7%E9%A9%AC-Nginx%E7%89%88%E6%9C%AC.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:28:18','2025-11-30 15:28:18',NULL,NULL,NULL,NULL),(176,'xiaomlove/nexusphp',1111,0,'https://raw.githubusercontent.com/xiaomlove/nexusphp/HEAD/include/globalfunctions.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:28:44','2025-11-30 15:28:44',NULL,NULL,NULL,NULL),(177,'drlippman/IMathAS',127,0,'https://raw.githubusercontent.com/drlippman/IMathAS/HEAD/bltilaunch.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:29:57','2025-11-30 15:29:57',NULL,NULL,NULL,NULL),(178,'yaofeifly/Vub_ENV',241,0,'https://raw.githubusercontent.com/yaofeifly/Vub_ENV/HEAD/%E6%B5%B7%E6%B4%8BCMS/seaCMS_env/bin/install/index.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:36:53','2025-11-30 15:36:53',NULL,NULL,NULL,NULL),(179,'zyx0814/Pichome',1050,0,'https://raw.githubusercontent.com/zyx0814/Pichome/HEAD/install/include/install_function.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:36:54','2025-11-30 15:36:54',NULL,NULL,NULL,NULL),(180,'webshellpub/awsome-webshell',221,0,'https://raw.githubusercontent.com/webshellpub/awsome-webshell/HEAD/php/w3d.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:36:55','2025-11-30 15:36:55',NULL,NULL,NULL,NULL),(181,'vito/chyrp',230,0,'https://raw.githubusercontent.com/vito/chyrp/HEAD/install.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 15:37:08','2025-11-30 15:37:08',NULL,NULL,NULL,NULL),(182,'duoergun0729/2book',380,0,'https://raw.githubusercontent.com/duoergun0729/2book/HEAD/data/webshell/webshell/PHP/tanjiti/dama/a824680ae0452cb5a0bebc0e8bf858f4.php',NULL,0,0,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-11-30 16:02:13','2025-11-30 16:02:13',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `progress` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-30 19:09:29
