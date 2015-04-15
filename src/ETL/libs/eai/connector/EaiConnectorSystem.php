<?php
/**
 * Classe de connexion a un fichier non-texte, chemin local, itère sur le nom de fichiers
 * (ou les noms de fichier, avec un plugin zip), en local (ou distant avec la plugin 'ftp')
 *
 * @see EaiObserverFtpIn.php
 * @see EaiObserverZipIn.php
 *
 * @author tbondois
 *
 */
class EaiConnectorSystem extends EaiConnector
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
		$this->setClassContext('dir' , $this->dir );
		if ($this->way == Esb::WAY_IN) {
			$this->pointer = 0;

			$iterator = new DirectoryIterator($this->dir);

			foreach ($iterator as $fileInfo) {
				if (!$fileInfo->isDir()) {
					//dump($fileInfo, (string) $fileInfo);kill();
					$this->files[] = $fileInfo->getFilename();	//envoie le nom de fichier, sans chemin complet
				}
			}
			sort($this->files);//tri par ordre alphabétique sinon c'est vraiment en vrac
		}
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
		if(isset($this->files[$this->pointer])) {
			$rawData = array('dir' => $this->dir,
					'file' => $this->files[$this->pointer]);
			$this->pointer++;
		} else {
			$rawData = false;
			$this->setEOF();
		}
		//dump($this->pointer, $rawData, $this->EOF);
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