<?php
/**
 * Classe de connexion SOAP
 *
 * @todo _eaiWriteRawDatas
 *
 * @author tbondois
 *
 */
class EaiConnectorSoap extends EaiConnector
{
	protected $url;

	protected $version = 1;

	protected $user;

	protected $key;

	protected $soapparams = array();

	protected $header = array();

    // Permet de définir la methode de connexion
    protected $useLogin = 1;

	/**
	 * Méthode d'appel du client Soap
	 * @var string
	 */
	protected $call = 'call';

	/**
	 * Tablea d'arguments passé à l'appel du client Soap
	 * @var array
	 */
	protected $callparams= array();

	protected $action; //obsolete ne devrait plus être utilisé

	protected $client;

	protected $session;

	protected $results = array();

    /**
     * Active ou non l'historique
     * @var bool
     */
    protected $history = false;

    protected $historyFile;

	protected $xmlRequest;
	/**
	 * Utilisation d'un fichier temporaire en entrée
	 * dans lequel la donnée est stockée sérialisée
	 * @var string
	 */
	protected $useTempFile = false;

    /**
	 * @see EaiConnector.connect()
	 *
	 * Utilisation d'un fichier temporaire en entrée
	 * dans lequel la donnée est stockée sérialisée
	 * @var boolean
	 */
    public function _eaiConnect()
	{
		ini_set("soap.wsdl_cache_enabled", "0");

		//Controle des paramètres de connexion :

		if (strpos($this->url, "://") === false) {
			$this->url = "http://".$this->url;
		}

		$this->setClassContext('url' , $this->url);
		$this->setClassContext('user', $this->user);
		$this->setClassContext('key' , $this->key);


		if (empty($this->soapparams)) {
    		$this->soapparams = array('connection_timeout' => 600,
    		                         'trace' => 1,
    		                         // https://bugs.php.net/bug.php?id=37054
    		                         'user_agent' => ''
    		                        );
		}

		$this->client = new SoapClient($this->url, $this->soapparams);
		if ($this->useLogin) {
			$this->session = $this->client->login($this->user, $this->key);
		}

		$this->setSoapHeader();

		if (!empty($this->callparams) and is_array($this->callparams)) {
			array_walk_recursive($this->callparams, 'static::parseBrackets', 0x07 | 0x08);
		} elseif (!empty($this->action)) {
			$this->callparams= array();
            if ($this->session) {
                $this->callparams['session'] = $this->session;
            }
            $this->callparams['action'] = $this->action;
		} else {
			$this->fault('no action defined');
		}

		//On se base sur l'exception pour le retour
		return true;
	}

	/**
	 *
	 * @return false|array;
	 */
	public function _eaiFetchRawData()
	{
		$this->methodStart();
		if (empty($this->results)) {

			if ($this->getUseTempFile() and file_exists($this->getUseTempFile())) {
			    $this->results = unserialize(file_get_contents($this->getUseTempFile()));
                //$this->results = array_slice($this->results[0], 0,2);
                //var_dump($this->results);
                //file_put_contents($this->getUseTempFile(),  serialize($this->results));
                $this->dispatchEvent('onSoapCall');
			} else {
    			$this->setMethodContext('call', $this->call);
    			$this->setMethodContext('callparams', $this->callparams);
                try {

                    $this->results = call_user_func_array(array($this->client, $this->call), $this->callparams);
                    $this->dispatchEvent('onSoapCall');
                    if ($this->debug) {
                      dump($this->client->__getLastRequest());
                    }
                    $this->historize();

                } catch(SoapFault $fault) {
                    throw new Exception('Soap Fault : '.$fault->getMessage().PHP_EOL."Request : ".htmlentities($this->client->__getLastRequest()));
                }

                if ($this->debug) {
                  dump($this->results);
                }
                if ($this->getUseTempFile()) {
                    file_put_contents($this->getUseTempFile(),  serialize($this->results));
                }

                $this->historize();

			}

		}

        $this->dispatchEvent('onSoapFetch');

		if (isset($this->results[$this->rawDatasFetched])) {
			$rawData = $this->results[$this->rawDatasFetched];
			if (is_object($rawData)) {
				$rawData = EaiFormatter::arrayFromObject($rawData);
// 				dump($rawData);
			}
		} else {
			$rawData = false;
		}

		if (!isset($this->results[$this->rawDatasFetched+1]) ){
			$this->setEOF();

		}

        if ($this->debug) {
          // Affichage du raw data
          dump($rawData);
        }

		$this->methodFinish();
		return $rawData;
	}

	public function _eaiWriteRawData()
	{
		$this->methodStart();
		$this->results = false;

		if (!empty($this->rawData)) {
            if ($this->debug) {
                dump($this->rawData);
            }

    		$this->setMethodContext('call', $this->call);
    		$this->setMethodContext('callparams', $this->callparams);

//     		return true;

            try {
              if ($this->call == '__doRequest') {
                if ($this->debug) {
                  // Affichage du resultats
                  dump($this->xmlRequest);
                }
                $this->results = $this->doRequest();
              } else {


                $this->results = call_user_func_array( array($this->client, $this->call),
                                                       array_merge($this->callparams, $this->rawData)
                                                     );
                if ($this->debug) {
                  // Affichage du resultats
                  dump($this->client->__getLastRequest());
                }
              }

              $this->dispatchEvent("onSoapWrite");

              if ($this->debug) {
                // Affichage du resultats
                dump("soap result:", $this->results);
              }
            } catch(SoapFault $fault) {

                $msg= $fault->getMessage();
                if ($this->header) {
                    $msg.= PHP_EOL.'Request :'.$this->client->__getLastRequest();
                }

                throw new Exception($msg);
            }


		} else {
            //TODO: Gestion du dernier enregistrement
            // propriété de classe pour indiquer la dernière insertion?
            //dump("derniere insertion", $this->rawData);
		}

		//On se base sur l'exception pour le retour
		return $this->results;
	}

	/**
	 * @todo multicall
	 */
	public function _eaiWriteRawDatas()
	{
		$this->methodStart();
		foreach ($this->rawDatas as $rawData) {
			$this->setRawData($rawData);
			$this->_eaiWriteRawData();
		}
		$this->methodFinish();
	}

	/**
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		$r = true;

    if (!empty($this->client) && $this->session) {
    		$this->client->endSession($this->session);
		}
		return $r;
	}

  /**
   * Met à jour le header du client SOAP selon les paramtre passés dans le fichier d'interface
   */
  protected function setSoapHeader()
  {
    if (!empty($this->header)) {
      $header = "<".$this->header['namespace'].":".$this->header['name'].">\n";
      foreach ($this->header['params'] as $id => $value) {
        $header .= "<".$this->header['namespace'].":".$id.">"
          .$value
          ."</".$this->header['namespace'].":".$id.">";
      }
      $header .= "</".$this->header['namespace'].":".$this->header['name'].">\n";

      $soap_var_header = new SoapVar($header, XSD_ANYXML, null, null, null);
      $soap_header = new SoapHeader($this->url,
              $this->header['namespace'],
              $soap_var_header);
      $this->client->__setSoapHeaders($soap_header);
    }
  }
  /**
   * Encapsulation de la methode __doRequest de php
   * @return <type>
   */
  public function doRequest() {
    if ($this->xmlRequest === null) {
      return true;
    }

    $this->setXmlRequest('<?xml version="1.0" encoding="UTF-8"?>
      <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">'
      .$this->xmlRequest.'</SOAP-ENV:Envelope>');
    $result = $this->client->__doRequest(
      $this->xmlRequest,
      $this->url,
      $this->callparams['urlAction'].'/'.$this->callparams['action'],
      $this->version);

    return XML2Array::createArray($result);
  }

    protected function historize()
    {
        //dump("historize");
        if ($this->history) {
            $historyDir = Esb::WORKBASE.Esb::registry('identifier')."/history/";
            $this->historyFile = $historyDir.date('Y-m-d_H.i.s')."_".getmypid().".txt";

            if (Esb::checkDir($historyDir)) {
                /*$content = datetime()." *** URL :";
                $content.= $this->url;
                $content.= PHP_EOL.PHP_EOL.datetime()." *** CALL PARAMS :".PHP_EOL;
                $content.= print_r($this->callparams, true);
                $content.= PHP_EOL.PHP_EOL.datetime()." *** LAST REQUEST :".PHP_EOL;
                $content.= serialize($this->client->__getLastRequest());
                $content.= PHP_EOL.PHP_EOL.datetime()." *** RESULTS :".PHP_EOL;
                $content.= print_r($this->results, true);*/

                $lines = array();
                $lines[] = datetime() ." ".$this->historyFile;
                $lines[] = "URL :";
                $lines[] = $this->url;
                $lines[] = "CALL PARAMS :";
                $lines[] = print_r($this->callparams, true);
                $lines[] = "LAST REQUEST :";
                $lines[] = serialize($this->client->__getLastRequest());
                $lines[] = "RESULTS :";
                $lines[] = print_r($this->results, true);
                $content = implode(PHP_EOL." # ", $lines);
                file_put_contents($this->historyFile, $content);
                //dump("History content :", $this->historyFile, $content);
            }
        }
    }

}//class
