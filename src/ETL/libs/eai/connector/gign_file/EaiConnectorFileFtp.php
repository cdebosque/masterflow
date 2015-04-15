<?php
/**
 *
 * @author tbondois
 */
class EaiConnectorFileFtp extends EaiConnectorFile
{

	protected $server    ;

	protected $user      ;

	protected $password  = '';

	protected $port      = 21;

	/**
	 * Chemin distant, a partir de la racine FTP
	 * @var string
	 */
	protected $remoteDir ;

	/*
	 * Note : on doit utiliser $this->file (défini dans EaiConnectorFile) dans la configuration pour préciser le fichier a récupérer/créer
	 */


	/**
	 * timeout de connexion au serveur, en secondes
	 * @var int
   */
	protected $timeout   = 360;

	/**
	 * Indique la connexion FTP en mode passive (ou active)
	 * @var int 0|1
	 */
	protected $passive   = 1;

	/**
	 * Mode de transfert, 1 pour FTP_ASCII , 2 pour FTP_BINARY
	 * @var int 1|2
	 */
	protected $transfertMode = FTP_BINARY;

	protected $allowRetransfert = 0;

	/**
	 * le stream de connexion ftp. .
	 * @var resource / false si erreur
	 */
	protected $connection;

	//+hérite et utilise $file, $dir, $pointer en interne


	/**
	 * @todo mkdir en cascade en local ?
	 * @see EaiConnector::connect()
	 * @return boolean
	 */
	public function _eaiConnect()
	{
		$r = false;

		if (is_null($this->dir)) {
			$this->dir = Esb::ROOT . 'var/ftp/' . Esb::registry('identifier').'/';
		}
		$this->setClassContext('dir' , $this->dir );
		$this->setClassContext('file', $this->file);

		if (is_null($this->connection)) {

			if (strlen($this->server) > 0 && strlen($this->user) > 0 && strlen($this->password) > 0 && strlen($this->file) > 0) {

				$localDirExist = false;
				if (file_exists($this->dir)) {
					$localDirExist = true;
				} else {
					if (mkdir($this->dir, 0777, true)) {
						$localDirExist = true;
						$this->log('Creating local directory recursively '.$this->dir);
					} else {
						$this->log('Bad local mkdir '.$this->dir, 'err');
					}
				}
				if ($localDirExist) {

					if($this->allowRetransfert || !file_exists($this->dir.$this->file)) {

						$this->connection = ftp_connect($this->server, $this->port);

						if ($this->connection) {
							if (ftp_login($this->connection, $this->user, $this->password)) {
								if (ftp_pasv($this->connection, (bool)$this->passive)) {
									if (ftp_chdir($this->connection, $this->remoteDir)) {
										dump("Fichiers dans le répertoire FTP :", ftp_nlist($this->connection, '.'), "recherche", $this->file, $this->dir.$this->file);

										if (ftp_get($this->connection, $this->dir.$this->file, $this->file, $this->transfertMode) ) {

											$r = parent::_eaiConnect();

										} else $this->log("connecting : bad ftp_get(connection, '{$this->dir}{$this->file}', '{$this->file}', '{$this->transfertMode}')", 'err');

									} else  $this->log("connecting : bad ftp_chdir(connection, '{$this->remoteDir}')", 'err');
								} else $this->log("connecting : bad ftp_pasv(connection, '{$this->passive}')", 'err');
							} else $this->log("connecting : bad ftp_login(connection, '{$this->user}', password)", 'err');
						} else  $this->log("connecting : bad ftp_connect({$this->server}, '{$this->port}')", 'err');

					} else $this->log("checking : local file {$this->dir}{$this->file} already exist, mode allowRetransfert = 0", 'err');
				} else $this->log("checking : local dir {$this->dir} not exist", 'err');
			} else $this->log("checking : bad configuration : required variable(s) missing (required : server, user, password, file)", 'err');

		} else {
			$r = true;
			$this->log("connecting : already done, aborted", 'warn');
		}
		return $r;
	}

	/**
	 * @see EaiConnector::disconnect()
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
		$r = ftp_close($this->connection);
		if (!$r) {
			$this->log("disconnecting : error", 'err');
		}
		parent::_eaiDisconnect($error);

		return $r;
	}

}//class