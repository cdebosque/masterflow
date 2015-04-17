-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Ven 17 Avril 2015 à 10:11
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
-- Contenu de la table `dataflow`
--

REPLACE INTO `dataflow` (`id_dataflow`, `name`, `enable`, `type`, `in_connection_type`, `out_connection_type`, `interface`, `mapping`, `observer`) VALUES
(107, 'Samples\\customer\\csv2db', 1, NULL, NULL, NULL, 'etc\\Samples\\customer\\csv2db\\interface.xml', 'etc\\Samples\\customer\\csv2db\\mapping.xml', ''),
(108, 'Samples\\customer\\csv2xml', 1, NULL, NULL, NULL, 'etc\\Samples\\customer\\csv2xml\\interface.xml', 'etc\\Samples\\customer\\csv2xml\\mapping.xml', ''),
(109, 'Samples\\customer\\xml2csv', 1, NULL, NULL, NULL, 'etc\\Samples\\customer\\xml2csv\\interface.xml', 'etc\\Samples\\customer\\xml2csv\\mapping.xml', ''),
(110, 'core\\base', 1, NULL, NULL, NULL, 'etc\\core\\base\\interface.xml', 'etc\\core\\base\\mapping.xml', ''),
(111, 'migrate\\dpam\\attributes', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\attributes\\interface.xml', 'etc\\migrate\\dpam\\attributes\\mapping.xml', ''),
(112, 'migrate\\dpam\\category\\export_soapxml', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\category\\export_soapxml\\interface.xml', 'etc\\migrate\\dpam\\category\\export_soapxml\\mapping.xml', ''),
(113, 'migrate\\dpam\\category\\import_csvsoap', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\category\\import_csvsoap\\interface.xml', 'etc\\migrate\\dpam\\category\\import_csvsoap\\mapping.xml', ''),
(114, 'migrate\\dpam\\category\\import_xmlsoap', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\category\\import_xmlsoap\\interface.xml', 'etc\\migrate\\dpam\\category\\import_xmlsoap\\mapping.xml', ''),
(115, 'migrate\\dpam\\category\\synch_products_csvsoap', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\category\\synch_products_csvsoap\\interface.xml', 'etc\\migrate\\dpam\\category\\synch_products_csvsoap\\mapping.xml', ''),
(116, 'migrate\\dpam\\child', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\child\\interface.xml', 'etc\\migrate\\dpam\\child\\mapping.xml', ''),
(117, 'migrate\\dpam\\collections', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\collections\\interface.xml', 'etc\\migrate\\dpam\\collections\\mapping.xml', ''),
(118, 'migrate\\dpam\\sales_order\\csv_soap', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\sales_order\\csv_soap\\interface.xml', 'etc\\migrate\\dpam\\sales_order\\csv_soap\\mapping.xml', ''),
(119, 'migrate\\dpam\\sales_order\\xml_mage', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\sales_order\\xml_mage\\interface.xml', 'etc\\migrate\\dpam\\sales_order\\xml_mage\\mapping.xml', ''),
(120, 'migrate\\dpam\\sales_order\\xml_soap', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\sales_order\\xml_soap\\interface.xml', 'etc\\migrate\\dpam\\sales_order\\xml_soap\\mapping.xml', ''),
(121, 'migrate\\dpam\\shops\\csv_soap', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\shops\\csv_soap\\interface.xml', 'etc\\migrate\\dpam\\shops\\csv_soap\\mapping.xml', ''),
(122, 'migrate\\dpam\\wishlist', 1, NULL, NULL, NULL, 'etc\\migrate\\dpam\\wishlist\\interface.xml', 'etc\\migrate\\dpam\\wishlist\\mapping.xml', ''),
(123, 'migrate\\oclio\\customers', 1, NULL, NULL, NULL, 'etc\\migrate\\oclio\\customers\\interface.xml', 'etc\\migrate\\oclio\\customers\\mapping.xml', ''),
(124, 'migrate\\oclio\\customers\\csv', 1, NULL, NULL, NULL, 'etc\\migrate\\oclio\\customers\\csv\\interface.xml', 'etc\\migrate\\oclio\\customers\\csv\\mapping.xml', ''),
(125, 'migrate\\oclio\\products', 1, NULL, NULL, NULL, 'etc\\migrate\\oclio\\products\\interface.xml', 'etc\\migrate\\oclio\\products\\mapping.xml', ''),
(126, 'migrate\\oclio\\products\\toxml', 1, NULL, NULL, NULL, 'etc\\migrate\\oclio\\products\\toxml\\interface.xml', 'etc\\migrate\\oclio\\products\\toxml\\mapping.xml', ''),
(127, 'migrate\\sales_order\\csv_soap', 1, NULL, NULL, NULL, 'etc\\migrate\\sales_order\\csv_soap\\interface.xml', 'etc\\migrate\\sales_order\\csv_soap\\mapping.xml', ''),
(128, 'migrate\\sales_order\\xml_mage', 1, NULL, NULL, NULL, 'etc\\migrate\\sales_order\\xml_mage\\interface.xml', 'etc\\migrate\\sales_order\\xml_mage\\mapping.xml', ''),
(129, 'migrate\\sales_order\\xml_soap', 1, NULL, NULL, NULL, 'etc\\migrate\\sales_order\\xml_soap\\interface.xml', 'etc\\migrate\\sales_order\\xml_soap\\mapping.xml', ''),
(130, 'migrate\\wishlist\\xml_mage', 1, NULL, NULL, NULL, 'etc\\migrate\\wishlist\\xml_mage\\interface.xml', '', ''),
(131, 'ocp\\default\\customer\\in', 1, NULL, NULL, NULL, 'etc\\ocp\\default\\customer\\in\\interface.xml', 'etc\\ocp\\default\\customer\\in\\mapping.xml', ''),
(132, 'ocp\\default\\product_attributes\\export_soap', 1, NULL, NULL, NULL, 'etc\\ocp\\default\\product_attributes\\export_soap\\interface.xml', 'etc\\ocp\\default\\product_attributes\\export_soap\\mapping.xml', ''),
(133, 'ocp\\default\\product_attributes_labels\\export_soap', 1, NULL, NULL, NULL, 'etc\\ocp\\default\\product_attributes_labels\\export_soap\\interface.xml', 'etc\\ocp\\default\\product_attributes_labels\\export_soap\\mapping.xml', ''),
(134, 'ocp\\default\\product_attributes_set\\export_soap', 1, NULL, NULL, NULL, 'etc\\ocp\\default\\product_attributes_set\\export_soap\\interface.xml', 'etc\\ocp\\default\\product_attributes_set\\export_soap\\mapping.xml', ''),
(135, 'ocp\\default\\products\\export_soap', 1, NULL, NULL, NULL, 'etc\\ocp\\default\\products\\export_soap\\interface.xml', 'etc\\ocp\\default\\products\\export_soap\\mapping.xml', ''),
(136, 'ocp\\init\\category\\export_csv', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\category\\export_csv\\interface.xml', 'etc\\ocp\\init\\category\\export_csv\\mapping.xml', ''),
(137, 'ocp\\init\\category\\export_soapxml', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\category\\export_soapxml\\interface.xml', 'etc\\ocp\\init\\category\\export_soapxml\\mapping.xml', ''),
(138, 'ocp\\init\\category\\exportbylevel_soapxml', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\category\\exportbylevel_soapxml\\interface.xml', 'etc\\ocp\\init\\category\\exportbylevel_soapxml\\mapping.xml', ''),
(139, 'ocp\\init\\category\\import_csvsoap', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\category\\import_csvsoap\\interface.xml', 'etc\\ocp\\init\\category\\import_csvsoap\\mapping.xml', ''),
(140, 'ocp\\init\\category\\import_xmlsoap', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\category\\import_xmlsoap\\interface.xml', 'etc\\ocp\\init\\category\\import_xmlsoap\\mapping.xml', ''),
(141, 'ocp\\init\\customer\\incsv', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\customer\\incsv\\interface.xml', 'etc\\ocp\\init\\customer\\incsv\\mapping.xml', ''),
(142, 'ocp\\init\\customer\\inxml', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\customer\\inxml\\interface.xml', 'etc\\ocp\\init\\customer\\inxml\\mapping.xml', ''),
(143, 'ocp\\init\\product_attributes\\export_soapxml', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes\\export_soapxml\\interface.xml', 'etc\\ocp\\init\\product_attributes\\export_soapxml\\mapping.xml', ''),
(144, 'ocp\\init\\product_attributes\\import_xmlsoap', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes\\import_xmlsoap\\interface.xml', 'etc\\ocp\\init\\product_attributes\\import_xmlsoap\\mapping.xml', ''),
(145, 'ocp\\init\\product_attributes_labels\\export_magexml', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_labels\\export_magexml\\interface.xml', 'etc\\ocp\\init\\product_attributes_labels\\export_magexml\\mapping.xml', ''),
(146, 'ocp\\init\\product_attributes_labels\\export_soapcsv', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_labels\\export_soapcsv\\interface.xml', 'etc\\ocp\\init\\product_attributes_labels\\export_soapcsv\\mapping.xml', ''),
(147, 'ocp\\init\\product_attributes_labels\\export_soapxml', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_labels\\export_soapxml\\interface.xml', 'etc\\ocp\\init\\product_attributes_labels\\export_soapxml\\mapping.xml', ''),
(148, 'ocp\\init\\product_attributes_labels\\import_csvmage', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_labels\\import_csvmage\\interface.xml', 'etc\\ocp\\init\\product_attributes_labels\\import_csvmage\\mapping.xml', ''),
(149, 'ocp\\init\\product_attributes_labels\\import_csvsoap', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_labels\\import_csvsoap\\interface.xml', 'etc\\ocp\\init\\product_attributes_labels\\import_csvsoap\\mapping.xml', ''),
(150, 'ocp\\init\\product_attributes_options\\export_csvsoap', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_options\\export_csvsoap\\interface.xml', 'etc\\ocp\\init\\product_attributes_options\\export_csvsoap\\mapping.xml', ''),
(151, 'ocp\\init\\product_attributes_options\\import_csvsoap', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_options\\import_csvsoap\\interface.xml', 'etc\\ocp\\init\\product_attributes_options\\import_csvsoap\\mapping.xml', ''),
(152, 'ocp\\init\\product_attributes_set\\export_soapxml', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_set\\export_soapxml\\interface.xml', 'etc\\ocp\\init\\product_attributes_set\\export_soapxml\\mapping.xml', ''),
(153, 'ocp\\init\\product_attributes_set\\import_xmlsoap', 1, NULL, NULL, NULL, 'etc\\ocp\\init\\product_attributes_set\\import_xmlsoap\\interface.xml', 'etc\\ocp\\init\\product_attributes_set\\import_xmlsoap\\mapping.xml', ''),
(154, 'ocp\\test', 1, NULL, NULL, NULL, 'etc\\ocp\\test\\interface.xml', '', ''),
(155, 'partners\\antidot\\categories', 1, NULL, NULL, NULL, 'etc\\partners\\antidot\\categories\\interface.xml', 'etc\\partners\\antidot\\categories\\mapping.xml', ''),
(156, 'partners\\antidot\\product', 1, NULL, NULL, NULL, 'etc\\partners\\antidot\\product\\interface.xml', 'etc\\partners\\antidot\\product\\mapping.xml', ''),
(157, 'partners\\colombus\\bt1', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\bt1\\interface.xml', 'etc\\partners\\colombus\\bt1\\mapping.xml', ''),
(158, 'partners\\colombus\\bt2', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\bt2\\interface.xml', 'etc\\partners\\colombus\\bt2\\mapping.xml', ''),
(159, 'partners\\colombus\\cm', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\cm\\interface.xml', 'etc\\partners\\colombus\\cm\\mapping.xml', ''),
(160, 'partners\\colombus\\co\\import_csvsoap', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\co\\import_csvsoap\\interface.xml', 'etc\\partners\\colombus\\co\\import_csvsoap\\mapping.xml', ''),
(161, 'partners\\colombus\\cp\\import_csvsoap', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\cp\\import_csvsoap\\interface.xml', 'etc\\partners\\colombus\\cp\\import_csvsoap\\mapping.xml', ''),
(162, 'partners\\colombus\\description', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\description\\interface.xml', 'etc\\partners\\colombus\\description\\mapping.xml', ''),
(163, 'partners\\colombus\\description_it', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\description_it\\interface.xml', 'etc\\partners\\colombus\\description_it\\mapping.xml', ''),
(164, 'partners\\colombus\\dm\\import_csvsoap', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\dm\\import_csvsoap\\interface.xml', 'etc\\partners\\colombus\\dm\\import_csvsoap\\mapping.xml', ''),
(165, 'partners\\colombus\\media', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\media\\interface.xml', '', ''),
(166, 'partners\\colombus\\ob', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\ob\\interface.xml', 'etc\\partners\\colombus\\ob\\mapping.xml', ''),
(167, 'partners\\colombus\\ob_original', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\ob_original\\interface.xml', 'etc\\partners\\colombus\\ob_original\\mapping.xml', ''),
(168, 'partners\\colombus\\price', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\price\\interface.xml', 'etc\\partners\\colombus\\price\\mapping.xml', ''),
(169, 'partners\\colombus\\price_sql', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\price_sql\\interface.xml', 'etc\\partners\\colombus\\price_sql\\mapping.xml', ''),
(170, 'partners\\colombus\\products', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\products\\interface.xml', 'etc\\partners\\colombus\\products\\mapping.xml', ''),
(171, 'partners\\colombus\\products\\update', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\products\\update\\interface.xml', 'etc\\partners\\colombus\\products\\update\\mapping.xml', ''),
(172, 'partners\\colombus\\rt', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\rt\\interface.xml', 'etc\\partners\\colombus\\rt\\mapping.xml', ''),
(173, 'partners\\colombus\\sales\\invoiced', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\sales\\invoiced\\interface.xml', 'etc\\partners\\colombus\\sales\\invoiced\\mapping.xml', ''),
(174, 'partners\\colombus\\sales\\refunded', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\sales\\refunded\\interface.xml', 'etc\\partners\\colombus\\sales\\refunded\\mapping.xml', ''),
(175, 'partners\\colombus\\sales_preprod\\invoiced', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\sales_preprod\\invoiced\\interface.xml', 'etc\\partners\\colombus\\sales_preprod\\invoiced\\mapping.xml', ''),
(176, 'partners\\colombus\\sales_preprod\\refunded', 1, NULL, NULL, NULL, 'etc\\partners\\colombus\\sales_preprod\\refunded\\interface.xml', 'etc\\partners\\colombus\\sales_preprod\\refunded\\mapping.xml', ''),
(177, 'partners\\crosslog\\cdmcard', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\cdmcard\\interface.xml', 'etc\\partners\\crosslog\\cdmcard\\mapping.xml', ''),
(178, 'partners\\crosslog\\orders\\cancel', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\cancel\\interface.xml', '', ''),
(179, 'partners\\crosslog\\orders\\changeStatus', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\changeStatus\\interface.xml', 'etc\\partners\\crosslog\\orders\\changeStatus\\mapping.xml', ''),
(180, 'partners\\crosslog\\orders\\exportNew', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\exportNew\\interface.xml', 'etc\\partners\\crosslog\\orders\\exportNew\\mapping.xml', ''),
(181, 'partners\\crosslog\\orders\\exportShippingOrder', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\exportShippingOrder\\interface.xml', '', ''),
(182, 'partners\\crosslog\\orders\\exportVerified', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\exportVerified\\interface.xml', '', ''),
(183, 'partners\\crosslog\\orders\\importBlockedStatus', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\importBlockedStatus\\interface.xml', '', ''),
(184, 'partners\\crosslog\\orders\\importChange', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\importChange\\interface.xml', 'etc\\partners\\crosslog\\orders\\importChange\\mapping.xml', ''),
(185, 'partners\\crosslog\\orders\\importChangeStatus', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\importChangeStatus\\interface.xml', 'etc\\partners\\crosslog\\orders\\importChangeStatus\\mapping.xml', ''),
(186, 'partners\\crosslog\\orders\\importChange_1', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\importChange_1\\interface.xml', 'etc\\partners\\crosslog\\orders\\importChange_1\\mapping.xml', ''),
(187, 'partners\\crosslog\\orders\\reload', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\reload\\interface.xml', '', ''),
(188, 'partners\\crosslog\\orders\\reserveWaitingOrder', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\orders\\reserveWaitingOrder\\interface.xml', 'etc\\partners\\crosslog\\orders\\reserveWaitingOrder\\mapping.xml', ''),
(189, 'partners\\crosslog\\products\\exportNew', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\products\\exportNew\\interface.xml', 'etc\\partners\\crosslog\\products\\exportNew\\mapping.xml', ''),
(190, 'partners\\crosslog\\products\\importStock', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\products\\importStock\\interface.xml', 'etc\\partners\\crosslog\\products\\importStock\\mapping.xml', ''),
(191, 'partners\\crosslog\\products\\importStock_1', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\products\\importStock_1\\interface.xml', 'etc\\partners\\crosslog\\products\\importStock_1\\mapping.xml', ''),
(192, 'partners\\crosslog\\products\\init', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\products\\init\\interface.xml', 'etc\\partners\\crosslog\\products\\init\\mapping.xml', ''),
(193, 'partners\\crosslog\\purchase\\exportNew', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\purchase\\exportNew\\interface.xml', 'etc\\partners\\crosslog\\purchase\\exportNew\\mapping.xml', ''),
(194, 'partners\\crosslog\\purchase\\importChange', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\purchase\\importChange\\interface.xml', 'etc\\partners\\crosslog\\purchase\\importChange\\mapping.xml', ''),
(195, 'partners\\crosslog\\purchase\\importStatus', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\purchase\\importStatus\\interface.xml', 'etc\\partners\\crosslog\\purchase\\importStatus\\mapping.xml', ''),
(196, 'partners\\crosslog\\rma\\exportNew', 1, NULL, NULL, NULL, 'etc\\partners\\crosslog\\rma\\exportNew\\interface.xml', 'etc\\partners\\crosslog\\rma\\exportNew\\mapping.xml', ''),
(197, 'partners\\neolane\\01_export_customer_newsletter', 1, NULL, NULL, NULL, 'etc\\partners\\neolane\\01_export_customer_newsletter\\interface.xml', 'etc\\partners\\neolane\\01_export_customer_newsletter\\mapping.xml', ''),
(198, 'partners\\neolane\\02_export_children', 1, NULL, NULL, NULL, 'etc\\partners\\neolane\\02_export_children\\interface.xml', 'etc\\partners\\neolane\\02_export_children\\mapping.xml', ''),
(199, 'partners\\neolane\\07_import_customer_cdm', 1, NULL, NULL, NULL, 'etc\\partners\\neolane\\07_import_customer_cdm\\interface.xml', 'etc\\partners\\neolane\\07_import_customer_cdm\\mapping.xml', ''),
(200, 'partners\\neolane\\08_import_optins', 1, NULL, NULL, NULL, 'etc\\partners\\neolane\\08_import_optins\\interface.xml', 'etc\\partners\\neolane\\08_import_optins\\mapping.xml', ''),
(201, 'partners\\neolane\\11_export_customer_cdm', 1, NULL, NULL, NULL, 'etc\\partners\\neolane\\11_export_customer_cdm\\interface.xml', 'etc\\partners\\neolane\\11_export_customer_cdm\\mapping.xml', ''),
(202, 'partners\\neolane\\import_coupons_used', 1, NULL, NULL, NULL, 'etc\\partners\\neolane\\import_coupons_used\\interface.xml', 'etc\\partners\\neolane\\import_coupons_used\\mapping.xml', ''),
(203, 'partners\\neolane\\import_coupons_valid', 1, NULL, NULL, NULL, 'etc\\partners\\neolane\\import_coupons_valid\\interface.xml', 'etc\\partners\\neolane\\import_coupons_valid\\mapping.xml', ''),
(204, 'partners\\wes\\exp_export_shipping', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\exp_export_shipping\\interface.xml', '', ''),
(205, 'partners\\wes\\fl_incomplete_order', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\fl_incomplete_order\\interface.xml', 'etc\\partners\\wes\\fl_incomplete_order\\mapping.xml', ''),
(206, 'partners\\wes\\ls_export_new_order', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\ls_export_new_order\\interface.xml', '', ''),
(207, 'partners\\wes\\relance_exp', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\relance_exp\\interface.xml', '', ''),
(208, 'partners\\wes\\si_import_stock', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\si_import_stock\\interface.xml', 'etc\\partners\\wes\\si_import_stock\\mapping.xml', ''),
(209, 'partners\\wes\\sl_import_status', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\sl_import_status\\interface.xml', 'etc\\partners\\wes\\sl_import_status\\mapping.xml', ''),
(210, 'partners\\wes\\so_reinit_stock', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\so_reinit_stock\\interface.xml', 'etc\\partners\\wes\\so_reinit_stock\\mapping.xml', ''),
(211, 'partners\\wes\\trk_import_tracking', 1, NULL, NULL, NULL, 'etc\\partners\\wes\\trk_import_tracking\\interface.xml', 'etc\\partners\\wes\\trk_import_tracking\\mapping.xml', '');

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
(34, 6, 2, '2015-04-14 11:12:23'),
(35, 12, 16, '2015-04-15 15:24:35');

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
