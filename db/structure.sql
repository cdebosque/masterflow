-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Ven 17 Avril 2015 à 10:02
-- Version du serveur :  5.6.21
-- Version de PHP :  5.5.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `masterflow`
--
CREATE DATABASE IF NOT EXISTS `masterflow` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `masterflow`;

-- --------------------------------------------------------

--
-- Structure de la table `counter`
--

CREATE TABLE IF NOT EXISTS `counter` (
`id_counter` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `dataflow`
--

CREATE TABLE IF NOT EXISTS `dataflow` (
`id_dataflow` smallint(5) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `enable` tinyint(1) DEFAULT '1' COMMENT 'Activée / Désactivée',
  `type` varchar(45) DEFAULT NULL,
  `in_connection_type` varchar(45) DEFAULT NULL,
  `out_connection_type` varchar(45) DEFAULT NULL,
  `interface` varchar(255) NOT NULL COMMENT 'Chemin relatif vers le fichier XML de configuration d''interface',
  `mapping` varchar(255) NOT NULL COMMENT 'Chemin relatif vers le fichier de mapping',
  `observer` varchar(255) NOT NULL COMMENT 'Chemin relatif vers les fichiers d''observers'
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `dataflow_log`
--

CREATE TABLE IF NOT EXISTS `dataflow_log` (
`id_dataflow_log` int(10) unsigned NOT NULL,
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
  `in_source_file` varchar(255) NOT NULL COMMENT 'Fichier source'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
`id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `actif` tinyint(4) NOT NULL DEFAULT '1',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `routes`
--

CREATE TABLE IF NOT EXISTS `routes` (
`id` int(11) NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ignore` tinyint(4) NOT NULL DEFAULT '0',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `route_permissions`
--

CREATE TABLE IF NOT EXISTS `route_permissions` (
`id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) unsigned NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(255) DEFAULT NULL,
  `salt` varchar(255) NOT NULL DEFAULT '',
  `roles` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `time_created` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(100) DEFAULT NULL,
  `isEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `confirmationToken` varchar(100) DEFAULT NULL,
  `timePasswordResetRequested` int(11) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `user_custom_fields`
--

CREATE TABLE IF NOT EXISTS `user_custom_fields` (
  `user_id` int(11) unsigned NOT NULL,
  `attribute` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `counter`
--
ALTER TABLE `counter`
 ADD PRIMARY KEY (`id_counter`), ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `dataflow`
--
ALTER TABLE `dataflow`
 ADD PRIMARY KEY (`id_dataflow`), ADD KEY `label` (`name`);

--
-- Index pour la table `dataflow_log`
--
ALTER TABLE `dataflow_log`
 ADD PRIMARY KEY (`id_dataflow_log`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `routes`
--
ALTER TABLE `routes`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `route` (`path`);

--
-- Index pour la table `route_permissions`
--
ALTER TABLE `route_permissions`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `route_id` (`route_id`,`role_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `unique_email` (`email`), ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `user_custom_fields`
--
ALTER TABLE `user_custom_fields`
 ADD PRIMARY KEY (`user_id`,`attribute`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `counter`
--
ALTER TABLE `counter`
MODIFY `id_counter` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `dataflow`
--
ALTER TABLE `dataflow`
MODIFY `id_dataflow` smallint(5) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=212;
--
-- AUTO_INCREMENT pour la table `dataflow_log`
--
ALTER TABLE `dataflow_log`
MODIFY `id_dataflow_log` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT pour la table `routes`
--
ALTER TABLE `routes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT pour la table `route_permissions`
--
ALTER TABLE `route_permissions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=36;
--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
