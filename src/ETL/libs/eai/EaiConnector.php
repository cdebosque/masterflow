<?php
/**
 *
 * @package eai-generic
 *
 * @author tbondois
 */
abstract class EaiConnector extends EaiObject
{
	const STOP_ON_WRITE_ERR    = 0x01;

	const STOP_ON_READ_ERR     = 0x02;

	protected $stopOnError=false;

	protected $way;
	/**
	 * Comportement du connecteur
	 *     0x01: Arrêt sur erreur de lecture
	 *     0x02: Arrêt sur erreur d'écriture
	 */
	protected $behavior        = 0x00;

	/**
	 * Fin de la lecture
	 * @var boolean
	 */
	protected $EOF             = false;

	/**
	 * Nombre de lignes lues
	 * @var int
	 */
	protected $rawDatasFetched = 0;

	/**
	 * Nombre de lignes ecrites
	 * @var int
	 */
	protected $rawDatasWrited  = 0;

	/**
	 * Nombre de lignes en erreur
	 * @var int
	 */
	protected $rawDatasError   = 0;


	/**
	 * Nombre de lignes en alerte
	 * @var int
	 */
	protected $rawDatasWarn   = 0;

	/**
	 * Donnée brute
	 * @var mixed
	 */
	protected $rawData         = false;

	/**
	 * lignes de données brutes
	 * @var array
	 */
	protected $rawDatas        = array();


	protected $writeEmptyRaw   = 0;


	protected $debug   = 0;

	/**
	 * Variables de configuration des plugins (qui sont des observers standards)
	 * contient un array (identifié par le 'type') de array (identifiés par le nom de la variable)
	 * Portée "public" pour simplifier la manipulation dans les observers
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


  protected function _eaiInit() {

  }

	/**
	 * Ouverture du connector, à surcharger dans les classes filles
	 *
	 * @return bool
	 */
	abstract protected function _eaiConnect();

	/**
	 * Fermeture du connector
	 *
	 * @return bool
	 */
	abstract protected function _eaiDisconnect($error=false);

	/**
	 * Lecture et retourne de la donnée brute
	 *
	 * @return $rawData
	 */
	abstract function _eaiFetchRawData();

	/**
	 * Ecriture de la donnée brute $this->rawData
	 *
	 * @param mixed $rawData
	 */
	abstract function _eaiWriteRawData();

	/**
	 * @uses subclass->_eaiConnect()
	 *
	 * @return boolean
	 */
	public function connect()
	{

    $r = $this->_eaiInit();

		$this->methodStart();

		$msgErr = '';
		try {
			$r = $this->_eaiConnect();
		} catch (Exception $e) {
			$r = false;
			$msgErr = $e->getMessage();
		}

		if (!$r || $msgErr) {
			$this->fault("Exception connecting $msgErr");
		}

		$this->methodFinish($r);
		return $r;
	}

	/**
	 * @uses subclass->_eaiFetchRawData()
	 *
	 * @return mixed;
	 */
	public function fetchRawData()
	{
		$this->methodStart();
		$this->rawData = false;
		$msgErr = '';

		$r = $this->isFirst = false;

        if ($this->rawDatasFetched == 0) {
            $this->isFirst = true;
        }

		if (!$this->isEOF()) {
			try {
			    $this->rawData = $this->_eaiFetchRawData();
			    if ($this->isEOF()) {
			      $this->isLast = true;
			    }
			    //dump('connector.fetchRawData', $this->rawData);
			    $this->incrementRawDatasFetched();
			    $r = true;
			} catch (Exception $e) {
			    $this->incrementRawDatasError();
			    $msgErr= $e->getMessage();
			}
		}
// 		else {
//             $this->isLast = true;
//         }

		if ($msgErr) {
			$msgErr = "Exception fetching: $msgErr";
			if ($this->behavior & self::STOP_ON_READ_ERR ) {
				$this->fault($msgErr);
			} else {
				$this->log($msgErr, 'err');
			}
		}

		$this->methodFinish();
		return $r;
	}

	/**
	 * @uses subclass->_eaiWriteRawDatas()
	 *
	 * @param string $mode 'line'|'block'
	 * @return boolean
	 */
	public function writeRawDatas($mode = 'line')
	{
		$this->methodStart($mode);
		$r = true;

		if (!empty($this->rawDatas)) {


			if ($mode == 'block') {
                //Mode Block :
				try {
				    $msgErr = '';
					$this->dispatchEvent("beforeSend");
				    $r = $this->_eaiWriteRawDatas();

				    //Traitement du message de retour dans le cas d'un retour vide
				    if ( empty($r) ) {
				    	$NbRawDatas = count($this->rawDatas);
				        $this->incrementRawDatasWarn($NbRawDatas);
				        $this->log("data n°".$this->rawDatasWrited ." mode block return empty. (data agregate?)", 'warn');
				    } else {
				    	$NbRawDatas = count($this->rawDatas);
				        $this->incrementRawDatasWrited($NbRawDatas);
				    }

				    $this->dispatchEvent("afterSend");
				} catch (Mage_Api_Exception $e) {
    				$r = false;
				    //TODO: Impossible de savoir le nombre d'éléments en erreurs
				    //$this->incrementRawDatasError($rawNbrElements);
    				$msgErr = "Exception Mage Api $mode writing with message: ".$e->getCustomMessage();
				} catch (Exception $e) {
				    $r = false;
				    //TODO: Impossible de savoir le nombre d'éléments en erreurs
				    //$this->incrementRawDatasError($rawNbrElements);
				    $msgErr = "Exception $mode writing with message: ".$e->getMessage();
				}

                if ($r === false && $msgErr) {
					if ($this->behavior & self::STOP_ON_WRITE_ERR ) {
					    $this->fault($msgErr);
					} else {
					    $this->log($msgErr, 'err');
					}
				}

			} else {
			    //Mode Line :

				foreach ($this->rawDatas as $rawData) {
					$this->setRawData($rawData);
					try {
    					$msgErr = '';
                        $this->dispatchEvent("beforeSend");
						$r = $this->_eaiWriteRawData();
                        //dump("retour:", $r);
						//Traitement du message de retour dans le cas d'un retour vide
						if( empty($r) ) {
						    $this->incrementRawDatasWarn();
						    $msgErr = '';
                            $this->log("Data n°".$this->rawDatasWrited ." mode line return empty. (data agregate ?)", 'warn');
						} else {
    						$this->incrementRawDatasWrited();
						}

						$this->dispatchEvent("afterSend");
					} catch (Mage_Api_Exception $e) {
    				$msgErr = "Exception Mage Api $mode writing with message: ".$e->getCustomMessage();
    				$r = false;
    				$this->incrementRawDatasError();
					} catch (Exception $e) {
	    				$msgErr = "Exception $mode writing with message: ".$e->getMessage();
	    				$r = false;
	    				$this->incrementRawDatasError();
	    			}

					if ($r === false && $msgErr) {
    					if ($this->behavior & self::STOP_ON_WRITE_ERR ) {
    					    $this->fault($msgErr);
    					} else {
    					    $this->log($msgErr, 'err');
    					}
					}
				}
			}
		} else {
            $this->isLast = true;
			$r = false;
		}


		//dump("************** writeRawDatas($mode) :", $this->rawDatas, "return :", $r, "nb rawDatasWrited:", $this->rawDatasWrited);

		$this->rawDatas = array();

		$this->methodFinish($r);
		return $r;
	}




	/**
	 * @uses subclass->_eaiDisconnect($error= false)
	 *
	 * @return boolean
	 */
	public function disconnect($error= false)
	{

	    $this->setStopOnError($error);

		$this->methodStart();

		$r = $this->_eaiDisconnect($error);

		$this->methodFinish($r);
		return $r;
	}

	/**
	 * @return boolean
	 */
	public function isEOF()
	{
// 		$this->methodStart();

// 		$this->methodFinish($this->EOF);
		return (bool)$this->EOF;
	}

	/**
	 * @param boolean $EOF
	 * @return boolean
	 */
	public function setEOF($EOF = true)
	{
	  $this->methodStart();
		$this->EOF = $EOF;

		//TODO: pas le role de cette méthode
		$this->rawData = false;
		//dump("setEOF", $EOF);
		$this->methodFinish();
		return true;
	}

	public function logReport()
	{
	    if ($this->way == Esb::WAY_IN) {
	    	$this->log("report rawDatas fetched {$this->way} : ".$this->rawDatasFetched);
	    } else {
	    	$this->log("report rawDatas writed {$this->way} : ".$this->rawDatasWrited);
	    }
	    $this->log("report rawDatas errors {$this->way} : ".$this->rawDatasError, $this->rawDatasError ? 'err'  : 'info');
	    $this->log("report rawDatas warning {$this->way} : ".$this->rawDatasWarn, $this->rawDatasWarn  ? 'warn' : 'info');
	}

	public function getRawData()
	{
		return $this->rawData;
	}

	public function setRawData($rawData)
	{
		$this->rawData = $rawData;
	}

 	public function addRawDatas($rawDatas)
 	{
 		$r = false;
 		if (!empty($rawDatas) || (!is_array($rawDatas) && strlen($rawDatas) > 0)) {
 			$this->rawDatas = array_merge($this->rawDatas, Esb::collection($rawDatas));
 			$r = true;
 		}
 		return $r;
 		//dump('addRawDatas()', $this->rawDatas);
 	}

 	/**
 	 * fonction qu'il faut surcharger dans les classes dérivée si on peut optimisé l'écriture d'element multiple
 	 */
 	protected function _eaiWriteRawDatas()
 	{
 		$this->methodStart();
 		foreach ($this->rawDatas as $rawData) {
 			$this->setRawData($rawData);
 			$this->_eaiWriteRawData();
 		}
 		$this->methodFinish();
 	}

    public function getWay()
    {
        return $this->way;
    }

    /**
     * Incremente le compteur Raw Datas Fetched.
     */
	protected function incrementRawDatasFetched($step=1)
	{
		$this->rawDatasFetched += $step;

		if ($this->way == 'in') {
			$this->dbRow->increment($this->way . '_raw_datas_fetched', $step);
		}
	}

	/**
     * Incremente le compteur Raw Datas Writed.
     */
	protected function incrementRawDatasWrited($step=1)
	{
		$this->rawDatasWrited += $step;

		if ($this->way == 'out') {
			$this->dbRow->increment($this->way . '_raw_datas_writed', $step);
		}
	}

	/**
	 * Incremente le compteur Raw Datas Warn.
	 */
	protected function incrementRawDatasWarn($step=1)
	{
		$this->rawDatasWarn += $step;

		if ($this->way == 'in' || $this->way == 'out') {
			$this->dbRow->increment($this->way . '_raw_datas_error', $step);
		}
	}

	/**
	 * Incremente le compteur Raw Datas Error.
	 */
	protected function incrementRawDatasError($step=1)
	{
		$this->rawDatasError += $step;

		if ($this->way == 'in' || $this->way == 'out') {
			$this->dbRow->increment($this->way . '_raw_datas_error', $step);
		}
	}

}