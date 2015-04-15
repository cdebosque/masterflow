<?php
/**
 * Ce fichier de configuration définit toutes les variable spécifiques à l'environnement
 * Normalement seul ce fichier doit changer lors du deploiement du projet sur d'autres serveurs
 * Il ne doit donc pas être versionné.
 */

/**
 * Configuration du PHP
 */
set_time_limit(20 * 60 * 60);
error_reporting(E_ALL);
//ini_set("memory_limit","2048M");
/**
 * Initialisation de l'environnement magento
 */
ini_set('display_errors', 1);

/**
 * env indiquera ou chercher le fichier de configurations spécifiques à l'environnement
 * dans etc/core/base/env/*.xml
 * @var string
 */
define('ENV', 'dev-win');
/**
 * Pour es interfaces qui ne contiennent pas de logging.php :
 * on ira chercher le fichier etc/core/base/logging/*.php
 * Cette valeur doit donc correspondre a un fichier existant.
 * @var string
 */
define('LOGGING_BASE', ENV);

/**
 * Permet de définir le séparateur de répertoire en fonction du système
 * @var int
 */
define('DIR_SEP', "\\");

/**
 * Contrôle si la  fonction kill éxécute un exit apres l'affichage
 * @var int
 */
define('KILL_ALLOWED', 1);


/**
 * Definition des chemins du projet Windows
 */
define('DIR_ROOT', __DIR__ .DIR_SEP);
define('DIR_ETC' , DIR_ROOT.'etc'.DIR_SEP);
define('DIR_BASE', DIR_ETC.'core'.DIR_SEP.'base'.DIR_SEP);
define('DIR_CDM' , DIR_ETC.'core'.DIR_SEP.'cdm'.DIR_SEP);
define('DIR_LIBS', DIR_ROOT.'libs'.DIR_SEP.'');
define('DIR_WORKBASE', DIR_ROOT.'var'.DIR_SEP);
/**
 * Uncomment next line to log (INFO level) all called events
 * (even if they aren't triggered by any observers)
 * if you are not in a HTML-based context
 */
//if(!is_array($_GET)) $_GET = array(); if(!isset($_GET['eventHelper'])) $_GET['eventHelper'] = 1;

/**
 * Paramètre de connexion à la base de données.
 */
define('DB_BASE', 'esb');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');

/**
 * Paramètre des outils métiers 
 */
define('SP_FOLDER', 'ecommerce');
