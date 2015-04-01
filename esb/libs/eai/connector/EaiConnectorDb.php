<?php
/**
 * Classe de connexion a une base de données
 *
 *
 * @author tbondois
 *
 */
class EaiConnectorDb extends EaiConnector
{

	public $dir    ;

	protected $files   = array();

	protected $pointer = 0;

	/**
	 * @see EaiConnector::connect()
	 * @return boolean
	 */
	public function _eaiConnect()
	{
		$r = true;
		
		
		return $r;
	}


	/**
	 * @see EaiConnector::disconnect()
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		$r = true;

		return $r;
	}

	/**
	 * @see EaiConnector::fetchRawData()
	 * @return mixed
	 */
	public function _eaiFetchRawData()
	{
		
		return $rawData;

	}


	/**
	 * @see EaiConnector::send()
	 * @return bool
	 */
	public function _eaiWriteRawData()
	{

	}




}//class