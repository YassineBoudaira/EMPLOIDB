-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           5.7.33 - MySQL Community Server (GPL)
-- SE du serveur:                Win64
-- HeidiSQL Version:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Listage de la structure de la base pour ecommerceit
DROP DATABASE IF EXISTS `ecommerceitmysql`;
CREATE DATABASE IF NOT EXISTS `ecommerceit` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `ecommerceit`;

-- Listage de la structure de la table ecommerceit. appros
DROP TABLE IF EXISTS `appros`;
CREATE TABLE IF NOT EXISTS `appros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(11) NOT NULL DEFAULT '0',
  `date` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `four_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK__fournisseurs` (`four_id`),
  CONSTRAINT `FK__fournisseurs` FOREIGN KEY (`four_id`) REFERENCES `fournisseurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.appros : ~0 rows (environ)
/*!40000 ALTER TABLE `appros` DISABLE KEYS */;
/*!40000 ALTER TABLE `appros` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. appros_prods
DROP TABLE IF EXISTS `appros_prods`;
CREATE TABLE IF NOT EXISTS `appros_prods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prod_id` int(11) NOT NULL DEFAULT '0',
  `appro_id` int(11) NOT NULL DEFAULT '0',
  `qte` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK__produits` (`prod_id`),
  KEY `FK__appros` (`appro_id`),
  CONSTRAINT `FK__appros` FOREIGN KEY (`appro_id`) REFERENCES `appros` (`id`),
  CONSTRAINT `FK__produits` FOREIGN KEY (`prod_id`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.appros_prods : ~0 rows (environ)
/*!40000 ALTER TABLE `appros_prods` DISABLE KEYS */;
/*!40000 ALTER TABLE `appros_prods` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- Listage des données de la table ecommerceit.categories : ~6 rows (environ)
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `nom`) VALUES
	(1, 'PC'),
	(2, 'mobile'),
	(3, 'camera'),
	(4, 'Print'),
	(7, 'MOUSE'),
	(8, 'Keyboard');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. clients
DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.clients : ~2 rows (environ)
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` (`id`, `nom`, `adresse`, `email`, `telephone`) VALUES
	(1, 'rguina', 'N 14 Massira', 'rguina@gmail.com', '0660994282'),
	(2, 'reda reda', 'QU 24  N 60 60', 'reda@gmail.com', '0660601040');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. cmds_prods
DROP TABLE IF EXISTS `cmds_prods`;
CREATE TABLE IF NOT EXISTS `cmds_prods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(11) NOT NULL DEFAULT '0',
  `prod_id` int(11) NOT NULL DEFAULT '0',
  `cmd_id` int(11) NOT NULL DEFAULT '0',
  `qte` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_cmds_prods_produits` (`prod_id`),
  KEY `FK_cmds_prods_commandes` (`cmd_id`),
  CONSTRAINT `FK_cmds_prods_commandes` FOREIGN KEY (`cmd_id`) REFERENCES `commandes` (`id`),
  CONSTRAINT `FK_cmds_prods_produits` FOREIGN KEY (`prod_id`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.cmds_prods : ~0 rows (environ)
/*!40000 ALTER TABLE `cmds_prods` DISABLE KEYS */;
/*!40000 ALTER TABLE `cmds_prods` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. commandes
DROP TABLE IF EXISTS `commandes`;
CREATE TABLE IF NOT EXISTS `commandes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commandes_clients` (`client_id`),
  CONSTRAINT `FK_commandes_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.commandes : ~0 rows (environ)
/*!40000 ALTER TABLE `commandes` DISABLE KEYS */;
/*!40000 ALTER TABLE `commandes` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. fournisseurs
DROP TABLE IF EXISTS `fournisseurs`;
CREATE TABLE IF NOT EXISTS `fournisseurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.fournisseurs : ~0 rows (environ)
/*!40000 ALTER TABLE `fournisseurs` DISABLE KEYS */;
INSERT INTO `fournisseurs` (`id`, `nom`, `adresse`, `email`, `telephone`) VALUES
	(1, 'zakaria', 'jreyfat rue 1500', 'zaki@gmail.com', '0550406040');
/*!40000 ALTER TABLE `fournisseurs` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. produits
DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `prix_u` float NOT NULL DEFAULT '0',
  `prix_achat` float NOT NULL DEFAULT '0',
  `prix_vente` float NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `qte_stock` int(11) DEFAULT NULL,
  `cat_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK__categories` (`cat_id`),
  CONSTRAINT `FK__categories` FOREIGN KEY (`cat_id`emploi) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.produits : ~0 rows (environ)
/*!40000 ALTER TABLE `produits` DISABLE KEYS */;
/*!40000 ALTER TABLE `produits` ENABLE KEYS */;

-- Listage de la structure de la table ecommerceit. users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table ecommerceit.users : ~2 rows (environ)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `user`, `pass`, `email`, `role`) VALUES
	(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin.com', 'admin'),
	(3, 'ssss', 'af15d5fdacd5fdfea300e88a8e253e82', 'rguinahicham993@gmail.com', 'utilisateur');
	
	
	
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*____________________________table profiles________________*/
DROP TABLE IF EXISTS `profiles`;
CREATE TABLE IF NOT EXISTS `profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` int(11) NOT NULL DEFAULT '0',
 `prenom` VARCHAR(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Tel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dateNais` date COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  user-idemploiemploiemploi
  
  PRIMARY KEY (`id`),
  KEY `FK_cmds_prods_produits` (`user-id`),
  KEY `FK_cmds_prods_commandes` (`ville-id`),
  CONSTRAINT `FK_cmds_prods_commandes` FOREIGN KEY (`user-id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_cmds_prods_produits` FOREIGN KEY (`ville-id`) REFERENCES `villeemplois` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
