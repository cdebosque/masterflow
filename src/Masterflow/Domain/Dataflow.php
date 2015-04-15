<?php

namespace Masterflow\Masterflow\Domain;

class Dataflow
{
	/**
	 * Dataflow id_dataflow.
	 *
	 * @var integer
	 */
	private $id;

	/**
	 * Dataflow name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Dataflow enable.
	 *
	 * @var boolean
	 */
	private $enable;

	/**
	 * Dataflow type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Dataflow in_connection_type.
	 *
	 * @var string
	 */
	private $in_connection_type;

	/**
	 * Dataflow out_connection_type.
	 *
	 * @var string
	 */
	private $out_connection_type;

	/**
	 * Dataflow interface.
	 *
	 * @var string
	 */
	private $interface;	

	/**
	 * Dataflow mapping.
	 *
	 * @var string
	 */
	private $mapping;

	/**
	 * Dataflow observer.
	 *
	 * @var string
	 */
	private $observer;	


	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function isEnable() {
		return $this->enable;
	}

	public function setEnable($enable) {
		$this->enable = (bool)$enable;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getInConnectionType() {
		return $this->in_connection_type;
	}

	public function setInConnectionType($in_connection_type) {
		$this->in_connection_type = $in_connection_type;
	}

	public function getOutConnectionType() {
		return $this->out_connection_type;
	}

	public function setOutConnectionType($out_connection_type) {
		$this->out_connection_type = $out_connection_type;
	}

	public function getInterface() {
		return $this->interface;
	}

	public function setInterface($interface) {
		$this->interface = $interface;
	}

	public function getMapping() {
		return $this->mapping;
	}

	public function setMapping($mapping) {
		$this->mapping = $mapping;
	}

	public function getObserver() {
		return $this->observer;
	}

	public function setObserver($observer) {
		$this->observer = $observer;
	}
}