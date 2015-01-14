<?php
/**
 *
 * @package eai-generic
 *
 * @author tbondois
 *
 */
class EaiMapper extends EaiObject
{
	protected $way;

	/**
	 * Le chemin vers les fichiers xml. Même valeur que dans EaiHandler
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var array
	 */
	protected $map = array();

	/**
	 * @var EaiCdm
	 */
	protected $cdm;

	/**
	 * Data before mapping
	 * @var array
	 */
	protected $element;

	/**
	 * Data after mapping
	 * @var array
	 */
	protected $eaiData;

	/**
	 * @var array
	 */
	protected $options;
	/**
	 *
	 * @var unknown_type
	 */
	protected $associatedSrc= array();

	/**
	 * @var string
	 */
	protected $config_key = 'mapping';

	protected $hierarchySeparator = '=>';

	/**
	 * @var EaiConfiguration
	 */
	protected $config;


	/**
	 *
	 * @var string model
	 *          issue de interface[model] du fichier core/base/interface.xml
	 *          issue de interface[model] du fichier ../../interface.xml
	 *       ou issue de mapping/object   du fichier ../../mapping.xml
	 *
	 */
	//protected $model;

	/**
	 * @var EaiModel format pivot
	 */
	//protected $eaiModel;



	/**
	 *
	 * @param string $identifier identifiant de fichier de config
	 * @param string $way in|out
	 */
	public function __construct($identifier, $way, EaiConfiguration $interfaceConfig = null)
	{
		parent::__construct();
		$this->methodStart();

		$this->way = $way;
		$this->identifier = $identifier;

    //$this->model = $interfaceConfig->getAttribute('model');//deprecated
		//$this->eaiModel = new EaiModel($this->model);//deprecated

		$this->cdm = new EaiCdm($interfaceConfig->getAttribute('model'));

		$this->options = $this->config()->getArray("$way/options");

		if ($this->identifier) {

			$fields = $this->config()->getArray("$way/fields/field");

			if (!empty($fields)) {

				$this->map = Esb::collection($fields);

				foreach ($this->map as $index => $def)  {
					//réassociation des clés manquantes
					if ((isset($def['from']) && $def['from'] != '') && (!isset($def['to']) || $def['to'] == '')) {
						//Pas de to defini : on prends la valeur du from
						$this->map[$index]['to']   = $this->map[$index]['from'];

					} elseif ((!isset($def['from']) || $def['from'] == '') && (isset($def['to']) && $def['to'] != '' )) {
						//Pas de from defini : on prends la position de colonne (commence a zéro)
						$this->map[$index]['from'] = $index;

					} elseif ((!isset($def['from']) || $def['from'] == '') && (!isset($def['to']) || $def['to'] == '' ) && (isset($def['calls']) && !empty($def['calls']))) {
						//Pas de from ni to mais un call
						$this->map[$index]['from'] = $index;
						$this->map[$index]['to']   = "_call_$index";

					} elseif ((!isset($def['from']) || $def['from'] == '') && (!isset($def['to']) || $def['to'] == '' )) {
						//Pas de from ni to ni de call, on ne gère pas ce cas
						$this->log("unset key $index of map : no from neither to defined", 'warn');
						unset($this->map[$index]);
					}

					if(isset($this->map[$index]['from']) && is_string($this->map[$index]['from'])) {
						$this->map[$index]['from'] = trim($this->map[$index]['from']);
					}
					if(isset($this->map[$index]['to']  ) && is_string($this->map[$index]['to']  )) {
						$this->map[$index]['to']   = trim($this->map[$index]['to']);
					}
				}

				 //dump("map : way '{$this->way}' identifier '{$this->identifier}'", $this->map);
			} else {
				$this->log("configuration de mapping vide dans '$identifier'", 'debug');
			}
		} else {
			$this->log("mapper '$way' en mode passoire, no identifier $identifier", 'debug');
		}
		$this->methodFinish();

	}

	/**
	 * Gère le binding avec mapping.xml et les règles de transformation/filtres définies
	 *
	 * @param array $inputData
	 * @return boolean|array
	 */
	protected function getMappedData($inputData)
	{
		$this->methodStart();
		$mappedData = array();

		/********/ /********/ /********/ /********/ /********/ /********/

		//dump($inputData, $this->map);
		$i = 0;
		if (count($this->map) >= 1) {

			foreach ($this->map as $index => $def) {
				//dump($index, $def, $inputData[$def['from']], $inputData);

				$i++; //if($i > 25) break;//pour le dumpage

				if(array_key_exists('constant', $def) && isset($def['to'])) {
					//La definition de <constant> rend inutile et ignoré le <from>
					$mappedData[$def['to']] = $def['constant'];
				} elseif (isset($inputData[$def['from']])
                       || strpos($def['from'], $this->getHierarchySeparator())) {
                    //Gestion d'une deuxième dimension
					if (strpos($def['from'], $this->getHierarchySeparator())) {
                        $path = explode($this->getHierarchySeparator(), $def['from']);
                        $inputVal = self::readHierarchy($inputData, $path);
					} else {
    					$inputVal = $inputData[$def['from']];
					}

					//Gestion d'une deuxième dimension
					if (strpos($def['to'], $this->getHierarchySeparator())) {
					    $mappedData= array_merge_recursive($mappedData, self::arrayCreateHierarchy(explode($this->getHierarchySeparator(), $def['to']), $inputVal));
					} else {
    					$mappedData[$def['to']] = $inputVal;
					}

					if (isset($def['calls']['call'])) {

						$calls = Esb::collection($def['calls']['call']);

						foreach ($calls as $call) {


							if ( isset( $call['@attributes']['name'])) {
								$method = $call['@attributes']['name'];
								if (isset($call['@attributes']['type'])) {
									$type = $call['@attributes']['type'];
								} else {
									$type = "transform";
								}
								unset($call['@attributes']);
								if ($type == 'test') {
									$args = array_merge(array($inputVal), $call);

									$returnedVal = $this->call($method, $args);
									if ($returnedVal === false) {
										$this->log("Test  $method negative for field {$def['from']} => $inputVal, skip", 'debug');
										return false;
									} else {
										$this->log("Test  $method ok for field {$def['from']} => $inputVal", 'debug');
									}
								} else {
									$args = array_merge(Esb::collection($inputVal), $call);
									$returnedVal = $this->call($method, $args);
									if(!is_null($returnedVal)) {
										$mappedData[$def['to']] = $returnedVal;
                                        $inputVal = $returnedVal;

									} else {
										$this->log("Call $method return null for {$def['from']} => $inputVal", 'debug');
									}
                                    //dump("call:", $def['to'], $inputVal, $returnedVal);
								}
							}
						}
					}//if isset calls
				} else {
					$mappedData[$def['to']] = null;//si l'index n'est pas présent dans eaiData, on le met quand meme artificiellement
				}
			}//foreach

		} else {
			//pas de mapper défini
			$mappedData = $inputData;
			//$this->log("mapper {$this->way} indefini ou incomplet", 'debug');
		}

		$this->methodFinish($mappedData);

		if(empty($mappedData) && !empty($inputData)) {
			$this->log("getMappedData return empty data. check if your mapping.xml is good", 'debug');
		}
// 		dump('$mappedData', $mappedData);
		return $mappedData;
	}

	/**
	 * @see EaiDriver.fetchEaiDatas()
	 *
	 * @param array $element
	 * @return array
	 */
	public function setEaiDataFromElement($element)
	{
		$this->methodStart();

		$this->element = $element;

		$this->dispatchEvent('beforeMap');//deprecated
		$this->dispatchEvent('beforeMapIn');

		$mappedData = $this->getMappedData($this->element);

		//$eaiData = $this->eaiModel->format($eaiData);

		if (!empty($mappedData) && $this->cdm->validate($mappedData)) {	//TODO réactiver
			$this->eaiData = $this->cdm->getEaiData();
			$this->dispatchEvent('onMap');//deprecated
			$this->dispatchEvent('onMapIn');
		} else {
			$this->eaiData = false;
		}
 		//dump('EaiMapper->eaiData apres preparation :', $this->eaiData, '$mappedData:', $mappedData, '$element:', $element);


		$this->methodFinish();
		// Warning a lever uniquement si on n'utilise pas de gestion de buffer dans l'observer.
		//if(is_array($element) && !empty($element) && empty($this->eaiData)) {
		//	$this->log("setEaiDataFromElement generate empty eaiData, but there is element in source", 'warn');
		//}
		return true;
	}

	/**
	 * @see EaiDriver.putEaiDatas()
	 * @param array $eaiData
	 * @return array
	 */
	public function getElementsFromEaiData($eaiData)
	{
		$this->methodStart();

		$this->eaiData = $eaiData;

		$this->dispatchEvent('beforeMap');//deprecated
		$this->dispatchEvent('beforeMapOut');

		$this->element = $this->getMappedData($this->eaiData);
		//dump(">getElementFromEaiData()", $this->eaiData, $this->element);		kill();

		$this->dispatchEvent('onMap');//deprecated
		$this->dispatchEvent('onMapOut');

		$this->methodFinish();
		//dump($this->element);
		return $this->element;
	}



	/********************************************************
	**********       Fonctions de filtrages        **********
	*********************************************************/



	public function testType($fied, $input)
	{
		$this->methodStart();
		$r = true;
		$this->methodFinish($r);
		return $r;
	}

	public function testRequired($fied, $input)
	{
		$this->methodStart();
		$r = true;

		$this->methodFinish($r);
		return true;
	}


	/**
	 * Fonction de call de type 'test' : teste que la valeur d'un champs doit fasse partie d'une liste de moté clés (séparés par un pipe)
	 *
	 * @param string $input
	 * @param string $keywords
	 * @return boolean
	 */
	public function testValueIn($input, $keywords)
	{
		$this->methodStart();
		$r = false;

		$arrWords = explode('|', $keywords);

		foreach ($arrWords as $word) {
			if ($input == trim(str_replace(array("'", '"'), '', $word))) {
				$r = true;
				//dump('testValueIn ok', $input, $word, $r, trim(str_replace(array("'", '"'), '', $word)));
				break;
			}
		}
		$this->methodFinish($r);
		return $r;
	}


	/**
	 * Fonction de call de type 'test' : Teste que la valeur d'un champs doit ne fasse PAS partie d'une liste de moté clés (séparés par un pipe)
	 * @param unknown_type $input
	 * @param unknown_type $keywords
	 * @return boolean
	 */
	public function testValueNotIn($input, $keywords)
	{
		$this->methodStart();

		$r = !$this->testValueIn($input, $keywords);

		$this->methodFinish($r);
		return $r;
	}

	/**
	 * Fonction de call de type 'test' : Teste que la valeur d'un champs doit ne fasse PAS partie d'une liste de moté clés (séparés par un pipe)
	 * @param unknown_type $input
	 * @param unknown_type $keywords
	 * @return boolean
	 */
	public function testValueNotEmpty($input)
	{
	    $this->methodStart();

	    $r = !empty($input);

	    $this->methodFinish($r);

	    return $r;
	}


	/**
	 * Fonction de call de type 'test' : Teste que la valeur d'un champs doit ne fasse PAS partie d'une liste de moté clés (séparés par un pipe)
	 * @param unknown_type $input
	 * @param unknown_type $keywords
	 * @return boolean
	 */
	public function testValueEmpty($input)
	{
	    $this->methodStart();

	    $r = empty($input);

	    $this->methodFinish($r);

	    return $r;
	}
	/**
	 * Teste la valeur d'un champs
	 * @param string $fied
	 * @param string $input
	 * @return boolean
	 */
	public function test($field, $input)
	{
		$this->methodStart();
		$r = false;

		$this->methodFinish($r);
		return true;
	}



	/********************************************************
	**********    Fonctions de transformations     **********
	*********************************************************/

	/**
	 * @todo fonction qui encode une chaine de type $source en $to (ex : iso to utf8)
	 * @param unknown_type $input
	 * @param unknown_type $source
	 * @param unknown_type $destination
	 */
	public function encode($input, $source, $to)
	{

	}

	/**
	 * @todo fonction de conversion de type (int, bool...)
	 * @param string $input
	 * @param string $type
	 * @return string
	 */
	public function convert($input, $type)
	{
		$this->methodStart();

		$r = $input;

		$this->methodFinish($r);
		return $r;
	}

	/**
	 * Strip whitespace (or other characters) from the beginning and end of a string
	 * @link http://www.php.net/manual/en/function.trim.php
	 * @param string $input
	 * The string that will be trimmed.
	 * @param string[optional] $pos left|right|both (default: both)
	 *
	 * @param string[optional] $charlist
	 * Optionally, the stripped characters can also be specified using
	 * the charlist parameter.
	 * Simply list all characters that you want to be stripped.
	 * With .. you can specify a range of characters.
	 *
	 * @return string The trimmed string.
	 */

	public function trim($input, $pos = 'both', $charlist = null)
	{
		$this->methodStart();
		$r = false;

		if(!is_string($input) || (!is_null($charlist) && !is_string($charlist)) ){
			$this->log("Wrong type ".EaiDebug::getCalledFunction(), 'warn');
		}
		switch ($pos) {
			case 'left':
				$r = ltrim($input, $charlist);
				break;
			case 'right':
				$r = rtrim($input, $charlist);
				break;
			case 'both':
			default:
				$r = trim($input, $charlist);
		}
		$this->methodFinish($r);
		return $r;
	}

	/**
	 * Pad a string to a certain length with another string
	 * @link http://www.php.net/manual/en/function.str-pad.php
	 * @param string $input
	 * The input string.
	 *
	 * @param int $length
	 * If the value is negative,
	 * less than, or equal to the length of the input string, no padding
	 * takes place.
	 *
	 * @param string[optional] $str
	 * may be truncated if the required number of padding
	 * characters can't be evenly divided by the str length.
	 *
	 * @param int[optional] $pos
	 * Optional argument pad_type can be
	 * STR_PAD_RIGHT, STR_PAD_LEFT,
	 * or STR_PAD_BOTH. If
	 * pad_type is not specified it is assumed to be
	 * STR_PAD_RIGHT.
	 *
	 * @return string the padded string.
	 */
	public function pad($input, $length, $str = null, $pos = STR_PAD_RIGHT)
	{
		$this->methodStart();
		$r = str_pad($input, $length, $str, $pos);
		$this->methodFinish($r);
		return $r;
	}


	/**
	 * Replace all occurrences of the search string with the replacement string
	 * @link http://www.php.net/manual/en/function.str-replace.php
	 * @param search mixed <p>
	 * The value being searched for, otherwise known as the needle.
	 * An array may be used to designate multiple needles.
	 * </p>
	 * @param replace mixed <p>
	 * The replacement value that replaces found search
	 * values. An array may be used to designate multiple replacements.
	 * </p>
	 * @param subject mixed <p>
	 * The string or array being searched and replaced on,
	 * otherwise known as the haystack.
	 * </p>
	 * <p>
	 * If subject is an array, then the search and
	 * replace is performed with every entry of
	 * subject, and the return value is an array as
	 * well.
	 * </p>
	 * @param count int[optional] If passed, this will hold the number of matched and replaced needles.
	 * @return mixed This function returns a string or an array with the replaced values.
	 */
	function replace($input, $search, $replace = '')
	{

		$this->methodStart();
		$r = str_replace ($search, $replace, $input);
		$this->methodFinish($r);
		return $r;
	}

	function suppressEOL($input, $pos = 'all')
	{
		$this->methodStart();

		switch ($pos) {
			case 'left':
			case 'right':
				$r = $this->trim($input, $pos, "\r\n");
				break;
			case 'outside':
				$r = $this->trim($input, 'both', "\r\n");
				break;
			case 'inside':
				//break;//TODO
			case 'all':
			default:
				$r = $this->replace($input, array(PHP_EOL, "\r", "\n"));
		}
		$this->methodFinish($r);
		return $r;
	}
	
	function cleanHtml($input, $mode = 'jaymard')
	{
		$r = $input;
		if ($mode == 'jaymard') {
		  $r = strip_tags($input, '<p><ul><li><a><b><strong><i><em><u><br>');
			$r = preg_replace('/<p.*?>/', '<p>', $r);
			$r = preg_replace('/ +/', ' ', $r);
			$r = str_replace("\r\n", "\n", $r);
      $r = html_entity_decode($r, ENT_COMPAT | ENT_XHTML, 'UTF-8');
			$r = trim($r);
		}
		return $r;
	}


	/**
	 * convertit une date au format DD/MM/YYYY (avec éventuellemt l'heure qui suit, séparé d'un espace)
	 * en date (sans l'heure) au format YYYY-MM-DD
	 * @param string $input
	 * @param boolean $now Si True on ignore input et on se base de la date actuelle
	 * @return string
	 */
	public function dateDMY2YMD($input, $now = false, $withTime = false)
	{
		$r = $input;
		if ($now) {
			$r = date("d/M/Y H:i:s");
		}
		$bigparts = explode(' ', $r);//sépare date et time
		$date = reset($bigparts);
		$parts = explode('/', $date);
		if (count($parts) == 3) {
			$r = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
		}
		if ($withTime && isset($bigparts[1])) {
			$r.= " ".$bigparts[1];
		}
		return $r;

	}

	/**
	 * convertit une date au format YYYY-MM-DD (avec éventuellemt l'heure qui suit, séparé d'un espace)
	 * en date (sans l'heure si $withTime) au format DD/MM/YYYY
	 * @param string $input
	 * @param boolean $now Si True on ignore input et on se base de la date actuelle
	 * @return string
	 */
	public function dateYMD2DMY($input, $now = false, $withTime = false)
	{
		$r = $input;
		if ($now) {
			$r = datetime();
		} elseif(strlen($input) == 0) {
            return $r;
        }
		$bigparts = explode(' ', $r);//sépare date et time
		$date = reset($bigparts);
		$parts = explode('-', $date);
		if (count($parts) == 3) {
			$r = "{$parts[2]}/{$parts[1]}/{$parts[0]}";
		}
		if ($withTime && isset($bigparts[1])) {
			$r.= " ".$bigparts[1];
		}

		return $r;

	}


    /**
     * En cas de valeur null, vide ou 0 renvois une constante
     *
     * @param mixed $input
     * @param mixed $value
     */
	public function coalesce($input, $value = 0)
	{
	    $r = $input;
	    if (empty($input) or $input==='0') {
	        $r= $value;
	    }
	    return $r;

	}

	/**
	 * TODO tester surtout avec otherKeys
	 * @param string $inputs
	 * @param string $file
	 * @param string $path
	 * @param string k
	 * @return unknown
	 */
    public function getAssociatedValue($firstKey, $file, $path = '', $otherKeys = null, $out = null)
	{
		$this->methodStart();

		$inputKeys = array($firstKey);
		if(!empty($otherKeys)) {
			$arrKeys = explode(',', $otherKeys);
			foreach ($arrKeys as $key){
				$inputKeys[] = $this->element[trim($key)];
			}
		}

		if(!empty($otherKeys)) {
		    $arrKeys = explode(',', $otherKeys);
		} else {
		    $arrKeys = array($firstKey);
		}


		//TODO: Tester que l'on a deja chargé le fichier de mapping
		if (!$this->hasAssociatedSrc($file, $path)) {
    		$connector = new EaiConnectorFile();
    		$connector->setProp('dir'  , ($path ? $path : Esb::ETC.$this->identifier));
    		$connector->setProp('file' , $file);
    		$connector->setProp('fileWorkflow' , false);

    		if ($connector->connect()) {
    		    $formatter = new EaiFormatterCsv();
    		    $formatter->setProp('headline', 0);
    		    $formatter->connector = $connector;

        		$associatedSrc = array();
    		    if ($formatter) {
    		        while ($formatter->fetchElements()) {
    		            $elements = $formatter->getElements();
    		            if (isset($elements[0]) and is_array($elements[0])) {
        		            $element  = $elements[0];

        		            $tempMapKey= array();
        		            foreach ($inputKeys as $inputKey=>$inputField){
        		                if (isset($element[$inputKey])) {
        		                    $tempMapKey[] = $element[$inputKey];
        		                }
        		            }
        		            $associatedSrc[$this->getAssociatedKey($tempMapKey)]=end($element);
    		            }
    		        }
    		    }
    		    $this->setAssociatedSrc($file, $path, $associatedSrc);

    		} else {
    		    $this->fault('error opening file '.$file.' for getAssociatedValue');
    		}
		} else {
		    $associatedSrc= $this->getAssociatedSrc($file, $path);
		}

		if (isset($associatedSrc[$this->getAssociatedKey($inputKeys)])) {
		    $r= $associatedSrc[$this->getAssociatedKey($inputKeys)];
		    //$this->log('finding match for: '.$this->getAssociatedKey($inputKeys). ' value: '.$r, 'err');
		} else {
		    $this->log("error finding match in $file/$firstKey for : ".$this->getAssociatedKey($inputKeys), 'err');
		    $r= '';
		}

		$this->methodFinish($r);

		return $r;
	}


	protected function hasAssociatedSrc($file, $path = '')
	{
        return isset($this->associatedSrc[$path.$file]);
	}

	protected function setAssociatedSrc($file, $path = '', array $associatedSrc)
	{
	    $this->associatedSrc[$path.$file]= $associatedSrc;
	}

	protected function getAssociatedSrc($file, $path = '')
	{
	    return $this->associatedSrc[$path.$file];
	}

	protected function getAssociatedKey(array $elements)
	{
	    return implode('::', $elements);
	}


	/**
	 *
	 * @param array  $item
	 * @param mixed $value
	 * @return array
	 */
	function arrayCreateHierarchy($item, $value)
	{
	    $result= array();
	    if (!empty($item)) {
	        if (is_array($item)) {
	            $key= current($item);
	            array_shift($item);

	            if (empty($item)) {
	                $result[$key]= $value;
	            } else {
	                $result[$key]= self::arrayCreateHierarchy($item, $value);
	            }
	        } else {
	            $result[$item]= $value;
	        }
	    }
	    return $result;
  }

  /**
   *
   * @param <type> $data
   * @param <type> $path
   * @return <type>
   */
  function readHierarchy($data, $path)
  {
    if (empty($path)) {
      return $data;
    } else {
      $tmp = array_shift($path);
      if (isset($data[$tmp])) {
        return self::readHierarchy($data[$tmp], $path);
      } else return null;
      }
    }

  }
