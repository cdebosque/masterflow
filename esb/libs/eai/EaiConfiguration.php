<?php
/**
 *
 * @package eai-generic
 *
 * @author tbondois
 *
 */
class EaiConfiguration extends EaiObject
{

	protected $identifier;

	protected $type;

	protected $config_base_path = '';

	/** @var array */
	public $datas = array();
	/**
	 * parsera en php les expressions entre {}
	 *
	 * @param string $file
	 */
	public function __construct($identifier, $type, $config_base_path = '')
	{
		parent::__construct();
		$this->methodStart();
		//on charge les données par rapport a un fichier, on les mémorise sous formes de propriété de la classe
		$this->type = $type;
		$this->identifier = $identifier;
		$this->config_base_path = $config_base_path;
		$this->datas = $this->parseDatasFromFile();
		$this->methodFinish();

		return $this->datas;
	}

	protected function parseDatasFromFile()
	{
		$this->methodStart();
		$datas = array();

		$ext = 'xml';
		$configFile= $this->type.(strrpos($this->type, ".$ext") ? '' : ".$ext");


		$fileSpecific = Esb::ETC.$this->config_base_path.$this->identifier.DIR_SEP.$configFile;
		$fileBase  = Esb::ETC.'core'.DIR_SEP.'base'.DIR_SEP.$configFile;
		
		if ($this->identifier) {

			$filePaths = array($fileBase, $fileSpecific);
		} else {
            //Il peut ne pas y avoir d'identifier si on est en train de parser un fichier core/base.
            //Dans ce cas il ne faut pas traiter le fileSpecific qui contient un mauvais path
			$filePaths = array($fileBase);
		}

        //Gestion de la configuration surchargé en paramètre d'entrée :
        $replaceDatas = null;
        $registryConfigKey = 'config:'.$this->type;
        $registryConfigValue = Esb::registry($registryConfigKey);

        if (!empty($registryConfigValue)) {
            //on utilise des clés de tableau non numérique pour identifier qu'il faut charger du xml a partir d'un string plutot que d'un fichier
            $filePaths[$registryConfigKey] = $registryConfigValue;
            //dump($registryConfigKey, $registryConfigValue);
        }



		foreach ($filePaths as $key => $filePath) {
// 				echo "<h2>File path : ".$filePath."</h2>";
			if (!is_numeric($key) || file_exists($filePath)) {
				//dump("exist:", $filePath);
                if(is_numeric($key)) {
// 				echo "<h2>File path : ".$filePath."</h2>";
                	$simpleXMLElement = simplexml_load_file($filePath);
                } else {
                    $simpleXMLElement = simplexml_load_string($filePath);
                }
				if (is_object($simpleXMLElement)) {
					$news = EaiFormatter::arrayFromObject($simpleXMLElement);
					$datas = array_merge_recursive($datas, $news);

				}	else {
					if(isset($this)) $this->log("filePath $filePath not exist in getDatasFromFile($this->type, $this->identifier)", 'warn');
				}
			} else {
// 				echo "<h2>". $filePath. "</h2>";
				
			    $this->log("not exist file $filePath", 'info');
				return false;
			}

			array_walk_recursive($datas, 'static::parseBrackets', (empty($this->identifier) ? 0x05 :0x03));
		}
        //if(!is_numeric($key)) dump($datas);
	 	$this->methodFinish();
		return $datas;
	}

	/**
	 *
	 * @param string $tree avec des / pour passer a une autre dimension
	 */
	protected function getNode($tree = null, $returnAttributes = false)
	{
		if(is_null($tree)){
			return $this->datas;
		}
		$indexes = explode('/', $tree);
		$data = $this->datas;
		foreach ($indexes as $index){
			if(isset($data[$index])){
				$data = $data[$index];
				if(is_scalar($data)){
					break;
				} elseif(is_array($data) && isset($data['@attributes'])) {
					if(!$returnAttributes)
					    unset($data['@attributes']);
				}
			} else {
				$data = null;
				$this->log("Not found node '$index' ".EaiDebug::getFunctionsTrace(), 'debug');
				break;
			}
		}
		return $data;
	}


	protected function setNode($tree = null, $data = false)
	{

	    if (!empty($tree)) {

	        $indexes = explode('/', $tree);
	        if (count($indexes)>1) {
        	    $this->log("Method setNode not working with real xpath :-(", 'error');
        	    $ret= false;
	        } else {
	            $this->datas[$tree]= $data;
	            $ret= true;
	        }
	    } else {
            $ret= false;
        }

        return $ret;
	}

	public function getAttribute($attribute, $tree = null)
	{
		$this->methodStart();
		$r = null;
		if ($this->hasAttribute($attribute, $tree)) {
			$r = $this->getNode($tree, true);
			$r = $r['@attributes'][$attribute];
			if(is_array($r)){
				$r = $r[count($r)-1];
			}
		}
		$this->methodFinish($r);
		return $r;
	}

	public function hasAttribute($attribute, $tree = null)
	{
		$this->methodStart();
		$r = false;
		$data = $this->getNode($tree, true);
		if (isset($data['@attributes'][$attribute])) {
			$r = true;
		}
		$this->methodFinish($r);
		return $r;
	}

	/**
	 * Récupère une valeur scalaire de fichier de config
	 * Si on a un tableau, on prends la valeur au dernier index :
	 * on considère en effet que c'est un tableau a cause du mécanisme de surcharge
	 * @param unknown_type $tree
	 * @return Ambigous <unknown, multitype:, NULL>
	 */
	public function getValue($tree = null)
	{
		$this->methodStart();
		$r = $this->getNode($tree);
		if(is_array($r)){
			$r = $r[count($r)-1];
		}

		$this->methodFinish($r);
		return $r;
	}

	public function setValue($key, $value)
	{
		$r = $this->setNode($key, $value);

		return $r;
	}



	public function getArray($tree = null)
	{
		$this->methodStart();
		$casted = false;
		$r = $this->getNode($tree);
		if (!empty($r) && !is_array($r)) {
 			$r = array($r);
 			$casted = true;
		}

		$this->methodFinish(array('return' => $r, 'casted' => $casted));
		return $r;
	}



	public function getIdentifier()
	{
		$this->methodStart();
		$r = $this->identifier;
		$this->methodFinish();
		return $r;
	}




}//class