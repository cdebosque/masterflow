<?php
/**
 *
 * @package eai-generic
 *
 * @author tbondois
 */
abstract class EaiFormatter extends EaiObject
{
	protected $way;
	/**
	 * @var int
	 */
	protected $elementsFormatted = 0;

	/**
	 * @var array
	 */
	protected $elements = array();

	/**
	 * @var EaiConnector
	 */
	public    $connector;

	/**
	 * Variables de configuration des plugins (qui sont des observers standards)
	 * contient un array (identifié par le 'type') de array (identifiés par le nom de la variable)
	 * Doit etre public pour simplifier la manipulation dans les observers
	 * @var array
	 */
	public $plugins = array();

    /**
     * Indique on est en train de lire le premier "lot" de données
     * @var bool
     */
    public $isFirst = false;

    /**
     * Indique on est en train de lire le dernier "lot" de données
     * @var bool
     */
    public $isLast = false;

  /**
   * @see EaiFormatter->fetchElements()
   *
   * @param array $rawdata
   */
	abstract protected function _getElementFromRaw($rawData);

	/**
	 * @see EaiFormatter->getRawFromElement()
	 *
	 * @param array $element
	 * @param boolean $empty_buffer
	 */
	abstract protected function _getRawFromElement($element, $empty_buffer = false);


	protected function incrementElementsFormated()
	{
		$this->methodStart();

		$this->elementsFormatted++;
		//dump('elementsFormatted'.get_class($this), $this->elementsFormatted);

		$this->methodFinish();
	}

	/**
	 * @uses subclass->_getElementFromRaw()
	 *
	 * @return boolean
	 */
	public function fetchElements()
	{
		$this->methodStart();

		$r = false;
		$this->elements = array();
		if ($this->connector->fetchRawData()) {

			$r = true; //fetchRawData retourne true : ca veut dire qu'on est pas en EOF, et cette fonction renvoie la meme code

			$rawData = $this->connector->getRawData();

			if (!empty($rawData)) {	//il se peut qu'on ai un retour false ou array vide
				$element = $this->_getElementFromRaw($rawData);
				//dump('getElements', $element);

				if (!empty($element)) {
  				$this->elements[] = $element;

  				$this->incrementElementsFormated();

				}
			}

		}
        $this->isFirst = $this->connector->isFirst;
        $this->isLast  = $this->connector->isLast;

		$this->methodFinish();

		if (empty($rawData) && !empty($this->elements)) {
			$this->log("fetchElements: generate empty elements, but there is rawData in source", 'warn');
		}
 		//dump("formatter.fetchElements return", $r, $this->getClass(), '$this->elements:', $this->elements);
		return $r;
	}

	/**
     * For out
	 * @uses subclass->_getRawFromElement()
	 *
	 * @param array $element
	 * @param boolean $empty_buffer
	 * @return array
	 */
	public function getRawFromElements($elements, $empty_buffer= false)
	{
	    $this->methodStart();
	    //Gestion du formattage de plusieurs elements
	    if (isset($elements[0]) and is_array($elements[0])) {
	        $multipleElements= true;
            $this->log('multipleElements='.(int)$multipleElements, 'debug');
	    } else {
	        $multipleElements= false;
	    }
	    if ($multipleElements) {
	    	$this->elements = $elements;//array($elements);//changé parce que sinon internface TV marche pas. Voir les impacts
	    } else {
	    	$this->elements = $elements;
	    }
	    if ($multipleElements) {
    	    $rawData = '';
    	    foreach ($this->elements as $element) {
    		    $rawData.= $this->_getRawFromElement($element, $empty_buffer);
    		    $this->incrementElementsFormated();
                //dump($element, $rawData);
    	    }
	    } else {
	        $rawData = $this->_getRawFromElement($this->elements, $empty_buffer);
	        $this->incrementElementsFormated();
	    }
	    $this->connector->setRawData($rawData);

	    if (!empty($this->elements) && empty($rawData)) {
	    	$this->log("getRawFromElements {$this->way}: generate empty rawData, but there is elements in source", 'warn');
	    } elseif (empty($rawData)) {
	    	$this->log("getRawFromElements() : return rawData empty. EOF ?", 'debug');
	    }
        //dump('Formatter raw out:', $rawData);

        $this->methodFinish();

	    return $rawData;
	}

	/**
	 * @return array
	 */
	public function getElement()
	{
		$this->methodStart();
		$r = $this->getProp('element');
		$this->methodFinish($r);
		return $r;
	}

	// From object to array
	public static function arrayFromObject($data)
	{
		if (is_object($data)) {
		    $data = get_object_vars($data);
		    //TODO: Qu'est qu'un $data vide? array(), '', 0, false ou null
		    if( empty($data) )
		        $data= null;
		}
		return is_array($data) ? array_map(__CLASS__.'::'.__FUNCTION__, $data) : $data;
	}

	// From array to object
	public function objectFromArray($data)
	{
		return is_array($data) ? (object) array_map(__CLASS__.'::'.__FUNCTION__, $data) : $data;
	}

	public function logReport()
	{
	    $this->log("report elements formatted {$this->way} : ".$this->elementsFormatted);
	}

}//class