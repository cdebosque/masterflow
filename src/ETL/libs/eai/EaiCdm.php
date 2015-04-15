<?php
/**
 * Cdm comme Canonical Data Model
 * Fonctionne à base de fichier XSD (XML Schema Definition)
 * Il faut transforter un array eaiData en XML pour ensuite le parser avec le ficher xsd correspondant afin de vérifier la validité
 *
 * @package eai-generic
 *
 * @author tbondois
 *
 */
class EaiCdm extends EaiObject
{
	/**
	 * Nom du modèle. Doit correspondre à l'arborescence dans Esb::CDM
	 * @var string
	 */
	protected $identifier;

	/**
	 * Namespace xml défini dans les fichiers xsd
	 * @var unknown_type
	 */
	protected $namespace = 'cdm';

	/**
	 * Chemin de base des fichiers CDM
	 * @var unknown_type
	 */
	protected $dir       = Esb::CDM;

	/**
	 * nom de fichier standard des fichiers xsd
	 * @var string
	 */
	protected $file = 'cdm.xsd';

	/**
	 * Données du schema
	 * @var array
	 */
	protected $schema    = array();

	/**
	 * donnée eaiData transformé pendant la validation
	 * @var array
	 */
	protected $eaiData = array();

	public function __construct($identifier)
	{
		parent::__construct();
		$this->methodStart();

		$this->identifier = strtolower($identifier);	//le name défini dans le fichier model.xml. Normalement $name == $identifier

		if (!$this->defineSchema()) {
			$this->fault("CDM Schema '{$this->identifier}' undefined in {$this->getPath()}");
		}
		$this->methodFinish();
	}

	public function getPath()
	{
		$this->methodStart();

		$r = $this->dir.$this->identifier.'/'.$this->file;

		$this->methodFinish($r);
		return $r;
	}

	function refactor(&$data, $key = null)
	{
		if (is_array($data)) {
			if(in_array(array('complexType', 'complexContent', 'sequence', 'all'), $key($data))  ){
				$data = $data[$key($data)];
			}
		}
		return $data;
	}


	public function getSchemaElements()
	{
		$this->methodStart();
		$r = array();
		if (isset($this->schema[key($this->schema)]['values'])
				&& !empty(
							$this->schema[key($this->schema)]['values'])
		) {
			$r =    $this->schema[key($this->schema)]['values'];
		}
		$this->methodFinish($r);
		return $r;
	}


	protected function defineSchema()
	{
		$this->methodStart();
		$r = false;

		if (!$this->identifier) {
			$r = true;
		} else {
			$filePath = $this->getPath();

			if (file_exists($filePath)) {

				// tri et filtre des balises non définie dans le xsd (TODO améliorer) :

				$strCdm = str_replace(($this->namespace.':'), '', file_get_contents($filePath));//Pb sur simplexml quand il y a des namespace - le 4eme paramètre n'est pas pris en compte dans les sous-objets

				$simpleXml = simplexml_load_string($strCdm);

				if (is_object($simpleXml)) {
					$arrCdm = EaiFormatter::arrayFromObject($simpleXml);

					$this->schema = $this->formatSchema($arrCdm['element']);
					if (!empty($this->schema)) {
						$r = true;
					}
				}
			} else $this->log("Not exist file {$this->getPath()}", 'warn');
		}
		//dump('$this->schema', $this->schema);;
		$this->methodFinish($r);
		return $r;
	}

	/**
	 * enleve les champs non définis dans le xsd, et instancie $this->eaiData
	 *
	 * @param array $mappedData
	 * @return multitype:
	 */
	protected function prepare($mappedData)
	{
		$this->methodStart($mappedData);

		if(!$this->identifier) {
			$this->eaiData = $mappedData;
		} else {

			$this->eaiData = array();
			foreach ($this->getSchemaElements() as $name => $properties){
				//on boucle sur les attributs du CDM : les champs non définis dans le CDM seront donc filtrés
				if (isset($mappedData[$name])) {
					$this->eaiData[$name] = $mappedData[$name];
				} else {
					//L'attribut est dans le xsd du CDM mais pas présent dans la data en entrée
					//$this->log("CDM attribute '$name' missing in mapped data");
					//dump($mappedData);
				}
				//dump("$name => ",$properties,$this->eaiData[$name],  $eaiData, $this->eaiData);
			}
// 			echo var_dump($this->eaiData);
		}

// 		echo var_dump($mappedData);
		//$this->eaiData = $eaiData;//TODO supprimer
		if(empty($this->eaiData) && !empty($mappedData)) {
			$this->log("Cdm.prepare return empty datas. Check if the 'model' defined in your interface.xml is good. If are allowed to not use a model", 'warn');
			//dump($mappedData, $this->eaiData, $this->identifier);
		}
		$this->methodFinish($this->eaiData);
		return $this->eaiData;
	}

	public function validate($mappedData)
	{
		$this->methodStart();
		$r = false;

		//enleve les champs non définis dans le xsd, et instancie $this->eaiData :
		$this->prepare($mappedData);

		//dump('preparedEaiData', $this->eaiData);

		if (!$this->identifier) {
// 			dump("no identifier");
			return true;
		}

 		return true;//////////////TODO

		if (!empty($this->eaiData)) {

			$formatter = new EaiFormatterXml();
			$formatter->setXmlNodeRoot(null);
			$formatter->setXmlNodeElement($this->identifier);

			libxml_use_internal_errors(true);

			$dom = new DOMDocument($formatter->getVersion(), $formatter->getEncoding());

			$dom->loadXML($formatter->getRawFromElement($this->eaiData));
			if ($dom->schemaValidate($this->getPath())) {
				$this->log("schemaValidate() : ok");
				$r = true;
			} else 	{

				$this->log("schemaValidate() : eaiData no valid for the schema : ".$this->getPath(), 'err');
				//see http://www.php.net/manual/fr/domdocument.schemavalidate.php#62032
				$errors = libxml_get_errors();
				foreach ($errors as $error) {

					$this->log("XSD validation error ".$error->level.": ".trim($error->message), 'err');
				}
				libxml_clear_errors();
			}

			libxml_use_internal_errors(false);

		} else $this->log("Probleme during preparing eaiData, now empty", 'warn');


		$this->log("On force validate() a renvoyer true", 'debug');
		$r = true;//TODO supprimer

		dump("validate() retourne:", $r, "chemin du xsd :", $this->getPath(), "eaiData en entree:", $mappedData, "eaiData apres preparation:",$this->eaiData);

		if (!$r) {
			$this->log("Cdm not validated for an eaiData.", 'warn');
		}
		$this->methodFinish($r);
		return $r;
	}

	/**
	 * Formatte un tableau reprenant l'arbo xml d'un xsd, en un format maison...
	 * Attention fonction récursive
	 *
	 * @param array $datas
	 *
	 * @author VP
	 *
	 * @return array
	 */
	protected function formatSchema($datas)
	{
		$magicArray = array();
		if (isset($datas['@attributes']['name'])) {
			$keyName = $datas['@attributes']['name'];

			if (isset($datas['complexType'])) {
				$keyType=  key($datas['complexType']);
        if (!empty($datas['complexType'][$keyType]['element'])) {
          $values = $this->formatSchema($datas['complexType'][$keyType]['element']);
        } else {
          $values = array();
          $this->log("Element complexe vide : ".$keyName, "err");
        }
 				$magicArray[$keyName] = array( 'type'=> $keyType, 'values'=> $values);
			} else {

				$keyType = $datas['@attributes']['type'];
				$magicArray[$keyName]= array( 'type'=> $keyType );
			}
		} else {
			foreach ($datas as $element) {
				$magicArray = array_merge($magicArray, $this->formatSchema($element));	//alternative : $magicArray+= $this->xsdTranform($element);
			}
		}
		return $magicArray;
	}


}
