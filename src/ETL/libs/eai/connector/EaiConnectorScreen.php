<?php
/**
 * Classe de connexion a un fichier accessible via un chemin local
 *
 * @author cdebosque
 *
 */

class EaiConnectorScreen extends EaiConnector
{

	/**
	 * @see EaiConnector::connect()
	 * @return boolean
	 */
	public function _eaiConnect()
	{
		$this->_eaiContructHeader();
		return true;
	}

	/**
	 * @see EaiConnector::disconnect()
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		WriteTruc ("Connector Screen : _eaiDisconnect");
		//$this->_eaiContructFooter();
    	return true;
	}

	/**
	 * @see EaiConnector::fetchRawData()
	 * @return mixed
	 */
	public function _eaiFetchRawData()
	{
		//WriteTruc ("Connector Screen : _eaiFetchRawData");
		
		return array();
	}

	/**
	 * @see EaiConnector::writeEaiDatas()
	 * @return bool
	 */
	public function _eaiWriteRawData()
	{
		//WriteTruc ("Connector Screen : _eaiWriteRawData");
		
        $this->methodStart();
    	//	dump($this->getRawData());
        exit();

		return true;
	}

	public function _eaiWriteRawDatas()
	{
		$this->methodStart();
		//WriteTruc ("Connector Screen : _eaiWriteRawDatas");
		//dump($rawData);
		
		foreach($this->getRawDatas() as $datas) {
      		exit();
    	}

		return true;
	}
	
	public function _eaiContructHeader()
	{
		echo '
		<!DOCTYPE HTML>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link type="text/css" rel="stylesheet" href="www/css/screen.css">
		</head>
		
		<body>
		<div id="dbm-table">
		
		<div id="dbm-header">
		<span class="header-text">FLUX ...</span><br>
		<a href="http://www.dbmwebdesign.fr">By www.dbmwebdesign.fr</a></div>
		
		<div id="tb-top">
		<div class="tb-top-cell1">Col1</div>
		<div class="tb-top-cell">Col2</div>
		<div class="tb-top-cell">Col3</div>
		<div class="tb-top-cell">Col4</div>
		<div class="tb-top-cell" style="border:none;">Col5</div>
		</div>				
				<div id="tb-corps">
				';
	}
	
	public function _eaiContructFooter()
	{

		echo '
		</div>
		<div id="tb-footer">
		* TODO : Résumé de l"import.
		</div>
		</div>
		</body>
		</html>
		';
	}

}//class