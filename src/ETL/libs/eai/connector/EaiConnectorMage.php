<?php
/**
 *
 * @author tbondois
 *
 */
class EaiConnectorMage extends EaiConnector
{
	protected $path;

	protected $apiclass;

	protected $obj;

	/**
	 * Méthode d'appel du client Soap
	 * @var string
	 */
	protected $apimethod;

	/**
	 * Tableau d'arguments passé à l'appel du client Soap
	 * @var array
	 */
	protected $methodparams= array();

	/**
	 * Utilisation d'un fichier temporaire en entrée
	 * dans lequel la donnée est stockée sérialisée
	 * @var string
	 */
	protected $useTempFile = false;

	/**
	 * @return boolean
	 */
	public function _eaiConnect()
	{
		$r = false;

		$path = $this->getPath();
		if ($path) {
			$mageFileName = Esb::fullPath($path).'app/Mage.php';

			require_once $mageFileName;

			Mage::app();

			$class = $this->getApiclass();

			if (!empty($class) and class_exists($class)) {
			    $reflect = new ReflectionClass($class);
			    if ($reflect->isInstantiable()) {
			        $this->obj = $reflect->newInstance();

			    } else {
			        $this->fault("not instanciable $class");
			    }
			} else {
			    $this->fault("class not exists $class");
			}

			if (empty($this->apimethod) or !$reflect->hasMethod($this->apimethod) ) {
			    $this->fault("apimethod '".$this->apimethod."' not defined or not a method of $class");
			}

			if (!empty($this->methodparams) and is_array($this->methodparams)) {
			    array_walk_recursive($this->methodparams, 'static::parseBrackets', 0x07);
			}/* else {
			    $this->fault("methodparams not defined");
			}*/

			$r = true;
		} else {
		    $this->fault("path not found");
		}
		return $r;
	}

	/**
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		$r = true;

		$this->obj = null;

		return $r;
	}


	public function _eaiFetchRawData()
	{
	    if (empty($this->results)) {
	      $this->setMethodContext('apimethod', $this->apimethod);
	      $this->setMethodContext('methodparams', $this->methodparams);

				if ($this->getUseTempFile() and file_exists($this->getUseTempFile())) {
				  $this->results = unserialize(file_get_contents($this->getUseTempFile()));
				} else {
	    	  $this->results = call_user_func_array(array( $this->obj, $this->apimethod), $this->methodparams);

				  if ($this->getUseTempFile()) {
      			    file_put_contents($this->getUseTempFile(),  serialize($this->results));
    			}
    	  }

	    }



        if (isset($this->results[$this->rawDatasFetched])) {
            $rawdata = $this->results[$this->rawDatasFetched];
	    } else {
	        $rawdata = false;
	    }

	    if (!isset($this->results[$this->rawDatasFetched+1])) {
	        $this->setEOF();
	    }

      if ($this->debug) {
        dump($rawdata);
      }

	    return $rawdata;
	}


	public function _eaiWriteRawData()
	{
	    $result = false;

        $this->setMethodContext('apimethod'   , $this->apimethod);

        $this->setMethodContext('methodparams', $this->methodparams);

	    $result = call_user_func_array(array( $this->obj, $this->apimethod), array_merge($this->methodparams, $this->rawData));

	    //On se base sur l'exeption pour le retour
	    return true;
	}


}//class
