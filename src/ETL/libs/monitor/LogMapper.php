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
	
	/**
	 * Retourne le dernier log de cette interface.
	 *
	 * @param string $states
	 * @return int
	 */
	public function countGlobal($states)
	{
		//var_dump($states);
		$select = $this->table->getSql()->select();
		$where = "";
		if(!empty($states)) 
			foreach($states as $state => $value)
				$where .= (empty($where))? $value . ">0" : " AND " .$value . ">0" ;
		else 
			$where = "warning = 0 AND error = 0 AND fatal = 0";
		//var_dump($where);
		$select->where($where)->order('id_dataflow_log DESC');
			
		$rowset = $this->table->selectWith($select)->count();
		//$result = $resultSet->toArray();
		return $rowset;
	}
}