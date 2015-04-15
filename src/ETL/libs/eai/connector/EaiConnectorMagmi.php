<?php
/**
 *
 * @author jaymard
 *
 */
class EaiConnectorMagmi extends EaiConnector
{
	protected $profil;
	protected $action;
	protected $magmiPump;


	/**
	 * @return boolean
	 */
	//public function connect()
	public function _eaiConnect()
	{
		$r = false;

		ini_set('include_path', get_include_path() . PATH_SEPARATOR . Esb::LIBS.'ext/magmi/inc');
		ini_set('include_path', get_include_path() . PATH_SEPARATOR . Esb::LIBS.'ext/magmi/engines');
		require_once(Esb::LIBS.'ext/magmi/integration/inc/magmi_datapump.php');

		/*
		 * create a Product import Datapump using Magmi_DatapumpFactory
		 */
		$this->magmiPump = Magmi_DataPumpFactory::getDataPumpInstance("productimport");

		if ($this->magmiPump) {
			$r = true;
		}

		return $r;
	}

	/**
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		/* End import Session */
    $this->magmiPump->endImportSession();

		return true;
	}


	public function _eaiWriteRawData()
	{
		$r = true;

		/*
		 * Begin Import session with a profile & running mode, here profile is "default" & running mode is "create" (ie: create new & update existing)
		 * IMPORTANT : for values other than "default" Profile has to be an existing magmi profile
		*/
		$this->setMethodContext('profil', $this->profil);
		$this->setMethodContext('action', $this->action);

		$this->magmiPump->beginImportSession($this->profil, $this->action);

		$rawData= $this->rawData;

        /*
        * Now ingest item into magento
        */
		//dump('magmiPump->ingest', $rawData);
		$this->magmiPump->ingest($rawData);

		return $r;
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

	public function _eaiFetchRawData()
	{

	}

}//class
