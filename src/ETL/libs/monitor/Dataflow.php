<?php
/** 
 *
 * @uses ext/Zend classes
 *
 * @package monitor-Dataflow
 *
 * @author abobin
 *
 */

require_once 'LogMapper.php';

//use EaiDbGateway;

class Dataflow
{
	/** @var Zend\Db\Adapter - connexion à la base de données */
	protected $db;

	/** @var Zend\Db\TableGateway - gestionnaire de table */
	protected $table;

	/** @var LogMapper */
	protected $logMapper;


	public function __construct()
	{
		$db = new EaiDbGateway();
		$this->db = $db;

		$table = $db->getTable('dataflow');
		$this->table = $table;

		$this->logMapper = new LogMapper();
	}


	/**
	 * Importe les interfaces en base de données, en parcourrant les fichiers XML.
	 */
	public function import()
	{
		echo '<h2>Importation des interfaces de flux</h2>' . PHP_EOL;

		// @TODO : Doit-on vider la base de données avant ? Que faire des informations stockées en base pour les flux supprimés ?
		// Parcourt les dossiers à la recherche d'interfaces.
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Esb::ETC),
				RecursiveIteratorIterator::CHILD_FIRST);

		$identifiers = array();
		foreach ($iterator as $path) {
			$filename = $path->getFilename();
			$pathname = $path->getPathname();
			if (!$path->isDir() && $filename=='interface.xml') {
				$name = dirname(str_replace(Esb::ETC, '', $pathname));
				$relativePathname = str_replace(Esb::ROOT, '', $pathname);
				$relativePath = dirname($relativePathname);
				$interfaces[$name] = array('name'=>$name, 'relativePathname'=>$relativePathname, 'relativePath'=>$relativePath, 'path'=>$path);
			}
		}
		ksort($interfaces);
		echo '<p>' . count($interfaces) . ' flux trouvés<p>' . PHP_EOL;
		foreach ($interfaces as $interface) {

			echo '<br /><b>'.$interface['name'].'</b>' . PHP_EOL;

			$table = $this->table;
			$rowset = $table->select(array('name'=>$interface['name']));

			// Mapping
			$mapping = $interface['relativePath'] . DIRECTORY_SEPARATOR . 'mapping.xml';
			if (!file_exists(Esb::ROOT . $mapping)) {
				$mapping = '';
			}

			// Observers // @TODO : Il peut y avoir plusieurs observeurs par interface.
			//$observer = $interface['relativePath'] . '/Observer.php';
			//if (!file_exists(Esb::ROOT . $observer)) {
				$observer = '';
			//}

			if ($rowset->count() <= 0) {
				$values = array('name' => $interface['name'], 'interface' => $interface['relativePathname'], 'mapping' => $mapping, 'observer' => $observer);
				$affectedRows = $table->insert($values);
				if ($affectedRows >= 1) {
					echo ' => <span class="lvl1">Flux inséré.</span>' . PHP_EOL;
				}
				else {
					throw new Exception('Insertion dans la base de données impossible.');
				}
			}
			else {
				$values = array('interface' => $interface['relativePathname'], 'mapping' => $mapping, 'observer' => $observer);
				//$values = array('interface' => $interface['relativePathname'], 'mapping' => $mapping);
				$where = array('name' => $interface['name']);
				$affectedRows = $table->update($values, $where);
				if ($affectedRows >= 1) {
					echo ' => <span class="lvl2">Flux mis à jour.</span>' . PHP_EOL;
				}
				// Impossible de lever une exception car si la ligne n'a pas changé, le $affectedRows est à zéro.
				//else {
				//	throw new Exception('Mise à jour dans la base de données impossible.' . mysql_error());
				//}
				else {
					echo ' => <span class="lvl3">Flux identique.</span>' . PHP_EOL;
				}
			}
		} // foreach : identifiers

		echo '<p>Fin de l\'import.</p>' . PHP_EOL;
	}


	/**
	 * Affiche la liste des interfaces.
	 */
	public function showList()
	{
		

		$list = $this->getList();
		foreach ($list as $info) {
			if ($info['enable'] == 1) {
			  $xmlInfo = $this->getXmlInfo($info['name']);
			} else {
			  $xmlInfo = array(
              				'type' => '',
              				'in' => '',
              				'out' => '',
              		    );
			}

			$lastLog = $this->getLastLogs($info['id_dataflow']);
			$status = Dataflow::getStatus($lastLog); // @TODO : Déporter la fonction getStatus() sur l'objet Log.

			$listDatas[$info['id_dataflow']] = array(
				'id'                  => $info['id_dataflow'],
				'name'                => $info['name'],
				'enable'              => $info['enable'],
				//'type'                => $info['type'],
				//'in_connection_type'  => $info['in_connection_type'],
				//'out_connection_type' => $info['out_connection_type'],
				'type'                => $xmlInfo['type'],
				'in_connection_type'  => $xmlInfo['in'],
				'out_connection_type' => $xmlInfo['out'],
				'date_start'          => $lastLog['date_start'],
				'status'              => $status,
				'logfile'             => $lastLog['logfile'],
			);
		}
		
		$sort = 'date_start';
		$order = SORT_DESC;
		$listDatasSorted = array();
		if(!empty($listDatas)){
			$listDatasSorted = Dataflow::array_sort($listDatas, $sort, $order);
			//krsort($listSorted);
	
			
		}

		return $listDatasSorted;
	}


	/**
	 * Affiche le détail d'une interface.
	 *
	 * @param int $id
	 */
	public function showInfo($id)
	{
		$info = $this->getInfo($id);
		echo '<h2>Détail de l\'interface : <em>' . $info['name'] . '</em></h2> ' . PHP_EOL;

		// Info Générales
		echo '<table>' . PHP_EOL;
		foreach($info as $key=>$value) {
			echo ' <tr><td><b>' . $key . '</b></td><td>' . $value . '</td></tr>' . PHP_EOL;
		}
		echo '</table>' . PHP_EOL;

    // Lancement / Upload
    if ($info['enable'] == 1) {
      $xmlInfo = $this->getXmlInfo($info['name']);
      if ($xmlInfo['in'] == 'file' || $xmlInfo['in'] == 'xml') {
        echo '<h2>Upload de fichier pour lancement d\'interface</h2>' . PHP_EOL;
        echo '<form action="?action=launch&amp;id='.urlencode($id).'&amp;file=upload" method="POST" enctype="multipart/form-data">' . PHP_EOL;
        echo ' <input id="source" name="source" type="file" />' . PHP_EOL;
        echo ' <input type="submit" value="Envoyer et lancer l\'interface" />' . PHP_EOL;
        echo '</form>' . PHP_EOL;
      }
      else {
        echo '<h2>Lancement d\'interface</h2>' . PHP_EOL;
        echo '<form action="?action=launch&amp;id='.urlencode($id).'&amp;file=upload" method="POST" enctype="multipart/form-data">' . PHP_EOL;
        echo ' <input type="submit" value="Lancer l\'interface" />' . PHP_EOL;
        echo '</form>' . PHP_EOL;
      }
    }
    
		// Interface
		if (!empty($info['interface']) && file_exists(Esb::ROOT . $info['interface'])) {
			echo '<h3>interface.xml</h3>' . PHP_EOL;
			$content = file_get_contents(Esb::ROOT . $info['interface']);
			Dataflow::displayCode($content, 'xml');
		}

		// Mapping
		if (!empty($info['mapping']) && file_exists(Esb::ROOT . $info['mapping'])) {
			echo '<h3>mapping.xml</h3>' . PHP_EOL;
			$content = file_get_contents(Esb::ROOT . $info['mapping']);
			Dataflow::displayCode($content, 'xml');
		}

		// Observers
		//if (!empty($info['observers'])) {
		//	echo '<h3>Observers</h3>' . PHP_EOL;
		//	$content = file_get_contents(Esb::ROOT . $info['observers']);
		//	$content = file_get_contents(Esb::ROOT . 'etc/ocp/init/product_attributes_labels/import_csvsoap/Observer.php');
		//	Dataflow::displayCode($content, 'php-brief');
		//}
	}


	/**
	 * Affiche l'historique des lancements de l'interface.
	 *
	 * @param int $id
	 */
	public function showHistory($id)
	{
		$info = $this->getInfo($id);
	  $logs = $this->getListLogs($id);
		
		echo '<h2>Historique de l\'interface : <em>' . $info['name'] . '</em></h2> ' . PHP_EOL;
		
		echo '<table>' . PHP_EOL;
		echo ' <tr>' . PHP_EOL;
		echo '  <th rowspan="2">ID</th>' . PHP_EOL;
		echo '  <th rowspan="2">État</th>' . PHP_EOL;
		echo '  <th rowspan="2">Début</th>' . PHP_EOL;
		echo '  <th rowspan="2">Fin</th>' . PHP_EOL;
		echo '  <th rowspan="2">Durée</th>' . PHP_EOL;
		//echo '  <th rowspan="2">^Durée</th>' . PHP_EOL;
		echo '  <th rowspan="2">Fatal</th>' . PHP_EOL;
		echo '  <th rowspan="2">Error</th>' . PHP_EOL;
		echo '  <th rowspan="2">Warn</th>' . PHP_EOL;
		echo '  <th colspan="4">Entrée (IN)</th>' . PHP_EOL;
		echo '  <th colspan="3">Sortie (OUT)</th>' . PHP_EOL;
		//echo '  <th rowspan="2">Vue</th>' . PHP_EOL;
		echo '  <th rowspan="2">Détail</th>' . PHP_EOL;
		echo '</tr>' . PHP_EOL;
		echo '<tr>' . PHP_EOL;
		echo '  <th>Source</th>' . PHP_EOL;
		echo '  <th>Lignes<br>lues</th>' . PHP_EOL;
		echo '  <th>Error</th>' . PHP_EOL;
		echo '  <th>Warn</th>' . PHP_EOL;
		echo '  <th>Lignes<br>écrites</th>' . PHP_EOL;
		echo '  <th>Error</th>' . PHP_EOL;
		echo '  <th>Warn</th>' . PHP_EOL;
		echo '</tr>' . PHP_EOL;

		foreach($logs as $log)	{
			$status = Dataflow::getStatus($log); // @TODO : déporter la fonction getStatus() dans l'objet Log.
			// @TODO : Vérifier proprement que les dates ne soient pas nulles.
			$duration = Dataflow::durationBetweenMysqlDates($log['date_start'], $log['date_finish']);

			echo ' <tr>' . PHP_EOL;
			echo '  <td>' . $log['id_dataflow_log'] . '</td>' . PHP_EOL;
			echo '  <td>' . (!empty($status) ? '<img class="icon" src="images/status/' . $status . '.png" alt="' . $status . '" />' : '') . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::mysql2Datetime($log['date_start']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::mysql2Datetime($log['date_finish']) . '</td>' . PHP_EOL;
			echo '  <td>' . $duration . '</td>' . PHP_EOL;
			//echo '  <td></td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['fatal']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['error']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['warning']) . '</td>' . PHP_EOL;
			echo '  <td>' . basename($log['in_source_file']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['in_raw_datas_fetched']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['in_raw_datas_error']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['in_raw_datas_warning']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['out_raw_datas_writed']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['out_raw_datas_error']) . '</td>' . PHP_EOL;
			echo '  <td>' . Dataflow::prepareIndicator($log['out_raw_datas_warning']) . '</td>' . PHP_EOL;
			//echo '  <td></td>' . PHP_EOL;
			echo '  <td>' . (!empty($log['logfile']) ? '<a href="' . '../' . $log['logfile'].'"><img class="icon" src="images/favicons/launch.png" alt="Afficher le fichier de logs pour ce lancement" /></a>' : '') . '</td>' . PHP_EOL;
			echo ' </tr>' . PHP_EOL;
		}
		echo '</table>' . PHP_EOL;
	}


	/**
	 * Retourne la liste des interfaces.
	 *
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	function getList()
	{
		$table = $this->table;
		$rowset = $table->select();

		return $rowset;
	}


	/**
	 * Retourne les informations de l'interface.
	 *
	 * @param int $id
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	function getInfo($id)
	{
		$table = $this->table;
		$rowset = $table->select(array('id_dataflow' => $id));
		$row = $rowset->current();

		return $row;
	}


	/**
	 * Récupère les paramètres du flux dans le fichier de configuration "interface.xml"
	 *
	 * @param string $name
	 * @return array $datas
	 */
	public function getXmlInfo($name)
	{
	  $configFile= 'interface.xml';
		$fileSpecific = Esb::ETC . $name . DIRECTORY_SEPARATOR . $configFile;

	  if (file_exists($fileSpecific)) {
			$config = new EaiConfiguration($name, $configFile);
 			$datas = array(
 				'type' => $config->getAttribute('type'),
 				'in' => $config->getAttribute('type', 'in/connection'),
 				'out' => $config->getAttribute('type', 'out/connection'),
 			);
	  }
	  else {
	    $datas = array(
	        'type' => '',
	        'in' => '',
	        'out' => '',
	    );
	  }

	  return $datas;
	}


	/**
	 * Retourne le dernier log de l'interface.
	 *
	 * @param int $id_dataflow
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function getLastLogs($id_dataflow)
	{
		$log = $this->logMapper->fetchLast($id_dataflow);
		return $log;
	}


	/**
	 * Retourne une collection de logs de l'interface.
	 *
	 * @param int $id_dataflow
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function getListLogs($id_dataflow)
	{
		$logs = $this->logMapper->fetchAll($id_dataflow);
		return $logs;
	}

	
	/**
	 * Lance une interface avec la config par défaut.
	 *
	 * @param int $id
	 * @param string $fileTmp
	 * @param string $fileName
	 */
	public function launch($id)
	{
	  $info = $this->getInfo($id);
	
	  echo '<pre>';
	  Esb::start($info['name']);
	  echo '</pre>';
	}
	

	/**
	 * Lance une interface avec un fichier fourni.
	 *
	 * @param int $id
	 * @param string $fileTmp
	 * @param string $fileName
	 */
	public function launchWithFile($id, $fileTmp, $fileName)
	{
		$info = $this->getInfo($id);
		//$dir = Esb::ROOT . 'var' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $info['name'] . DIRECTORY_SEPARATOR;
		$dir = Esb::WORKBASE . $info['name'] . DIRECTORY_SEPARATOR;
		//$file = $dir . $fileName;

		if (!file_exists($dir) && !mkdir($dir, 0777, true)) {
			throw new Exception('Création du répertoire "' . $dir . '" impossible.');
		}

		// @TODO : Vérifier l'existance d'un fichier de même nom pour l'archiver avant écrasement.

		if (file_exists($dir . $fileName)) {
			$i = 1;
		  do {
		    $dotPos = strrpos($fileName, '.');
		    $fileNew = substr($fileName, 0, $dotPos) . '.' . ++$i . substr($fileName, $dotPos);
		  }
		  while (file_exists($dir . $fileNew));
		  $fileName = $fileNew;
		}
		
		if (!move_uploaded_file($fileTmp, $dir . $fileName)) {
			throw new Exception('Déplacement du fichier "' . $fileTmp . '" vers "' . $dir . $fileName . '" impossible.');
		}

		$config = '
		<interface>
			<in>
				<connection>
					<file>' . $fileName . '</file>
				</connection>
			</in>
		</interface>';

		echo '<pre>';
		Esb::start($info['name'], $config);
		echo '</pre>';
	}
	
	
	/**
	 * Retourne le status en fonction des types de logs apparus.
	 *
	 * @param array $lastLog
	 * @return string
	 */
	public static function getStatus($lastLog) // @TODO : Déplacer cette fonction dans l'objet Log.
	{
		if (!$lastLog) {
			$status = '';
		}
		elseif(empty($lastLog['date_finish']) || $lastLog['date_finish'] == '0000-00-00 00:00:00') {
		  $status = 'processing';
		}
		elseif ($lastLog['fatal'] > 0) {
			$status = 'fatal';
		}
		elseif ($lastLog['error'] > 0) {
			$status = 'error';
		}
		elseif ($lastLog['warning'] > 0) {
			$status = 'warning';
		}
		else {
			$status = 'info';
		}

				return $status;
	}

	/**
	 * Transforme une datetime MySQL en datetime affichable.
	 *
	 * @param string $date
	 * @return string
	 */
	public static function mysql2Datetime($mysqlDate)
	{
		if (empty($mysqlDate) || $mysqlDate == '0000-00-00 00:00:00') {
			$datetime = '';
		}
		else {
			$date = new DateTime($mysqlDate, new DateTimeZone('UTC'));
			$date->setTimezone(new DateTimeZone(Esb::getTimezone()));
			$datetime = $date->format('d/m/Y H:i:s');
		}

		return $datetime;
	}

	/**
	 * Retourne le nombre de secondes entre deux dates.
	 *
	 * @param string $date1
	 * @param string $date2
	 * @return int
	 */
	public static function durationBetweenMysqlDates($date1, $date2)
	{
		$duration = '';
		if ($date1 != '0000-00-00 00:00:00' && $date2 != '0000-00-00 00:00:00')
		{
			$dateStart = new DateTime($date1, new DateTimeZone('UTC'));
			$dateFinish = new DateTime($date2, new DateTimeZone('UTC'));
			$secBetween = $dateFinish->format('U') - $dateStart->format('U');

			if ($secBetween > 0)
			{
				$hours = floor($secBetween / 3600);
				$mod = $secBetween % 3600;
				$minutes = floor($mod / 60);
				$seconds = $mod % 60;

				$duration = (!empty($hours) ? $hours.'h' : '') . (!empty($minutes) ? $minutes.'m' : '') . (!empty($seconds) ? $seconds.'s' : '');
			}
			else {
			  $duration = '0s';
			}
		}

		return $duration;
	}

	/**
	 * Prépare l'affichage en supprimant les valeurs égalent à zéro.
	 *
	 * @param int $indicator
	 * @return string
	 */
	public static function prepareIndicator($indicator)
	{
		if ($indicator == 0) {
			$indicator = '';
		}
		return $indicator;
	}

	/**
	 * Affiche le contenu avec indentation et coloration syntaxique.
	 */
	public static function displayCode($content, $language)
	{
		// Utilisation de la librairie GeSHi
		$geshi = new GeSHi($content, $language);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS); // GESHI_FANCY_LINE_NUMBERS | GESHI_NORMAL_LINE_NUMBERS | GESHI_NO_LINE_NUMBERS
		echo '<div class="code">' . PHP_EOL;
		echo $geshi->parse_code();
		echo '</div>' . PHP_EOL;
	}


	public static function array_sort($array, $field, $order=SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						if ($key2 == $field) {
							$sortable_array[$key] = $value2;
						}
					}
				} else {
					$sortable_array[$key] = $value;
				}
			}

			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
					break;
				case SORT_DESC:
					arsort($sortable_array);
					break;
			}

			foreach ($sortable_array as $key => $value) {
				$new_array[$key] = $array[$key];
			}
		}

		return $new_array;

		// Exemple d'utilisation :
		//
		//$people = array(
		//		12345 => array(
		//				'id' => 12345,
		//				'first_name' => 'Joe',
		//				'surname' => 'Bloggs',
		//				'age' => 23,
		//				'sex' => 'm'
		//		),
		//		12346 => array(
		//				'id' => 12346,
		//				'first_name' => 'Adam',
		//				'surname' => 'Smith',
		//				'age' => 18,
		//				'sex' => 'm'
		//		),
		//		12347 => array(
		//				'id' => 12347,
		//				'first_name' => 'Amy',
		//				'surname' => 'Jones',
		//				'age' => 21,
		//				'sex' => 'f'
		//		)
		//);
		//
		//print_r(array_sort($people, 'age', SORT_DESC));
		//print_r(array_sort($people, 'surname', SORT_ASC));
	}
}