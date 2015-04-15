<?php
/**
 * 
 * Canonical Data Model / pivot format
 * 
 * @deprecated
 * @see EaiCdm
 * 
 * @author tbondois
 */
class EaiModel extends EaiObject
{
	/**
	 * Données de configuration du fichier etc/core/base/<xxx>/cdm.xml
	 * @var EaiConfiguration
	 */

	protected $identifier;

	protected $name;

	protected $validations = array();

	protected $config_key = 'model';

	protected $config_base_path = 'core/cdm/';
	
	/**
	 *
	 * @var Eaiconfiguration
	 */
	protected $config;

	public function __construct($identifier)
	{
		parent::__construct();
		$this->methodStart();

		$this->identifier = strtolower($identifier);	//le chemin du fichier model.xml

		$this->name = $this->config()->getAttribute('name');	//le name défini dans le fichier model.xml. Normalement $name == $identifier

		$this->methodFinish();

	}

	public function format($eaiData)
	{
		$this->methodStart();
		$r = array();

		foreach($eaiData as $field => $value) {
			if ($this->validate($field, $value))	{
				$r[$field] = $this->getValue($field, $value);
			}
		}

		$this->methodFinish();

		return $r;
	}

	public function getValue($field, $value)
	{
		$this->methodStart();
		$r = $value;

		if($this->isRequired($field)){
			if(!is_null($value) && $value !== '' && !is_null($this->config()->getValue("$field/default"))){
				$r = $this->config()->getValue("$field/default");
			}
		}

		$this->methodFinish();
		return $r;
	}

	public function validate($field, $value)
	{
		$this->methodStart();

		$validation = array('defined'  => false
		                  ,	'required' => false
		                  , 'type'     => false
		                  , 'global'   => false
		  );
		if(!empty($this->name)) {
			if (!is_null($this->config()->getArray($field))) {	//test si ce noeud est defini dans le fichier cdm
				$validation['defined'] = true;

				$validation['required'] = $this->validateRequired($field, $value);
				if ($validation['required']) {

					$validation['type']    = $this->validateType($field, $value);
					if ($validation['type']) {

						$validation['global'] = true;

					}
				}
			}
		}
		$this->validations[$field] = $validation;

		$this->methodFinish();
		return $validation['global'];
	}

	public function isRequired($field)
	{
		$this->methodStart();
		$r = (bool) $this->config()->getAttribute('required', $field);
		$this->methodFinish();
		return $r;
	}

	protected function validateRequired($field, $value)
	{
		$this->methodStart();
		$r = false;

		$required = $this->isRequired($field);
		if(!empty($required)) {
			if(!is_null($value) && $value !== ''){
				$r = true;
			} elseif(!is_null($this->config()->getValue("$field/default"))){
				$r = true;
			}
		} else {
			$r = true;//pas required
		}

		$this->methodFinish();
		return $r;
	}

	protected function validateType($field, $value)
	{
		$this->methodStart();
		$r = false;

		$type = $this->config()->getAttribute('type', $field);
		switch ($type) {
			case 'int':
				if(is_numeric($value)) $r = true;
				break;
			case 'uint'://unsigned int
				if(is_numeric($value) && (int) $value >= 0) $r = true;
				break;
			case 'timestamp':
			case 'nint'://natural int
				if(is_numeric($value) && (int) $value > 0) $r = true;
				break;
			case 'float':
				$r = true;//TODO
				break;
			case 'email':
				$r = true;
				break;
			case 'datetime':
				$r = true;//TODO
				break;
			case 'enum':
				$r = true;//TODO
				break;
			case 'string':
			default:
				$r = true;
		}
		//dump("model: $type", $field, $value, $r);
		$this->methodFinish();
		return $r;
	}

// 	/**
// 	 *
// 	 * @return EaiConfiguration
// 	 */
// 	public function config()
// 	{
// 		$this->methodStart();

// 		if (is_null($this->config)) {
// 			$this->config = new EaiConfiguration("core/models/{$this->identifier}", 'model');
// 		}
// 		$this->methodFinish();
// 		return $this->config;
// 	}
}//class
?>