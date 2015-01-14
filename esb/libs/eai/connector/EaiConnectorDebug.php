<?php
/**
 * Classe de connexion a un fichier accessible via un chemin local
 *
 * @author tbondois
 *
 */

class EaiConnectorDebug extends EaiConnector
{

	/**
	 * @see EaiConnector::connect()
	 * @return boolean
	 */
	public function _eaiConnect()
	{

		return true;
	}

	/**
	 * @see EaiConnector::disconnect()
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
    return true;
	}

	/**
	 * @see EaiConnector::fetchRawData()
	 * @return mixed
	 */
	public function _eaiFetchRawData()
	{

		return array();
	}

	/**
	 * @see EaiConnector::writeEaiDatas()
	 * @return bool
	 */
	public function _eaiWriteRawData()
	{
        $this->methodStart();
    		dump($this->getRawData());
        exit();

		return true;
	}

	public function _eaiWriteRawDatas()
	{
		$this->methodStart();

		$rawData = $this->getRawData();

		foreach($this->getRawDatas() as $datas) {
      dump($datas);exit();
    }

		return true;
	}

}//class