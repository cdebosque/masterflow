-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mer 15 Avril 2015 à 15:19
-- Version du serveur :  5.6.21
-- Version de PHP :  5.5.19

SET FOREIGN_KEY_CHECKS=0;
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

--
-- Contenu de la table `roles`
--

REPLACE INTO `roles` (`id`, `nom`, `description`, `actif`, `last_update`) VALUES
(1, 'ROLE_DEV', 'Définit le groupe d''utilisateur qui fait partie de l''équipe développement Nexecom', 1, '2015-04-13 10:35:41'),
(2, 'ROLE_DIRECTION', 'Définit le groupe d(utilisateur qui fait partie de la direction.', 1, '2015-04-13 10:36:06'),
(16, 'ROLE_CATALOGUE', '', 1, '2015-04-14 11:19:58');

--
-- Contenu de la table `routes`
--

REPLACE INTO `routes` (`id`, `path`, `nom`, `ignore`, `last_update`) VALUES
(2, '/user/', '', 0, '2015-04-13 15:13:08'),
(3, '/user/{id}', '', 0, '2015-04-13 15:13:08'),
(4, '/user/{id}/edit', '', 0, '2015-04-13 15:13:08'),
(5, '/user/list', '', 0, '2015-04-13 15:13:08'),
(6, '/user/register', '', 1, '2015-04-14 11:18:21'),
(7, '/roles', '', 0, '2015-04-13 15:13:08'),
(8, '/role/add', '', 0, '2015-04-13 15:13:08'),
(9, '/role/{id}/edit', '', 0, '2015-04-13 15:13:08'),
(10, '/roles-route', '', 0, '2015-04-13 15:13:08'),
(11, '/', '', 0, '2015-04-13 15:13:08'),
(12, '/admin', '', 0, '2015-04-13 15:19:05');

--
-- Contenu de la table `route_permissions`
--

REPLACE INTO `route_permissions` (`id`, `route_id`, `role_id`, `last_update`) VALUES
(1, 1, 1, '2015-04-13 14:10:50'),
(2, 1, 2, '2015-04-13 14:19:43'),
(7, 12, 1, '2015-04-14 09:35:28'),
(8, 12, 2, '2015-04-14 09:35:28'),
(13, 11, 2, '2015-04-14 10:04:45'),
(14, 11, 13, '2015-04-14 10:04:45'),
(15, 11, 14, '2015-04-14 10:04:45'),
(16, 11, 15, '2015-04-14 10:04:45'),
(17, 10, 2, '2015-04-14 10:10:23'),
(18, 9, 1, '2015-04-14 11:10:20'),
(19, 9, 2, '2015-04-14 11:10:20'),
(20, 8, 1, '2015-04-14 11:10:27'),
(21, 8, 2, '2015-04-14 11:10:27'),
(22, 7, 1, '2015-04-14 11:10:33'),
(23, 7, 2, '2015-04-14 11:10:33'),
(24, 10, 1, '2015-04-14 11:10:48'),
(25, 2, 1, '2015-04-14 11:11:58'),
(26, 2, 2, '2015-04-14 11:11:58'),
(27, 3, 1, '2015-04-14 11:12:06'),
(28, 3, 2, '2015-04-14 11:12:07'),
(29, 4, 1, '2015-04-14 11:12:12'),
(30, 4, 2, '2015-04-14 11:12:12'),
(31, 5, 1, '2015-04-14 11:12:18'),
(32, 5, 2, '2015-04-14 11:12:18'),
(33, 6, 1, '2015-04-14 11:12:23'),
(34, 6, 2, '2015-04-14 11:12:23');

--
-- Contenu de la table `users`
--

REPLACE INTO `users` (`id`, `email`, `password`, `salt`, `roles`, `name`, `time_created`, `username`, `isEnabled`, `confirmationToken`, `timePasswordResetRequested`) VALUES
(1, 'christophe@nexecom.fr', 'LMuIYo4fVZL3Iofb881Mz5biO3PeJPLiUXqCrE7d79GVoHJGfD62ZWLIxRAAdXLwzutnQjO4btrab2IJVwEIGA==', 'b0tty36qse0w0cs4w8cgs4w4cwk8wcc', 'ROLE_DIRECTION', 'Direction', 1428663010, NULL, 1, NULL, NULL),
(4, 'christophe.torres@nexecom.fr', 'Qb51QUoFLgOdxkCCn8x7pnB//JImF5t1BK2O1ylEr6bdU9sqQXlhCDfHuHnBmaW7qkh2rutQy2OCmab88HNe2g==', 'omphjksn42swkk4gk8ggw4gso048c', 'ROLE_DEV', 'Christophe Torres', 1428672287, NULL, 1, NULL, 1428914879),
(5, 'test@example.com', '8pAor3lVyNf+Pyl1nKhotdc32pp7KHa8uBWDBqnBah2Acv1pfM4Mq43/F7jXyUmaqTS9iip7f37O9eR7IJcBWg==', 'odkjugf79gggwos008ss0kss484wg44', 'ROLE_USER', 'masterflow', 1429019060, NULL, 1, NULL, NULL),
(6, 'sebastien@nexecom.fr', 'UDDmUMA2KkvWppzF321gsZkEg6li0g4n0m2Y+xKDnk07DxrDpeVF3pH3DMsaEw6z8hHRBN2gC9KgcpkNiJMYQw==', 'pkfl2ziyqog8gcwockswwkko8wso8cg', 'ROLE_DEV', 'Sebastien', 1429019201, NULL, 1, NULL, NULL);
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
