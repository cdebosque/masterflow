<?php

//use Zend\Db\Sql\Select;

class LogMapper
{
	/** @var Zend\Db\Adapter - connexion à la base de données */
	protected $db;
	
	/** @var Zend\Db\TableGateway - gestionnaire de table */
	protected $table;
	
	
	public function __construct()
	{
		$db = new EaiDbGateway();
		$this->db = $db;

		$table = $db->getTable('dataflow_log');
		$this->table = $table;
	}
	
	
	/**
	 * Retourne la liste des logs de cette interface.
	 * 
	 * @param int $idDataflow
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function fetchAll($idDataflow)
	{
		$select = $this->table->getSql()->select();
		$select->where(array('id_dataflow' => $idDataflow))->order('id_dataflow_log DESC');
		$rowset = $this->table->selectWith($select);
		//$result = $resultSet->toArray();
		return $rowset;
	}
	
	
	/**
	 * Retourne le dernier log de cette interface.
	 * 
	 * @param int $idDataflow
	 * @return array
	 */
	public function fetchLast($idDataflow)
	{
		$select = $this->table->getSql()->select();
		$select->where(array('id_dataflow' => $idDataflow))->order('id_dataflow_log DESC')->limit(1);
		$rowset = $this->table->selectWith($select);
		$row = $rowset->current();
		return $row;
	}
	
}