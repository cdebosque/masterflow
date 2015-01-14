<?php
/**
 * 
 * @uses ext/Zend classes
 *
 * @package eai-generic
 * 
 * @author abobin
 *
 */

use Zend\Db\Sql\Expression;
//use EaiDbGateway;

class EaiDbRow extends EaiObject
{
	/** @var EaiDbGateway */
	protected $db;
	
	/** @var Zend\Db\TableGateway\TableGateway */
	protected $table;
	
	/** @var int */
	protected $id;
	
	/** @var int */
	protected $parentId;
	
	/** @var string */
	protected $identifier;


	/**
	 * Initialise la ligne de log à mettre à jour.
	 */
	public function __construct($identifier)
	{
		$this->identifier = $identifier;
		
		$db = new EaiDbGateway();
		$table = $db->getTable('dataflow_log');
		
		$this->db = $db;
		$this->table = $table;

		// Crée l'enregistrement dans la base de données et initialise l'id.
		$rowset = $this->db->getTable('dataflow')->select(array('name'=>$this->identifier));
		if ($rowset->count() <= 0) {
			throw new Exception('Le nom du flux "'. $this->identifier .'" est incorrect.');
		}
		$dataflow = $rowset->current();
		
		$this->table->insert(array('id_dataflow'=>$dataflow->id_dataflow));
		$this->id = $this->table->getLastInsertValue();
		$this->parentId = $dataflow->id_dataflow;
	}

	
	/**
	 * Met à jour la date de départ.
	 */
	public function updateStart()
	{
		$now = Esb::date('Y-m-d H:i:s');
		$this->table->update(array('date_start'=>$now), array('id_dataflow_log'=>$this->id));
	}

	
	/**
	 * Met à jour la date de fin.
	 */
	public function updateFinish()
	{
		$now = Esb::date('Y-m-d H:i:s');
		$this->table->update(array('date_finish'=>$now), array('id_dataflow_log'=>$this->id));
	}
	
	
	/**
	 * Incrémente un champ numérique (compteur) de la base de données. 
	 */
	public function increment($field, $step=1)
	{
		$this->table->update(array($field => new Expression($field . ' + ' . $step)), array('id_dataflow_log'=>$this->id));
	}

	
	/**
	 * Met à jour le nom du fichier de log.
	 */
	public function updateLogFilename($filename)
	{
		$this->table->update(array('logfile'=>$filename), array('id_dataflow_log'=>$this->id));
		// @TODO : Déplacer la fonction updateLogFilename qui n'a rien à faire dans EaiDbRow.
	}


  public function updateInSourceFile($filename)
  {
    $this->table->update(array('in_source_file'=>$filename), array('id_dataflow_log'=>$this->id));
  }
}
