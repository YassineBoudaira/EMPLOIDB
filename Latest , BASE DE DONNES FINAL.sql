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


-- Listage de la structure de la base pour emploi
DROP DATABASE IF EXISTS `emploi`;
CREATE DATABASE IF NOT EXISTS `emploi` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `emploi`;

-- Listage de la structure de la table emploi. annonces
DROP TABLE IF EXISTS `annonces`;
CREATE TABLE IF NOT EXISTS `annonces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_a` date DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `telephone` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entreprise` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entreprise_detaile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `siteweb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `contrat_id` int(11) DEFAULT NULL,
  `ville_id` int(11) DEFAULT NULL,
  `domaine_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__profiles` (`profile_id`),
  KEY `FK_annances_contrats` (`contrat_id`),
  KEY `FK_annances_villes` (`ville_id`),
  KEY `FK_annances_domaines` (`domaine_id`),
  CONSTRAINT `FK__profiles` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`),
  CONSTRAINT `FK_annances_contrats` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`),
  CONSTRAINT `FK_annances_domaines` FOREIGN KEY (`domaine_id`) REFERENCES `domaines` (`id`),
  CONSTRAINT `FK_annances_villes` FOREIGN KEY (`ville_id`) REFERENCES `villes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.annonces : ~5 rows (environ)
/*!40000 ALTER TABLE `annonces` DISABLE KEYS */;
INSERT IGNORE INTO `annonces` (`id`, `titre`, `date_a`, `image`, `description`, `telephone`, `email`, `entreprise`, `entreprise_detaile`, `siteweb`, `date_fin`, `profile_id`, `contrat_id`, `ville_id`, `domaine_id`) VALUES
	(1, 'Technicien en Maintenance et Reseaux', '2022-04-28', '382_117233750.jpg', 'Vous trouverez en piÃ¨ce jointe ma candidature au poste d\'Assistant Chain dans votre service logistique de Luxembourg. Je r\r\neste Ã  votre entiÃ¨re disposition pour de plus amples informations. En vous remerciant par avance de lâ€™attention que vous porterez Ã  ma candidature, Monsieur Dupuis\r\n\r\nVous trouverez en piÃ¨ce jointe ma candidature au poste d\'Assistant Ch', '0697564008', 'saida@sdaa.com', 'Devosoft', 'pour les developeur', 'www.DevoSoft.com', '2022-05-29', 3, 4, 9, 13),
	(2, 'Ingenieur Developement-et-System', '2022-04-29', '4.jpg', 'Vous trouverez en piÃ¨ce jointe ma candidature au poste d\'Assistant Chain dans votre service logistique de Luxembourg. Je reste Ã  votre entiÃ¨re disposition pour de plus amples informations. En vous remerciant par avance de lâ€™attention que vous porterez Ã  ma candidature, Monsieur Dupuis', '06878656583', 'ZAD1996HAXER@gmail.com', 'ZIDEX', 'pour les systemes d\'administration', 'www,ZIDEX.COM', '2022-07-01', 2, 5, 3, 5),
	(3, 'Professeur Englais', '2022-05-15', 'win.jpg', 'Il enseigne la langue anglaise aux collégiens, aux lycéens ou aux étudiants en université mais aussi aux adultes et professionnels. Il élabore des cours et exercices selon le niveau du public auquel il enseigne. Il explique les règles de grammaire, de conjugaison, ainsi que l\'orthographe et le vocabulaire. Le professeur d\'anglais a également pour rôle de faire découvrir la culture anglophone à ses élèves. Pour ce faire, il utilise des supports divers et variés : articles de journaux, chansons, textes littéraires, films, émissions de télé etc. Il sait captiver l\'attention de son public en transmettant le goût de la langue. Le professeur d\'anglais suit un programme imposé par l\'éducation nationale, mais a cependant une part de liberté quant au choix des outils pédagogiques et des méthodes d\'enseignement (musique, sketchs, lecture phonétique ...). Il évalue régulièrement ses élèves sur l\'acquis des connaissances, puis les corrige.', '069879555599', 'mohamedhaj@gmail.com', 'ISTA', 'societé specifique', 'www.ista.com', '2022-05-22', 5, 3, 2, 16),
	(4, 'Technicienne', '2022-05-17', '6.jpg', 'Il enseigne la langue anglaise aux collÃ©giens', '433222323', 'test@test.com', 'teser', 'iens, de grammaire, ', 'www.test.com', '2022-05-25', 1, 1, 1, 1),
	(5, 'chef de transpot', '2022-05-18', 'download-removebg-.png', ' Il ï¿½labore des cours et exercices selon le niveau du public auquel il enseigne. Il explique les rï¿½gles de grammaire, ', '06765664648', 'vectalia.gmail.com', 'vectalia safi', ' Il ï¿½labore des cours et exercices selon le niveau du public auquel il enseigne. Il explique les rï¿½gles de grammaire, de conjugaison', 'safi.vectalia.com', '2022-05-31', 1, 3, 21, 21);
/*!40000 ALTER TABLE `annonces` ENABLE KEYS */;

-- Listage de la structure de la table emploi. article
DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `article` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.article : ~0 rows (environ)
/*!40000 ALTER TABLE `article` DISABLE KEYS */;
/*!40000 ALTER TABLE `article` ENABLE KEYS */;

-- Listage de la structure de la table emploi. contrats
DROP TABLE IF EXISTS `contrats`;
CREATE TABLE IF NOT EXISTS `contrats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.contrats : ~6 rows (environ)
/*!40000 ALTER TABLE `contrats` DISABLE KEYS */;
INSERT IGNORE INTO `contrats` (`id`, `nom`) VALUES
	(1, 'CDD'),
	(2, 'CDI'),
	(3, 'ANAPEC'),
	(4, 'STAGE'),
	(5, 'FORMATION'),
	(6, 'FREELANCE');
/*!40000 ALTER TABLE `contrats` ENABLE KEYS */;

-- Listage de la structure de la table emploi. domaines
DROP TABLE IF EXISTS `domaines`;
CREATE TABLE IF NOT EXISTS `domaines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.domaines : ~17 rows (environ)
/*!40000 ALTER TABLE `domaines` DISABLE KEYS */;
INSERT IGNORE INTO `domaines` (`id`, `nom`) VALUES
	(1, 'Commerce'),
	(2, 'Gestion'),
	(3, 'Comptabilite'),
	(4, 'Finance'),
	(5, 'Informatique'),
	(6, 'Nouvelles Technologies'),
	(8, 'Management'),
	(9, 'Marketing, Communication'),
	(10, 'La Santé'),
	(11, 'Services '),
	(12, 'Production'),
	(13, 'Maintenance'),
	(14, 'R&D, Gestion de projet'),
	(15, 'Ressources Humaines'),
	(16, 'Formation'),
	(17, 'Secrétariat, Assistanat'),
	(18, 'Tourisme'),
	(19, 'Hôtellerie'),
	(20, 'Rrestauration'),
	(21, 'Transport, Logistique'),
	(22, 'insfrastracture');
/*!40000 ALTER TABLE `domaines` ENABLE KEYS */;

-- Listage de la structure de la table emploi. postulation
DROP TABLE IF EXISTS `postulation`;
CREATE TABLE IF NOT EXISTS `postulation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `annonce_id` int(11) DEFAULT NULL,
  `nom_prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `FK_postulation_annonces` (`annonce_id`),
  CONSTRAINT `FK_postulation_annonces` FOREIGN KEY (`annonce_id`) REFERENCES `annonces` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.postulation : ~5 rows (environ)
/*!40000 ALTER TABLE `postulation` DISABLE KEYS */;
INSERT IGNORE INTO `postulation` (`id`, `annonce_id`, `nom_prenom`, `telephone`, `email`, `lien`, `fichier`, `message`) VALUES
	(1, 1, 'samir bdr', '0666039499', 'samir@boura.com', 'myportfolio.com', '', 'Je suis Samir Baira, 22 ans, Technicien RÃ©seaux et systÃ¨mes de communications passionnÃ© par lâ€™administration et la sÃ©curitÃ© des rÃ©seaux informatiques,'),
	(3, 2, 'anika jani', '0774646363', 'ZADffg6HAXER@gmail.com', 'www.linkedin.com', 'RAPPORT DE STAtE.pdf', ' Bonjour / Madame, Monsieur, Vous trouverez en piÃ¨ce jointe ma candidature au poste d\'Assistant Supply Chain dans votre service logistique de Luxembourg. Je reste Ã  votre entiÃ¨re disposition pour de plus amples informations. En vous remerciant par avance de lâ€™attention que vous porterez Ã  ma candidature, Monsieur Dupuis'),
	(8, 1, 'hichame nanos', '0687877600', 'ANA9A19ANA@gmail.com', 'www.dgdsd.com', 'CDC-YassineBoudaira LearningHa.com.pdf', ' Invalid parameter number: number of bound variables does not match number of tokens in C:\\laragon\\www\\emploiDB\\validation.php on line 16'),
	(9, 5, 'zad hacker', '0699978877', 'zaddfgdfhacker@gmail.com', 'www.google.ma', 'UseCase.pdf', 'http://localhost:8080/emploiDB/domend/domandes.php\r\nhttp://localhost:8080/emploiDB/domend/domandes.php\r\nhttp://localhost:8080/emploiDB/domend/domandes.php\r\nhttp://localhost:8080/emploiDB/domend/domandes.php'),
	(10, 5, 'mohammed oumi', '0698976878', 'mohammfdgmi@gmail.com', 'www.linkedin.com', 'RAPPORT DE STAGE jobmaroc.pdf', 'dfhhskldjqhfildglqsjkdlfglsdhfjsdhisdfuihjmohammed ouhmimohammed ouhmimohammed ouhmimohammed ouhmimohammed ouhmi'),
	(11, 1, 'anika', 'ryeryzery', 'ani9aani9a@outlook.com', 'www.dgdsd.com', '8pagePDF.pdf', 'rgsdfhhhhhhhhhhhhhhhhhhhhhhhhhgjtdyhtejty'),
	(12, 3, '', '', '', '', '', '');
/*!40000 ALTER TABLE `postulation` ENABLE KEYS */;

-- Listage de la structure de la table emploi. profiles
DROP TABLE IF EXISTS `profiles`;
CREATE TABLE IF NOT EXISTS `profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_n` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ville_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__users` (`user_id`),
  KEY `FK__villes` (`ville_id`),
  CONSTRAINT `FK__users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK__villes` FOREIGN KEY (`ville_id`) REFERENCES `villes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.profiles : ~5 rows (environ)
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;
INSERT IGNORE INTO `profiles` (`id`, `nom`, `prenom`, `telephone`, `adresse`, `date_n`, `user_id`, `ville_id`) VALUES
	(1, 'yassine', 'boudaira', '06 11435454', 'QU HRILA RUE LIBERTE GZOULA ', '1996-04-24', 1, 1),
	(2, 'hasnae', 'isis', '06 0000 0-000', 'RUE 360 NB TIFLET', '1999-03-28', 2, 2),
	(3, 'saida', 'ilqs', '06 66 435678', 'QU Hrilla Rue la libertÃ© Gzoula', '2003-03-27', 1, 9),
	(4, 'hicham', 'rguina', '0600000000', 'DEVOSOFT@devo', '1988-07-19', 2, 1),
	(5, 'mohammed', 'hj1', '0680905950', 'mohamedhj@gmail.com', '1997-03-15', 2, 1),
	(6, 'KHAOULA', 'fdl', '0660000200', 'safiistantic', '2022-05-26', 2, 5);
/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;

-- Listage de la structure de la table emploi. users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.users : ~2 rows (environ)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT IGNORE INTO `users` (`id`, `user`, `pass`, `email`, `role`) VALUES
	(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin.com', 'admin'),
	(2, 'user', 'ee11cbb19052e40b07aac0ca060c23ee', 'user@user.com', 'user'),
	(3, 'ista', '20b363233c20f74cb2d94e4890cde953', 'ista@ista', 'user'),
	(4, 'prof', 'd450c5dbcc10db0749277efc32f15f9f', 'ANA9A19dtANA@gmail.com', 'user');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

-- Listage de la structure de la table emploi. villes
DROP TABLE IF EXISTS `villes`;
CREATE TABLE IF NOT EXISTS `villes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table emploi.villes : ~22 rows (environ)
/*!40000 ALTER TABLE `villes` DISABLE KEYS */;
INSERT IGNORE INTO `villes` (`id`, `nom`) VALUES
	(1, 'Safi'),
	(2, 'Marrakech'),
	(3, 'Tiflet'),
	(5, 'Casablanca'),
	(6, 'Fés'),
	(7, 'Tanger'),
	(8, 'Salé'),
	(9, 'Meknes'),
	(10, 'Oujda'),
	(11, 'Rabat'),
	(12, 'Kenitra'),
	(13, 'Agadir'),
	(14, 'Tétouan'),
	(15, 'Temara'),
	(16, 'Mohammedia'),
	(17, 'Khouribga'),
	(18, 'El Jadida'),
	(19, 'Béni Mellal'),
	(20, 'Taza'),
	(21, 'Nador'),
	(22, 'Khemisset'),
	(23, 'laayoune'),
	(24, 'Berkane');
/*!40000 ALTER TABLE `villes` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
