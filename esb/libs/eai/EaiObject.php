<?php
/**
 *
 * @package eai-generic
 *
 * @author tbondois
 *
 */

abstract class EaiObject
{

	protected $class_suffix = '';

	/**
	 * @var EaiLogger
	 */
	public    $logger;

	/**
	 * @var EaiDbRow
	 */
	public    $dbRow;

	/**
	 * Liste des évenements
	 *
	 * @var array
	 */
	private   $events;

	/**
	 * Contexte de la méthode
	 *
	 * @var array
	 */
	private   $method_context;

	/**
	 * Contexte de la classe
	 *
	 * @var array
	 */
	private   $class_context;

	/**
	 * Clé du fichier de configuration
	 */
	protected $config_key = false;

	/**
	 * Chemin de base du fichier de configuration
	 */
	protected $config_base_path = '';


	public function __construct()
	{
		$this->class_context = array();
		$this->logger = new EaiLogger($this->getClass());
		$this->dbRow = ESB::registry('dbRow');
	}

	public function __destruct()
	{

	}

	/**
	 * Be careful : When an object is cloned, PHP 5 will perform a shallow copy of all of the object's properties.
	 * Any properties that are references to other variables, will remain references.
	 */
	public function __clone()
	{

	}

	public function __toString()
	{

		$r = (string)print_r($this, true);
		$maxLen = 25000;
		if(strlen($r) > $maxLen) {
			$r = substr($r, 0, $maxLen)."... [troncated at $maxLen chars]";
		}
		return $r;
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $args)
	{

		$prefixMethod= substr($method, 0 ,3);
		switch ($prefixMethod) {
    	case 'get' :
				$property = lcfirst(substr($method, 3));
				$r = $this->getProp($property);//a tester, voir si ca prend les get de class fille ou de cette classe
				break;
			case 'set' :
				$property = lcfirst(substr($method, 3));
				$r = $this->setProp($property, isset($args[0]) ? $args[0] : null);
				break;
			case 'uns' :
				$property = lcfirst(substr($method, 3));
				$r = $this->unsProp($property, isset($args[0]) ? $args[0] : null);
				break;
			case 'raz' :
				$property = lcfirst(substr($method, 3));
				$r = $this->razProp($property, isset($args[0]) ? $args[0] : null);
				break;
			default:
				$r = null;
				$this->fault("Method not accessible ".$this->getClass()."->$method(". implode(', ', $args).")");
				break;
		}
		return $r;
	}

	/**
	 * Since PHP 5.3.0
	 * @param string $name
	 * @param array $arguments
	 */
	public static function __callStatic($method, $args)
	{
		static::methodStart();
		$r = null;

		static::log("Static method not accessible ".self::getClass()."::$method(". implode(', ', $args).")", 'fatal');
		static::methodFinish();
	}

	public function call($method, $args = array())
	{
		$this->methodStart();
		$r = null;
		if($this->hasMethod($method)){
			$r = call_user_func_array(array($this, $method), $args);
		}
		$this->methodFinish($r);
		return $r;
	}

	/**
	 *
	 * @param string $class
	 * @param array $properties
	 * @return EaiLogged
	 */
	public static function factory($identifier, $properties = null, $way = null)
	{
		$instance = null;
		$class = static::getClass().ucfirst($identifier);


		if ($identifier && class_exists($class)) {
			$reflect = new ReflectionClass($class);
			if ($reflect->isInstantiable()) {
				$instance = $reflect->newInstance();
				$instance->class_suffix= strtolower($identifier);
				if(!is_subclass_of($class, static::getClass())){
					$instance = null;
					static::log("$class not subclass of ".static::getClass().", but subclass of ".get_parent_class($class) , 'err', $class);
				}
			} else static::log("Not instanciable $class from '$identifier'", 'warn', $class);
		} else static::log("Class not exists : $class from '$identifier'", 'warn', static::getClass());

		//On rajoute $way a la liste des propriété a implementer
		if(!is_null($way)) {
			if(is_array($properties)) {
				$properties['way'] = $way;
			} else {
				$properties = array('way' => $way);
			}
		}
		if (is_array($properties) && !empty($properties)) {
			foreach ($properties as $var => $val) {
				//dump("$class", $identifier);//Pb d'autoloading si ca coince ici
				if ($reflect->hasProperty($var)) {

					if ($var == "plugins" && isset($val['plugin'])) {
							$plugins = Esb::collection($val['plugin']);

							$newPlugins = array();
							foreach($plugins as $key => $plugin){
								if (isset($plugin['@attributes']['type']) && strlen($plugin['@attributes']['type']) > 0) {
									$type = $plugin['@attributes']['type'];
									$obs = 'EaiPlugin'.ucfirst($type);
									Esb::registerObserver($obs);
									$instance->log("registering plugin observer '$obs'", 'debug');
								}	else {
									$type = $key;
								}
								unset($plugin['@attributes']);
								$newPlugins[$type] = $plugin;
							}//foreach
							$val = $newPlugins;

					} elseif (is_array($val) && empty($val)) {
						$val = null;//le xml du fichier de conf donne par exemple <node/> = array(0)
					} elseif(is_array($val) && isset($val[0]) && isset($val[1])) {
                        $val = end($val);//configuration surchargée, on prends la dernier ligne
                    }

					$instance->$var = $val;

				} elseif($var != 'comment') $instance->log("Property $var ($val) defined in configuration is not a member for class $class, skipped", 'err');
			}
		}
		//dump($instance);
		return $instance;
	}

	protected function dispatchEvent($name, $args = array())
	{
		$r = null;
		$observers = Esb::registry('observers');

		if (Esb::registry('eventHelper')) {
			//Si ?eventHelper=1 en paramètre, on affiche tous les evenements générérés
			$this->log('[eventHelper] Registering event : '.$name.'() - Memory used : ' . number_format(memory_get_usage(), 0, ',', ' ') . ' Bytes');
			//dump($name, $args);
		}
		if ($observers) {

	    if (empty($this->events[$name])) {
   			$event = new EaiEvent();
   			foreach ($args as $argKey => $argVal) {
   			    $event->setArg($argKey, $argVal);
   			}
   			if (isset($this)) {
   			    $event->setObj($this);
                $event->setObjClass($this->getClass());
   			}
   			$this->events[$name]= $event;
	    } else {
	        $event = $this->events[$name];
	    }

	    foreach ($observers as $class) {
				$reflect = new ReflectionClass($class);
				if ($reflect->hasMethod($name)) {
					if (isset($this)) {
						$this->log("dispatchEvent('$name') triggers $class.$name()", 'debug');
					}
					$r = call_user_func_array(array($class, $name), array($event));
					//$r=call_user_func_array(array($class, $name), $args);
				}
			}
		}
		return $r;
	}


    protected function methodStart()
    {
        $args = func_get_args();
        $r = false;
        if (isset($this)) {
            $this->method_context = array();

            $r = $this->methodTrace('start', $args);
        }
        return $r;
    }

	protected function methodFinish()
	{
		$args = func_get_args();
        $r = false;
        if (isset($this)) {
		    $r = $this->methodTrace('finish', $args);
        }
		return $r;
	}

    protected function methodTrace($stage, array $args)
    {

        if (!is_array($args)) {
            $args = array($args);
        }

        $r = null;
        $traces = debug_backtrace();

        $last = $traces[2];
        $class = $this->getClass();
        $method = $last['function'];
        //$eventName =  $stage.'_'.$this->getClass($this).'_'.str_replace('_', '', $last['function']);//onStart_EaiConnectorSystem_connect

        $this->log("method $class.$method() $stage - Memory used : " . number_format(memory_get_usage(), 0, ',', ' ') . ' Bytes', 'trace');

        //1. Préfixe "on"
        $eventName   =  'on';

        //2. Nom de la methode (ou ClasseMethode pour les fonctions magiques)

        if (strpos($method, '__') === 0) {
            //Pour les Fonctions Magiques : le nom de la fonction seul permet pas de retrouver, on préfixe avec la classe
            $eventName.= ucfirst($class);
        }
        $eventName  .= ucfirst(trim($method, '_'));

        //3. In ou Out (si défini en tant que membre de classe)
        if (isset($this->way)) {
            $eventName .= ucfirst($this->way);
            $args['calledWay'] = $this->way;
        } else {
            $args['calledWay'] = null;
        }

        //4. Start/Finish
        $eventName.= ucfirst($stage);

        //dump($eventName, $this->getClass());

        $observers = Esb::registry('observers');

        if (isset($observers) && !in_array($method, array('registerObservers', 'dispatchEvent'))) {
	          //TODO tbondois dégager le if ici + trace d'eventHelper, redondant avec le code dans dispatchEvent
            $args['calledClass'] = $class;
            $args['calledMethod'] = $method;
            $r = static::dispatchEvent($eventName, $args);
        }

        if (Esb::registry('eventHelper')) {
            //Si ?eventHelper=1 en paramètre, on affiche tous les evenements générérés
            $this->log("[eventHelper] Registering event: $eventName()", 'info');
        }

        return $r;
    }



	/**
	 * Getter - uniquement sur les propriétés déclarées
	 * @param string $property
	 * @return mixed
	 */
	public function getProp($property)
	{
        //echo EaiDebug::getFunctionsTrace();//en cas de bug
		if ($this->hasProperty($property)) {
            if(is_string($this->$property)) {
                switch ($this->$property) {
                    case 'C_TAB'    : $r = "\t"  ; break;
                    case 'C_SPACE'  : $r = " "   ; break;
                    case 'C_QUOTE'  : $r = '"'   ; break;
                    case 'C_APOST'  : $r = "'"   ; break;
                    case 'C_NULL'   : $r = ""    ; break;
                    case 'C_EMPTY'  : $r = ""    ; break;
                    case 'C_CR'     : $r = "\r"  ; break; //Carriage Return seul
                    case 'C_LF'     : $r = "\n"  ; break; //Line Feed seul : format unix
                    case 'C_EOL'    : $r = "\r\n"; break; //CR+LF : format Windows
                    case 'C_DOT'    : $r = "."   ; break;
                    case 'C_COMMA'  : $r = ","   ; break;
                    case 'C_SEMICOL': $r = ";"   ; break;
                    default :
                        $r = $this->$property;
                }
            } else {
                $r = $this->$property;
            }
		} else {
    		$r = null;
		}
		return $r;
	}

	/**
	 * Setter - uniquement sur les propriétés déclarées
	 * @param string $property
	 * @param mixed $value
	 * @return bool
	 */
	public function setProp($property, $value)
	{

		if ($this->hasProperty($property, 'set')) {
			$this->$property = $value;
			$r = true;
		} else  {
			$r = false;
		}
		return $r;
	}

	/**
	 * Unsetter - uniquement sur les propriétés déclarées
	 * @param string $property
	 * @param mixed $value
	 * @return bool
	 */
	public function unsProp($property)
	{
		if ($this->hasProperty($property, 'uns')) {
			$this->$property = null;
			$r = true;
		} else {
			$r = false;
		}
		return $r;
	}

	/**
	 * Retourne le nom de la classe par laquelle on appelle la méthode (dernier niveau)
	 * @return string
	 */
	public static function getClass()
	{
		if (isset($this)) {
			return get_class($this);//ne retournera pas la classe ou est défini la fonction, mais la classe d'appel
		} else {
			return get_called_class();//appel statique
		}
	}

	/**
	 * Checks if property is defined
	 * @link http://www.php.net/manual/en/reflectionclass.hasproperty.php
	 * @param name string <p>
	 * Name of the property being checked for.
	 * </p>
	 * @return bool true if it has the property, otherwise false
	 */
	public function hasProperty($name)
	{
		$reflect = new ReflectionClass($this->getClass());
		$r = $reflect->hasProperty($name);

		if (!$r) {
		 $this->log("property '$name' not declared ".EaiDebug::getFunctionsTrace(), 'debug');
		}
		return $r;
	}

  /**
	 * Checks if method is defined
	 * @link http://www.php.net/manual/en/reflectionclass.hasmethod.php
	 * @param name string <p>
	 * Name of the method being checked for.
	 * </p>
	 * @return bool true if it has the method, otherwise false
	 */
	public function hasMethod($name)
	{
// 		$this->methodStart();
		$reflect = new ReflectionClass($this->getClass());
		$r = $reflect->hasMethod($name);
// 		$this->methodFinish($r);
		return $r;
	}

  /**
   *
   * @param string $message
   * @param string $level
   *                'trace'
   *                'info'
   *                'warn'
   *                'err'
   *                'fatal'
   */
  public static function log($message, $level = EaiLogger::INFO)
  {

    // TODO : rendre le niveau d'affichage des logs parametrable proprement
    // pour le moment c'est fait via un paramtre en GET

//     if (isset($_GET['logLevel'])) {
//       // on evite d'afficher les logs purement informatif
//       if ($_GET['logLevel'] == 1) {
//         if (in_array($level, array('info', 'debug', 'trace'))) return;
//       }
//     }

    if (!isset($this) || !is_object($this)) {
     // dump($message);
    } else {
      if (!is_object($this->logger)) {
        echo EaiDebug::getFunctionsTrace();
      }
      if (isset($this->way)) {
        $message = "[{$this->way}] $message";
      }
      $this->logger->log($message, $level);

      switch (trim(strtolower($level))) {
        //case EaiLogger::INFO: // @TODO : A SUPPRIMER !!!
        case EaiLogger::WARNING:
        case 'warning':
          $this->dbRow->increment('warning');
          break;
        case EaiLogger::ERROR:
        case 'error':
          $this->dbRow->increment('error');
          break;
        case EaiLogger::FATAL:
        case 'fatal':
          $this->dbRow->increment('fatal');
          break;
      }

    }
	}

	/**
	 * Determination des cas de sortie
	 *
	 * @param string $message
	 * @param int    $fault
	 */
	protected function fault($message, $fault=0)
	{
		$msgContext= $this->getClassContextMsg();
		if (!empty($msgContext)) {
            $this->logger->log('CLASS CONTEXT BEFORE FAULT: '.$msgContext, 'fatal');
		}
		$msgContext = $this->getMethodContextMsg();
		if (!empty($msgContext)) {
			$this->logger->log('METHOD CONTEXT BEFORE FAULT: '.$msgContext, 'fatal');
		}
		$this->log("Fault: ".$message, 'fatal');
		$this->log('Stopping Esb', 'fatal');
		Esb::stop(true);
	}



	/**
	 * Positionnement de variable de contexte
	 * pour utilisation dans la classe de plus bas niveau dans l'affichage des logs
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function setMethodContext($key, $value)
	{
		$this->method_context[$key]= $value;
	}


	/**
	 * Positionnement de variable de contexte
	 * pour utilisation dans la classe de plus bas niveau dans l'affichage des logs
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function setClassContext($key, $value)
	{
		$this->class_context[$key]= $value;
	}


	/**
	 * Positionnement de variable de contexte
	 * pour utilisation dans la classe de plus bas niveau dans l'affichage des logs
	 *
	 * @param string $key
	 * @param string $value
	 */
	private function getMethodContextMsg()
	{
		$msg = '';
		if (!empty($this->method_context)) {
			$msgArr = array();
			foreach ($this->method_context as $ctxtKey=>$ctxVal) {
				$msgArr[]= $ctxtKey.':"'.$ctxVal.'"';
			}
			$msg= implode('; ',$msgArr);
		}
		return $msg;
	}

	/**
	 * Positionnement de variable de contexte
	 * pour utilisation dans la classe de plus bas niveau dans l'affichage des logs
	 *
	 * @param string $key
	 * @param string $value
	 */
	private function getClassContextMsg()
	{
		$msg = '';
		if (!empty($this->class_context)) {
			$msgArr= array();
			foreach ($this->class_context as $ctxtKey=>$ctxVal) {
				$msgArr[]= $ctxtKey.':"'.$ctxVal.'"';
			}
			$msg= implode('; ',$msgArr);
		}
		return $msg;
	}

	/**
	 * Chargement de la configuration par fichier de configuration ou tableau de donnée
	 *
	 * @param mixed $configType
	 */
	public function config($configType = false)
	{
        $this->methodStart();

        if (empty($configType) and $this->config_key) {
            $configType = $this->config_key;
        }
        if (is_null($this->config)) {
            if (!empty($configType)) {
                $this->config = new EaiConfiguration($this->identifier, $configType, $this->config_base_path);
            } else {
                $this->fault('config_key is misdefined');
            }
        }
        $this->methodFinish();
        return $this->config;
	}

	/**
	 * Detecte et remplace les variables de fusions dans une chaine
	 * (utilisé via array_walk_recursive)
	 * array_walk_recursive ne change rien si le remplacement est faux (dont 0 ou chaine vide incluse dans la valeur du xml)
	 *
	 * @param string $data
	 * @param string $key
	 * @param int    $checkfrom
	 *                 0x01: define
	 *                 0x02: Esb::config
	 *                 0x04: class property
	 *                 0x08: php eval
	 */
	protected function parseBrackets(&$data, $key = null, $checkfrom = 0x07)
	{

// 	    dump($data);
		if (is_string($data)) {
            $replace = array();


            $bracketsFound = preg_match_all('/{(.*?)}/', $data, $matches);
            if ($bracketsFound) {
                $nb=0;
                foreach ($matches[1] as $index => $match) {
                    $nb++;
                    // Très artistique ;-)
                    // 					ob_start();
                    // 					eval("echo ". $match.";");
                    // 					$replace[$matches[0][$index]] = ob_get_clean();
                    $from = $matches[0][$index];
                    if( $checkfrom & 0x01 and defined($match) ) {
                        $replace[$from] = constant($match);
                    } elseif( $checkfrom & 0x02 and Esb::config()->getValue($match)) {
                        $replace[$from] = Esb::config()->getValue($match);
                    } elseif( $checkfrom & 0x04 and isset($this) and $this->hasProperty($match)) {
                        $replace[$from] = $this->getProp($match);
                    } elseif(Esb::phpEval($match, true)) {
                        $replace[$from] = Esb::phpEval($match);//laisser $replace[$from]=, sinon ca remplace la chaine entière
                    }
                    //if($nb>0) break;
                }

//                 return $data;

                if (is_string($data)) {
                  $data = str_replace(array_keys($replace), $replace, $data);
                }
            }
    } elseif(is_null($data)) {
        $data = '';
    }
    //return $data;
    return true;
	}
}
