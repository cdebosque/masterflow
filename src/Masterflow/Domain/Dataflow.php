<?php

namespace Masterflow\Masterflow\Domain;

class Dataflow
{
	/**
	 * Dataflow id_dataflow.
	 *
	 * @var integer
	 */
	private $id_dataflow;

	/**
	 * Dataflow name.
	 *
	 * @var string
	 */
	private $name;


	public function getId() {
		return $this->id_dataflow;
	}

	public function setId($id) {
		$this->id_dataflow = $id;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}
}