<?php
/**
 * Classe de connexion a un fichier texte accessible via un chemin local
 * (Pour un accès ftp, utilise le plugin 'ftp'
 *
 * @see EaiObserverFtpIn.php
 * @see EaiObserverZipIn.php
 *
 * @author tbondois
 *
 */

class EaiConnectorFile extends EaiConnector
{
	protected $dirError= 'error';


	protected $dirProcessed= 'processed';

	protected $dirProcessing= 'processing';

	protected $pathError;

	protected $pathProcessed;

	protected $pathProcessing;

	protected $fileProcessing;

	protected $dir;

	protected $file;

	protected $filePattern;

	protected $csv;

	protected $workflow=false;

	/**
	 * Mode d'ouverture,standard de fopen
	 * @var string
	 */
	protected $mode;

	/**
	 * @var mixed
	 */
	protected $pointer;

	/**
	 * Lignes tampon permettant de reculer le pointeur
	 * @var array
	 */
	protected $tmpLines = array();

    /**
     * Permet de spécifier le caractère de fin de ligne. utiliser les constantes C_* définies dans getProp pour que ca fonctionne
     * @example C_EOL;
     * @var string
     */
    protected $ending;

  protected function _eaiInit() {

    if(!$this->dir) {
        $this->dir = Esb::WORKBASE.Esb::registry('identifier');
    }

    $this->workflow = (bool)$this->workflow;

		if (is_null($this->mode)) {
			if($this->way == Esb::WAY_OUT) {
				$this->mode = 'w';//Ouvre en lecture et écriture ; par défaut on écrase le fichier existant
			} else {
				$this->mode = 'r';//Ouvre en lecture seule, et place le pointeur de fichier au début du fichier.

			}
		}

		if (!$this->file && $this->filePattern) {
    		$iterator = new DirectoryIterator($this->dir);

    		foreach ($iterator as $path) {
    		    if (!$path->isDir() and preg_match('/'.$this->filePattern.'/', $path->getFilename()) ) {
    		        $this->file= $path->getFilename();
                    break;
    		    }
    		}
		}

    return parent::_eaiInit();
  }

	/**
	 * @see EaiConnector::connect()
	 * @return boolean
	 */
	public function _eaiConnect()
	{
    $r = false;

    $this->setClassContext('dir' , $this->dir );
		$this->setClassContext('file', $this->file);
		$this->setClassContext('mode', $this->mode);

    if (!$this->file) {
     	throw new Exception("No input file or nothing matching file '{$this->file}' or filePattern '{$this->filePattern}' in dir {$this->dir}");
    }
    $this->fileProcessing= Esb::fullPath($this->dir).$this->file;

    $logFilename = str_replace(Esb::ROOT, '', $this->fileProcessing);
    $this->dbRow->updateInSourceFile($logFilename);

        if ($this->workflow) {

            //Construction des paramètres processed
            $this->pathProcessed= Esb::fullPath($this->dir).Esb::fullPath($this->dirProcessed);
            Esb::checkDir($this->pathProcessed);

            //Construction du repertoire error
            $this->pathError= Esb::fullPath($this->dir).Esb::fullPath($this->dirError);
            Esb::checkDir($this->pathError);

            //Construction du repertoire processing
            $this->pathProcessing= Esb::fullPath($this->dir).Esb::fullPath($this->dirProcessing);
            Esb::checkDir($this->pathProcessing);

            $this->fileProcessing= $this->pathProcessing.$this->file;

            $filePath = Esb::fullPath($this->dir).$this->file;

            if ($this->way == Esb::WAY_IN) {

                if (!file_exists($filePath)) {
                    if (!file_exists($this->dir)) {
                        throw new Exception("Dir {$this->dir} not exists");
                    } else {
                        throw new Exception("File {$filePath} not exists");
                    }
                }

                //Construction du fichier processing
                if (!rename($filePath, $this->fileProcessing)) {
                    throw new Exception("Cannot move file {$filePath} to {$this->fileProcessing}");
                }
            }
        }

		//Ouverture du fichier à traiter
		if (is_null($this->pointer)) {

		  //Lever une exception en cas de non accès
      if (Esb::checkDir(dirname($this->fileProcessing))) {

    			$handle = fopen($this->fileProcessing, $this->mode);

    			if ($handle) {
    				$this->pointer = $handle;
    				$r = true;
    				$this->log("Opening successfully {$this->fileProcessing}");
    			}
      }


		}

		return $r;
	}

	/**
     * Dans le cas d'un plugin ftp, le transfert se fait avant ce changement de répertoire, et donc se base sur le dossier processing
	 * @see EaiConnector::disconnect()
	 * @return boolean
	 */
	public function _eaiDisconnect($error= false)
	{
        $r = false;

		if (!empty($this->pointer)) {
			$r = fclose($this->pointer);

			if ($this->workflow) {
    			if ($error) {
        			$destPath = $this->pathError;
    			} else {
    			    $destPath = $this->pathProcessed;
    			}

    			$destFile= $destPath.date('Y-m-d_H.i.s')."_".getmypid().'.'.$this->file;

    			if (!rename($this->fileProcessing, $destFile)) {
    			    $this->log("Cannot move file {$this->fileProcessing} to {$destFile}", 'error');
    			} else {
    			    $this->log("Move file {$this->fileProcessing} to {$destFile}");
    			}
			}

		} else {
			$r = true;
		}
		if (!$r) {
			$this->log("Bad fclose {$this->fileProcessing}");
		}
		return $r;
	}

	/**
	 * @see EaiConnector::fetchRawData()
	 * @return mixed
	 */
	public function _eaiFetchRawData()
	{
		if(!empty($this->tmpLines)){
			$rawData = current($this->tmpLines);
			array_shift($this->tmpLines);
		} else {
		  // Propriété <csv> ajoutée pour prendre en compte les sauts de ligne dans les descriptions CSV encapsulés entre guillemet.
		  // @TODO : dynamiser les propriétés. On n'a pas accès aux propriétés du formateur : separator, enclosure, escape
		  if($this->getProp('csv') == true) {
		    $separator = ';';
		    $enclosure = '"';
		    $escape = '"';
		    // @TODO : Vérifier que les lignes CSV vides ne posent pas de problème en cas de retour : Array(NULL).
		    // "fgetcsv() Une ligne vide dans un fichier CSV sera retournée sous la forme d'un tableau contenant la valeur NULL et ne sera pas traitée comme une erreur." (doc PHP).
		    // "fgetcsv() retourne NULL si un paramètre handle invalide est fourni ou FALSE en cas d'autres erreurs, y compris la fin du fichier."
		    $rawData = fgetcsv($this->pointer, NULL, $separator, $enclosure, $escape);
		  } elseif($this->getProp('ending')) {
				$rawData = stream_get_line($this->pointer, null, $this->getProp('ending')); //on a spécifié le séparateur de ligne. Utiliser les constantes comme C_EOL !
			} else {
				$rawData = fgets($this->pointer);//prendra \n, \r et \r\n comme séparateur de lignes (standards)
			}
		}

        $this->rawData = $rawData;

		if (feof($this->pointer)) {
            $this->dispatchEvent("onConnectorFileEOF");
            if (empty($this->tmpLines)) {
                $this->setEOF();
            }
		}
		return $this->rawData;
	}

	/**
	 * @see EaiConnector::send()
	 * @return bool
	 */
	public function _eaiWriteRawData()
	{
		$this->methodStart();

       	$data=$this->getRawData();
       	//@TODO: A VERIFIER
       	$data = $data[0][0];
		$written = (bool)fwrite($this->pointer, $data);

		$this->methodFinish($written);
		return $written;
	}

    /**
     *
     * @return bool
     */
	public function _eaiWriteRawDatas()
	{
		$this->methodStart();

		$rawData = $this->getRawData();
		$written = (bool)fwrite($this->pointer, implode('', $this->getRawDatas()) );

		$this->methodFinish($written);
		return $written;
	}

	public function reserveLastData()
	{
		$rawData = $this->getRawData();
		if(!empty($rawData)){
			$this->tmpLines[] = $rawData ;
		}

	}


}//class