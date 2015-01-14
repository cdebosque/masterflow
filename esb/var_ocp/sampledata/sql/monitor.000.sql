-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Mar 12 Février 2013 à 14:42
-- Version du serveur: 5.5.29
-- Version de PHP: 5.3.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `dev_esb`
--

-- --------------------------------------------------------

--
-- Structure de la table `dataflow`
--

CREATE TABLE IF NOT EXISTS `dataflow` (
  `id_dataflow` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `in_connection_type` varchar(45) DEFAULT NULL,
  `out_connection_type` varchar(45) DEFAULT NULL,
  `interface` varchar(255) NOT NULL COMMENT 'Chemin relatif vers le fichier XML de configuration d''interface',
  `mapping` varchar(255) NOT NULL COMMENT 'Chemin relatif vers le fichier de mapping',
  `observer` varchar(255) NOT NULL COMMENT 'Chemin relatif vers les fichiers d''observers',
  PRIMARY KEY (`id_dataflow`),
  KEY `label` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `dataflow_log`
--

CREATE TABLE IF NOT EXISTS `dataflow_log` (
  `id_dataflow_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_dataflow` smallint(5) unsigned DEFAULT NULL,
  `date_start` datetime NOT NULL,
  `date_finish` datetime NOT NULL,
  `warning` int(10) unsigned NOT NULL COMMENT 'Nombre d''avertissements globaux',
  `error` int(10) unsigned NOT NULL COMMENT 'Nombre d''erreurs globales',
  `fatal` int(10) unsigned NOT NULL COMMENT 'Nombre d''erreurs fatales globales',
  `in_raw_datas_fetched` int(10) unsigned NOT NULL COMMENT '[IN] Nombre de données lues',
  `in_raw_datas_error` int(10) unsigned NOT NULL COMMENT '|IN] Nombre d''erreurs',
  `in_raw_datas_warning` int(10) unsigned NOT NULL COMMENT '[IN] Nombre d''avertissements',
  `out_raw_datas_writed` int(10) unsigned NOT NULL COMMENT '[OUT] Nombre de données écrites',
  `out_raw_datas_error` int(10) unsigned NOT NULL COMMENT '[OUT] Nombre d''erreurs',
  `out_raw_datas_warning` int(10) unsigned NOT NULL COMMENT '[OUT] Nombre d''avertissements',
  `logfile` varchar(255) NOT NULL COMMENT 'Chemin relatif vers le fichier de log',
  PRIMARY KEY (`id_dataflow_log`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
