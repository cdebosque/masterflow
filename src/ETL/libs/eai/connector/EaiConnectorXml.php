<?php
/**
 * Hack de ConnectorFile utilisé pour l'export en format XML
 * @author tbondois
 *
 */

class EaiConnectorXml extends EaiConnector
{

	protected $dir    ;

	protected $file   ;

	protected $mode   = 'r';

	/**
	 * @var mixed
	 */
	protected $pointer;

	/**
	 * @see EaiConnector::connect()
	 */
	public function _eaiConnect()
	{

	    $this->setClassContext('dir', $this->dir);
	    $this->setClassContext('file', $this->file);

	    $xmlFile= $this->dir.'/'.$this->file;

	    if (file_exists($xmlFile)) {
	        $xml = EaiFormatter::arrayFromObject(simplexml_load_file($xmlFile, 'SimpleXMLElement',LIBXML_NOCDATA));
	        if( !empty($xml) )
	        {

    	        $firstKey = key($xml);
    	        $secondKey = key($xml[$firstKey]);
    	        //dump($firstKey, $secondKey, $xml);
    	        if($secondKey === 0) {
    	            $this->allElements = $xml[$firstKey];
    	        } else {
    	            $this->allElements =  array(0 => $xml[$firstKey]);
    	        }
	        }

	        $r = true;
	    } else {
	        $r = false;
	    }

		return $r;
	}

	/**
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		$this->allElements = null;

		return true;
	}

	/**
	 * @see EaiConnector::fetchRawData()
	 * @return mixed
	 */
	public function _eaiFetchRawData()
	{
	  $rawData = $this->allElements[$this->rawDatasFetched];

		if (!isset($this->allElements[$this->rawDatasFetched+1])) {
			$this->setEOF();
		}
		return $rawData;
	}

	/**
	 * @see EaiConnector::send()
	 * @return bool
	 */
	public function _eaiWriteRawData()
	{
		$this->methodStart();

		$rawdata = $this->getRawData();

		$written = (bool)fwrite($this->pointer, $rawdata);

		$this->methodFinish($written);
		return $written;
	}

	public function _eaiWriteRawDatas()
	{
		$this->methodStart();
		foreach ($this->rawDatas as $rawData) {
			$this->setRawData($rawData);
			$this->_eaiWriteRawData();
		}
		$this->methodFinish();

		return true;
	}

}//class