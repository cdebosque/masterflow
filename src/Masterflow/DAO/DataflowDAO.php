<?php

namespace Masterflow\Masterflow\DAO;

use Doctrine\DBAL\Connection;
use Masterflow\Masterflow\Domain\Dataflow;

class DataflowDAO extends DAO
{
	/**
	 * Return a list of all articles, sorted by date (most recent first).
	 *
	 * @return array A list of all articles.
	 */
	public function findAll() {
		$sql = "select * from dataflow order by id_dataflow desc";
		$result = $this->getDb()->fetchAll($sql);

		// Convert query result to an array of domain objects
		$dataflow = array();
		foreach ($result as $row) {
			$dataflowId = $row['id_dataflow'];
			$dataflow[$dataflowId] = $this->buildDomainObject($row);
		}
		return $dataflow;
	}
	
	/**
	 * Creates an Article object based on a DB row.
	 *
	 * @param array $row The DB row containing Article data.
	 * @return \MicroCMS\Domain\Article
	 */
	protected function buildDomainObject($row) {
		$dataflow = new Dataflow();
		$dataflow->setId($row['id_dataflow']);
		$dataflow->setName($row['name']);
		$dataflow->setEnable($row['enable']);
		$dataflow->setType($row['type']);
		$dataflow->setInConnectionType($row['in_connection_type']);
		$dataflow->setOutConnectionType($row['out_connection_type']);
		$dataflow->setInterface($row['interface']);
		$dataflow->setMapping($row['mapping']);
		$dataflow->setObserver($row['observer']);
		return $dataflow;
	}
	
	/**
	 * Returns an dataflow matching the supplied id.
	 *
	 * @param integer $id
	 *
	 * @return \MicroCMS\Domain\Dataflow|throws an exception if no matching dataflow is found
	 */
	public function find($id) {
		$sql = "select * from dataflow where id_dataflow=?";
		$row = $this->getDb()->fetchAssoc($sql, array($id));
	
		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new \Exception("No dataflow matching id " . $id);
	}
}