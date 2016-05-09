
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
DROP TABLE IF EXISTS `equipements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mac` char(17) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'init',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1232 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (3,'tftpaddress','10.20.124.50'),(4,'dhcpdtemplate','default-lease-time 60;\r\nmax-lease-time 60;\r\nlog-facility local7;\r\n\r\noption option-150 code 150 = ip-address;\r\noption option-150 10.20.124.50;\r\noption routers 10.20.124.1;\r\n\r\nclass \"switchs\" {\r\n    match hardware;\r\n  \r\n}\r\n##autoinstall\r\n\r\nsubnet 0.0.0.0 netmask 0.0.0.0 {\r\n    pool {\r\n        allow members of \"switchs\";\r\n        range 10.20.124.51 10.20.124.51;\r\n        option subnet-mask 255.255.255.192;\r\n    }\r\n}\r\n\r\n\r\n\r\n'),(6,'dhcpdreload','sudo /usr/sbin/service isc-dhcp-server restart 2>&1'),(7,'dhcpdstatus','/usr/sbin/service isc-dhcp-server status'),(8,'dhcpdconffile','/etc/dhcp/dhcpd.conf'),(10,'boottemplate','file prompt quiet\r\ninterface vlan1\r\nip address dhcp\r\nip domain-name intranet\r\ncrypto key generate rsa modulus 1024\r\n\r\nip ssh version 2\r\n\r\naaa new-model\r\naaa authentication login default local \r\nusername local privilege 15 secret local\r\nline vty 0 15\r\ntransport input ssh\r\nprivilege level 15\r\nlogin authentication default \r\n');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `variables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variables` (
  `equipement_id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `equipement_id` (`equipement_id`),
  CONSTRAINT `variables_ibfk_1` FOREIGN KEY (`equipement_id`) REFERENCES `equipements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `events`;


CREATE TABLE `events` ( 
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `severity` int(10) unsigned NOT NULL default 0,
  `source` varchar(255),
  `date` datetime,
  `event` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

