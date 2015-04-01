<?php
/**
 * @TODO : Vérifier et/ou positionner (via ini_set()) les configurations PHP pour l'upload de fichier et le délai d'exécution du script.
 * @TODO : Revoir le modèle MVC... 
 * @TODO : Réorganiser la structure web. Exemple : stocker la partie web dans un dossier www.
 */
header("Content-Type: text/html;charset=UTF-8");

require_once __DIR__ . '/../esb.php';

$esb_version = '1.0.0';

// TimeZone utilisée pour l'affichage des dates.
Esb::setTimezone('Europe/Paris');

require_once Esb::LIBS . 'monitor' . DIRECTORY_SEPARATOR . 'Dataflow.php';
//use libs\monitor\Dataflow;

// Coloration syntaxique
require_once Esb::LIBS . 'ext' . DIRECTORY_SEPARATOR . 'geshi' . DIRECTORY_SEPARATOR . 'geshi.php';

// FlowManager
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$id = (int) isset($_GET['id']) ? $_GET['id'] : '';
$file = isset($_FILES['source']) ? $_FILES['source'] : '';

// @TODO : Vérifier les paramètres passés à la page.

require 'template_top.php';

try {
	
	$dataflow = new Dataflow();
	
	switch($action)
	{
		case 'info':
			$dataflow->showInfo($id);
			break;
	
		case 'history':
			$dataflow->showHistory($id);
			break;
	
		case 'launch':
			if ($file && $file['error'] == 0) {
				$dataflow->launchWithFile($id, $file['tmp_name'], $file['name']);
			}
			else { 
				// http://php.net/manual/fr/features.file-upload.errors.php
				throw new Exception('Chargement du fichier incorrect. ' . (isset($file['error']) ? 'Code erreur : ' . $file['error'] : ''));
			}
			break;
		
		case 'import':
			$dataflow->import();
			break;

		case 'list':
			
			require 'pages/dataflow_list.php';
			break;
		case 'dashboard':
			require 'pages/dashboard.php';
			break;
		
		default:
			if(file_exists(DIR_ROOT . SP_FOLDER . "/pages.php")) require DIR_ROOT . SP_FOLDER . "/pages.php";
			break;

	}
}
catch (Exception $e) {
	echo '<strong>Erreur</strong> : ', $e->getMessage();
}

require 'template_bottom.php';
