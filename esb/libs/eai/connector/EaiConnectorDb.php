<?php
/**
 * Classe de connexion a une base de données
 *
 *
 * @author tbondois
 *
 */

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class EaiConnectorDb extends EaiConnector
{

	public $db;
	public $driver;
	public $database;
	public $username;
	public $password;
	public $hostname;
	public $tablename;
	public $where;
	public $order;
	public $table;
	public $port;
	public $adapter;
	public $id;
	protected $content;

	/**
	 * @see EaiConnector::connect()
	 * @return boolean
	 */
	public function _eaiConnect()
	{
		
		$this->setClassContext('driver' , $this->driver);
		$this->setClassContext('database', $this->database);
		$this->setClassContext('username' , $this->username);
		$this->setClassContext('password' , $this->password);
		$this->setClassContext('hostname' , $this->hostname);
		$this->setClassContext('port' , $this->port);
		$this->setClassContext('tablename' , $this->tablename);
		$this->setClassContext('where' , $this->where);
		$this->setClassContext('order' , $this->order);
		$this->setClassContext('id' , $this->id);
		
		$config = array(
				'driver' => $this->driver,
				'database' => $this->database,
				'username' => $this->username,
				'password' => $this->password,
				'hostname' => $this->hostname,
				'port' => $this->port,
								
		);
		try {
			//J'ouvre ma connexion vers la base
 			$this->db = new Zend\Db\Adapter\Adapter($config);
			
			
			//Si le driver d'entrée est une table, on créé la requête
			if ($this->way == Esb::WAY_IN) {
				$sql = "SELECT * FROM ".$this->tablename;
				if(!empty($this->where))$sql .= " " . $this->where;
				if(!empty($this->order))$sql .= " " . $this->order;
				
				$this->table = $this->db->query($sql);
				$this->content = $this->table->execute();
				
			}
			
		} catch (Zend_Db_Adapter_Exception $e) {
			// probablement mauvais identifiants,
			echo $e->getMessage();
		} catch (Zend_Exception $e) {
			// probablement que factory() n'a pas réussi à charger
			// la classe de l'adaptateur demandé
			echo $e->getMessage();
		}
		
		
		return true;
	}


	/**
	 * @see EaiConnector::disconnect()
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		$r = true;

		return $r;
	}

	/**
	 * @see EaiConnector::fetchRawData()
	 * @return mixed
	 */
	public function _eaiFetchRawData()
	{
		//SI on n'est pas en fin de fichier
		if($this->content->valid()) {
			$rawData = $this->content->current();
			//On passe à l'enregistrement suivant
			$this->content->next();
			return $rawData;
		}
		else {
// 			echo "<h2>EOF</h2>";
			$this->setEOF();
			return false;
		}			
	}


	/**
	 * @see EaiConnector::send()
	 * @return bool
	 */
	public function _eaiWriteRawData()
	{
		$written = false;

		
		try {
// 			echo "<h2>Debut insert</h2>";
			$this->methodStart();

			$rawdata = $this->getRawData();
			var_dump($rawdata);
			$db = $this->db;
			$qi = function($name) use ($db) { return $db->platform->quoteIdentifier($name); };
			$fp = function($name) use ($db) { return $db->driver->formatParameterName($name); };
			
			$sql = 'REPLACE INTO ' . $qi($this->tablename);
			$Set ="";
			
			//On récupère les champs
			foreach($rawdata[0] as $table=>$datas)
			{
				$parameters = $datas;
				$key="";
				//On récupère la clef
				if(!empty($this->id) && !empty($datas[$this->id]) )
				{
					$key = $datas[$this->id];
				}
				foreach($datas as $col=>$data)
				{
					var_dump($col);
					var_dump($data);
					if(!empty($Set)) $Set .= ' ,';
					$Set .= $qi($col) . ' = ' . $fp($data);
				}
				if(!empty($Set)) $sql .= ' SET ' . $Set;


			}
			//$written = (bool)fwrite($this->pointer, $rawdata);
			$statement = $this->db->query($sql);
			
			$statement->execute($parameters);
			$this->methodFinish($written);
// 			echo "<h2>Fin insert</h2>";
		} catch (Exception $e) {
			echo $e->getMessage();
		}

		return $written;
	}




}//class