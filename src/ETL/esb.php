<?php
/**
 * Exemple lancement interface en mode debug :
 * http://localhost/home/dev_esb/gign_launch.php?code=partners/colombus/images&dump=1&eventHelper=1
 *
 * @version 0.2 alpha
 */

/* set environment and application paths : */
require_once "settings.php";

/* Autoloader pour les librairies Zend */
require_once Esb::LIBS . 'ext' . DIRECTORY_SEPARATOR . 'Zend' . DIRECTORY_SEPARATOR . 'Loader' . DIRECTORY_SEPARATOR . 'StandardAutoloader.php';
use Zend\Loader\StandardAutoloader;

/* Register ESB autoloader : */
Esb::autoload();


/**
 * Classe "point d'entrée' à inclure dans la page pour lancer une interface
 * @example include_once './esb.php';
 * @example Esb::start('identifier');
 *
 * @abstract cette classe n'a pas a etre instanciée (appels statiques)
 *
 * @package eai-generic
 *
 * @author tbondois
 * @version 0.01
 */
abstract class Esb
{
  /* constants loaded from settings : */

  /** env indiquera ou chercher le fichier de configurations spécifiques à l'environnement dans core/base/env/*.xml
   * @var string
   */
  const ENV  = ENV;

  const LOGGING_BASE = LOGGING_BASE;
  /**
   * Contrôle si la  fonction kill execute un exit
   * @var int
   */
  const KILL_ALLOWED = KILL_ALLOWED;
	const ROOT = DIR_ROOT;
	const ETC  = DIR_ETC ;
	const BASE = DIR_BASE;
	const CDM  = DIR_CDM ;
	const LIBS = DIR_LIBS;
	const WORKBASE = DIR_WORKBASE;

	const WAY_IN  = 'in' ;
	const WAY_OUT = 'out';

	/**
	 * Registry collection
	 * @var array
	 */
	protected static  $registry   = array();

	/**
	 * @var array
	 */
  protected static  $handlers   = array();


	/**
	 * Temporaire
	 * ce fichier de config devra récupérer ses informations en BDD
	 * @var EaiConfiguration
	 */
	public static $config;


  protected static $is_cli = null;

  /**
   * @var string $timeZone
   */
  protected static $timezone = 'Europe/Paris';



	/**
	 * Temporaire ...
	 * Cette méthode fera appel à un séquenceur pour lancer plusieurs interfaces
	 *
	 * @param string $identifier
	 */
	public static function start($identifier, $inlineConfigInterface = null)
	{
        $r = false;

        $identifier = trim(trim($identifier), '/');//on enlève les /, espaces et caractères parasites en début ou fin de chaine

        if (!isset(static::$handlers[$identifier])) {

            define('IDENTIFIER', $identifier);
            static::$registry = array();//précaution
            static::register('identifier', $identifier);
            static::register('config:interface', $inlineConfigInterface);
            static::register('eventHelper', (bool)static::GET('eventHelper'));
            static::register('dbRow', new EaiDbRow($identifier));

            static::$handlers[$identifier] = new EaiHandler($identifier);
            $r = static::$handlers[$identifier]->run();
        } else dump("Handler $identifier exist");

        return $r;
	}

	public static function stop($error= false)
	{
		if (!empty(static::$handlers)) {
			foreach (static::$handlers as $handler) {
				$handler->close($error); //TODO gérer un identifier ou supprimer le paramètre
			}
		}
		exit ("stop"); // ne pas changer ce "strop": il utilisée pour détecter un stop dans des exec en ligne de commande
	}


	/**
	 * Register a new variable
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param bool $graceful
	 * @throws Mage_Core_Exception
	 */
	public static function register($key, $value, $graceful = false)
	{
	    if (isset(static::$registry[$key])) {
	        if ($graceful) {
	            return;
	        }
	        throw new Exception('Esb registry key "'.$key.'" already exists '.EaiDebug::getFunctionsTrace());
	    }
	    static::$registry[$key] = $value;
	}

	/**
	 * Unregister a variable from register by key
	 *
	 * @param string $key
	 */
	public static function unregister($key)
	{
	    if (isset(static::$registry[$key])) {
	        if (is_object(static::$registry[$key]) && (method_exists(static::$registry[$key], '__destruct'))) {
	            static::$registry[$key]->__destruct();
	        }
	        unset(static::$registry[$key]);
	    }
	}

	/**
	 * Retrieve a value from registry by a key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function registry($key)
	{
	    if (isset(static::$registry[$key])) {
	        return static::$registry[$key];
	    }
	    return null;
	}

	/**
	 * Register a new variable
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param bool $graceful
	 * @throws Mage_Core_Exception
	 */
	public static function registerObserver($key, $value=false, $graceful = false)
	{
	    if (!isset(static::$registry['observers'])) {
	        static::$registry['observers'] = array();
	    }

      if (empty($value)) {
       $value= $key;
      }
			//echo("* registerObserver: $key => $value");
	    static::$registry['observers'][$key] = $value;
	}

	/**
	 *  Enregistrement autoloader du projet avec spl
	 */
	public static function autoload()
	{
		// Chargement de l'autoloader Eai
		if (!spl_autoload_register('static::autoloader')) {
			exit("Erreur enregistrement autoloader ESB");
		}

		// Chargement de l'autoloader Log4php.
		$name = 'ext' . DIRECTORY_SEPARATOR . 'log4php' . DIRECTORY_SEPARATOR . 'Logger';
		$loaded = static::loadLib($name, true);

		// Chargement de l'autoloader Zend.
		$zendLoader = new StandardAutoloader(array('autoregister_zf' => true));
		$zendLoader->register();
	}

	/**
	 * Permet de gérer l'autoload des librairies internes et externes
	 * @param string $class
	 * @return bool
	 */
	public static function autoloader($class)
	{
		$loaded = false;
		$name   = false;
		$words  = preg_split('/(?=[A-Z])/', $class, 3, PREG_SPLIT_NO_EMPTY);//split sur chaque majuscule, 3 FOIS MAXIMUM (3 2 sous-dossiers maximum)

		$prefix = strtolower($words[0]);
		switch ($prefix) {
			case 'eai':
			case 'observer':
				$dir = '';
				unset($words[count($words)-1]);
				foreach ($words as $word){
					$dir.= strtolower($word) . DIRECTORY_SEPARATOR; //each capital letter means a subdirectory
				}
				$name = $dir.$class;
				$loaded = static::loadLib($name, true);
			default:
				switch ($class) {
					case 'Array2XML':
					case 'XML2Array':
					    $name= "ext/xml/".$class;
						$loaded = static::loadLib($name, true);
						break;
                    case 'SFTPConnection':
                        $name = "ext/ssh/sftp";
                        $loaded = static::loadLib($name, true);
                        break;
// 				    default:
// 				        $name= "Unknown lib $class";
// 				        break;
				}
		}

		if ($name && !$loaded) {
			EaiObject::log(__METHOD__." ESB : Unable to load $name.php", 'fatal');
		}

		return $loaded;
	}

	/**
	 * Inclut une librairie php
	 * (a partir du dossier libs/, avec ou sans .php à la fin)
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function loadLib($path, $testExist = false)
	{
		$fullPath = Esb::LIBS . $path . (strrpos($path, ".php") ? '' : ".php");
		if(!$testExist || file_exists($fullPath)){
			//echo(PHP_EOL."<br/>* $fullPath</br>");
			include_once $fullPath;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Chargement de la configuration par fichier de configuration ou tableau de donnée
	 *
	 * @param mixed $config
	 */
	static function config($inputConfig = null)
	{
        if (is_null(static::$config) ) {
          static::$config = new EaiConfiguration('', 'env/'.static::ENV);
        }

        return static::$config;
	}


	/**
	 * @TODO enlever le mode test qui change le type de retour
	 * Liste des fonctions prise en compte défini en dur pour des raisons de sécurité
	 *
	 * @param string $value
	 * @param boolean $test
	 * @throws Exception
	 * @return string|boolean
	 */
    static function phpEval($value, $test = false)
    {
    $return = false;

    if (preg_match('/^php::([^()\n]+)(?:\((.*)\))?$/', $value, $match)) {

      if ($test) {
        $return = true;
      } else {
        switch ($match[1]) {
          case 'date':
                if($match[2]){
                    $return = date($match[2]);
                } else {
                    $return = date("Y-m-d H:i:s");
                }
                break;
          case 'array' :
            $return = array();
            break;
          case 'getopt' :
            //get the shell value for option -f
            return getopt("f:");
            break;
          case 'counter' :
            if ($match[2] !== '') {
              $return = call_user_func_array('Esb::counter', explode(',', $match[2]));
            }
            else {
              $return = static::counter();
            }
            break;
          case 'counterIncrement' :
            if ($match[2] !== '') {
              $return = call_user_func_array('Esb::counterIncrement', explode(',', $match[2]));
            }
            else {
              $return = static::counterIncrement();
            }
            break;
          default:
            dump($match);
            throw new Exception('phpEval valeur non autorisé "'.$value);
        }
      }
    } else {
        return false;
    }
    return $return;
    }

	/**
	 * Retourne un chemin qui se termine systématiquement par un séparateur
	 *
	 * @param string $path
	 * @return bool
	 */
	static function fullPath($path)
	{
		return rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	}

    static function checkDir($path, $throwException = true)
    {
        $r = false;

        $isInIdentifierPath= false;

        $interfaceDir = Esb::WORKBASE.Esb::registry('identifier');
        if ($isInIdentifierPath and strpos($path, $interfaceDir) !== 0) {
            //Le chemin doit etre dans le dossier var du projet :
            //$path = Esb::WORKBASE.$path;
            if ($throwException) {
                throw new Exception("Not a valid starting path : '$path' (must start by '$interfaceDir'')");
            }
        }

        if (!file_exists($path)) {
            if (mkdir($path , 0777, true)) {
                $r = true;
            } else {
                if ($throwException) throw new Exception("Unable to create dir:".$path);
            }
        } else {
            $r = true;
        }
        return $r;
    }

	public static function validateOverXsd($xmlString, $xsdPath)
	{
		$r = false;

		dump(basename($xsdPath));

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();

		$dom->loadXML($xmlString);
		if ($dom->schemaValidate($xsdPath)) {
			dump("Ok");
			$r = true;
		} else 	{
			//see http://www.php.net/manual/fr/domdocument.schemavalidate.php#62032
			$errors = libxml_get_errors();
			foreach ($errors as $error) {
				dump("Error (level:".$error->level.") ".trim($error->message), 'err');
			}
			libxml_clear_errors();
		}

		libxml_use_internal_errors(false);
		return $r;
	}

	public static function helper_getMapping($cdmName, $type = 'import')
	{
		$r = '';
		$cdm = new EaiCdm($cdmName);
		$mapping = array();

		if ($type == 'import') {
			$way = Esb::WAY_IN;
		} else {
			$way = Esb::WAY_OUT;
		}
		$mapping[$way]['fields'] = array();

		foreach ($cdm->getSchemaElements() as $name => $props){
			$mapping[$way]['fields']['field'][] = array('from' => $name, 'to' => $name);

			if (0 && isset($props['type'])) { //non activé
				$mapping[$way]['fields']['field']['calls']['call'] = array(
												'@attributes' => array('type' => 'test'
																						, 'name'=> 'testType'),
													'type' => array($props['type'])
						);
			}
		}
		$r.= EaiFormatterXml::xmlFromArray($mapping, 'mapping');
		$r = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.str_replace('<to>', '<to  >', $r);

		return $r;
	}


	/**
	 * todo gérer les options de ligne de commande
	 * @param string $name
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public static function GET($name, $defaultValue = null)
	{
		if (isset($_GET[$name])) {
			return $_GET[$name];
		} else {
			return $defaultValue;
		}
	}

	/**
	 * Méthode permettant d'identifier que l'on est en mode ligne de commande
	 */
	public static function isCli()
	{
	    if (is_null(static::$is_cli)) {
	        static::$is_cli= (PHP_SAPI === 'cli');
	    }

	    return static::$is_cli;
	}

	/**
	 * Hack permettant de faire un sous-tableau dans le cas d'un element xml seul alors qu'une collection est attendue
	 *
	 * @param unknown_type $data
	 * @return multitype:multitype:
	 */
	public static function collection($data)
	{
		if (!is_array($data) || !isset($data[0])) {
			$data = array($data);
		}
		return $data;
	}




	public static function object_to_array($obj) {
		if(is_object($obj))
			$obj = (array) $obj;
		if(is_array($obj)) {
			$new = array();
			foreach($obj as $key => $val) {
				$new[$key] = self::object_to_array($val);
			}
		}
		else $new = $obj;
		return $new;
	}


    static public function importFtp($workDir, $routes = array(), $ftpParams = array(), $unzip = false, $unzipParams = null)
    {
        $dispatchs = array("success" => array()
                         , "ignored" => array()
                         , "fail" => array()
        );

        $ftpBase = array('server'            => null
                       , 'user'              => null
                       , 'password'          => null
                       , 'remoteDir'         => ''
                       , 'searchFile'        => ""   //indique par quoi les fichiers cible doivent commencer  (non sensible à la casse)
                       , 'port'              => 21
                       , 'passive'           => 1
                       , 'transfertMode'     => FTP_BINARY
                       , 'override'          => 0   //TODO mode 2 qui renomme a coté (pas utile ici)
                       , 'deleteSourceAfter' => 0   //Attention 1 seulement en environnement de prod
                       , 'localDir'          => $workDir.'ftp/'
                       , 'remotelist'        => array()
                       , 'success'           => array()
                       , 'ignored'           => array()
                       , 'fail'              => array()
                       , 'connection'        => null
        );
        $unzipBase = array('extension' => ".zip"
                         , 'success'   => array()
                         , 'ignored'   => array()
                         , 'fail'      => array()
        );

        if (is_array($ftpParams)) {
            $ftpParams = array_merge($ftpBase, $ftpParams);
        }
        if ($unzip) {
            if (is_array($unzipParams)) {
                $unzipParams = array_merge($unzipBase, $unzipParams);
            } else if($unzip) {
                $unzipParams = $unzipBase;
            }
        }

        $ftpProcessedDir = $ftpParams['localDir']."processed/";
        if (!file_exists($ftpProcessedDir)) {
            mkdir($ftpProcessedDir, 0777, true);
        }

        if (strlen($ftpParams['server']  ) > 0
            && strlen($ftpParams['user']    ) > 0
            && strlen($ftpParams['password']) > 0
        ) {
            $localDirExist = false;
            if (file_exists($ftpParams['localDir'])) {
                $localDirExist = true;
            } else {
                if (mkdir($ftpParams['localDir'], 0777, true)) {
                    $localDirExist = true;
                    dump('Creating local directory recursively '.$ftpParams['localDir']);
                } else {
                    dump("Bad local mkdir '{$ftpParams['localDir']}'", 'err');
                }
            }
            if ($localDirExist) {

                $ftpParams['connection'] = ftp_connect($ftpParams['server'], $ftpParams['port']);
                if ($ftpParams['connection']) {
                    if (ftp_login($ftpParams['connection'], $ftpParams['user'], $ftpParams['password'])) {
                        if (ftp_pasv($ftpParams['connection'], (bool)$ftpParams['passive'])) {
                            if (strlen($ftpParams['remoteDir']) == 0
                                || ftp_chdir($ftpParams['connection'], $ftpParams['remoteDir'])
                            ) {
                                $ftpParams['remotelist'] = ftp_nlist($ftpParams['connection'], '.');
                                foreach($ftpParams['remotelist'] as $num => $remoteFile) {
                                    $localFile  = $ftpParams['localDir'].$remoteFile;

                                    if ( ($ftpParams['override'] || !file_exists($localFile))
                                        && stripos($remoteFile, $ftpParams['searchFile']) === 0
                                        && strripos($remoteFile, $unzipParams['extension']) == strlen($remoteFile)-strlen($unzipParams['extension'])
                                    ) {

                                        if (ftp_get($ftpParams['connection'], $localFile, $remoteFile, $ftpParams['transfertMode']) ) {

                                            $ftpParams['success'][$num]   = $localFile;
                                            //dump("successfuly FTP downloaded file $remoteFile to $localFile");

                                            if($ftpParams['deleteSourceAfter'] /*&& Esb::ENV == 'prod'*/) {
                                                $deleting = ftp_delete($ftpParams['connection'], $remoteFile);
                                                if($deleting) {
                                                    dump("ftp_delete ok for $remoteFile");
                                                } else {
                                                    dump("erreur ftp_delete $remoteFile");
                                                }
                                            }
                                        } else {
                                            $ftpParams['fail'][$num]   = $remoteFile;
                                            dump("connecting : bad ftp_get(connection, '$localFile' << $remoteFile')", 'err');
                                        }
                                    } else {
                                        $ftpParams['ignored'][$num]   = $remoteFile;
                                        /*dump("checking : local file $localFile already exist (mode override = ".(int)$ftpParams['override'].") or invalid filename / extension. , next"
                                        , $remoteFile
                                        , ($ftp['override'] || !file_exists($localFile))
                                        , stripos($remoteFile, $ftp['searchFile'])
                                        , strripos($remoteFile, $unzip['extension']) == strlen($remoteFile)-strlen($unzip['extension'])
                                        );*/
                                    }
                                }//foreach $remoteFile

                            } else dump("connecting : bad ftp_chdir(connection, '{$ftpParams['remoteDir']}')", 'fatal');
                        } else dump("connecting : bad ftp_pasv(connection, '{$ftpParams['passive']}')", 'fatal');
                    } else dump("connecting : bad ftp_login(connection, '{$ftpParams['user']}', password)", 'fatal');

                    ftp_close($ftpParams['connection']);

                } else  dump("connecting : bad ftp_connect({$ftpParams['server']}, '{$ftpParams['port']}')", 'fatal');
            }
        }

        //dump("ftp status:", $ftpParams);



        //traiter tous les fichiers dans to_deal, pas seulement la liste du ftp  intelligement
        // car si un vieux fichier openbar est retraité, on écrase tout le stock
        //faire la décompression et le traitement dans la meme boucle

        $localDirExist = false;

        if(!$unzip) {
            $unzipParams['success'] = $ftpParams['success'];
        } else {
            if (!empty($ftpParams['success'])) {
                if (file_exists($workDir)) {
                    $localDirExist = true;
                } else {
                    if (mkdir($workDir, 0777, true)) {
                        $localDirExist = true;
                        dump('Creating local directory recursively '.$workDir);
                    } else {
                        dump("Bad local mkdir '{$workDir}'", 'err');
                    }
                }

                if ( $localDirExist) {

                    foreach ($ftpParams['success'] as $num => $filePath) {

                        $file = end(explode('/', $filePath)); //nom de fichier sans les dossiers

                        if (strripos($filePath, $unzipParams['extension']) == strlen($filePath)-strlen($unzipParams['extension'])) {
                            $unzipFiles = EaiPluginUnzip::unzip($filePath, $workDir, false);

                            if (empty($unzipFiles)) {
                                $unzipParams['fail'][$num] = $filePath;
                            } else {
                                $unzipParams['success'] = array_merge($unzipParams['success'], $unzipFiles);

                                if(!file_exists($ftpProcessedDir.$file)) {
                                    chmod($filePath, 0666);
                                    $renamed = rename($filePath, $ftpProcessedDir.$file);
                                    if (!$renamed) {
                                        dump("erreur rename ftp->unzip ($filePath, {$ftpProcessedDir}$file)");
                                    }
                                } else {
                                    if(!$ftpParams['deleteSourceAfter']){
                                        //on supprime pas la source ftp à chaque fois, donc normal que le fichier existe déja dans les processed
                                        unlink($filePath);
                                        //dump("unlink($filePath) : on supprime pas la source ftp à chaque fois, donc normal que le fichier existe déja dans les processed");
                                    } else {    //on renomme pour garder une copie
                                        dump("warning : already exist $ftpProcessedDir.$file, rename ".$ftpProcessedDir.$file.time());
                                        rename($filePath, $ftpProcessedDir.$file.time());
                                    }
                                }
                            }
                        } else {
                            $unzipParams['ignored'][$num] = $filePath;
                        }
                    }
                }
            }
        }

        if (!empty($unzipParams['success']) && !empty($routes) && is_array($routes)){
            //dump(1);
            foreach($unzipParams['success'] as $fileName) {
                //dump($fileName);
                foreach($routes as $newPath => $pattern) {
                    //dump($newPath, $pattern);
                    if (preg_match('/'.$pattern.'/', $fileName)) {

                        if (!file_exists($newPath)) {
                            mkdir($newPath, 0777, true);
                        }
                        //dump("rename($workDir$fileName, $newPath$fileName)");
                        $renamed = rename($workDir.$fileName, $newPath.$fileName);
                        if($renamed) {
                            //dump("dispat:", $dispatched);
                            $dispatchs['success'][] = $newPath.$fileName;
                        } else {
                            $dispatchs['fail'][]    = $newPath.$fileName;
                        }
                        break 1;//on passe au fileName suivant
                    }
                    //dump("File not redispatched : $workDir$fileName");

                }

            }

        } else {
            //dump($routes);
        }

        dump( "############## Import Task Report $workDir ##############"
            , "Files/folders in dir FTP.........: ".count($ftpParams['remotelist'])
            , "FTP files downloaded successfully: ".count($ftpParams['success'])
            , "FTP files/folders ignored........: ".count($ftpParams['ignored'])
            , "FTP download files failed........: ".count($ftpParams['fail'])
            , "Unzipped files successfully......: ".count($unzipParams['success'])
            , "Unzipped files ignored...........: ".count($ftpParams['ignored'])
            , "Unzipped files failed............: ".count($ftpParams['fail'])
            , "Redispatchs successfully.........: ".count($dispatchs['success'])
            , "Redispatchs failed...............: ".count($dispatchs['fail'])
        );
        foreach (array('fail', 'ignored') as $key) {
            if (count($ftpParams[$key]) > 0) {
                dump("FTP $key :", $ftpParams[$key]);
            }
            if (count($unzipParams[$key]) > 0) {
                dump("Unzip $key :", $unzipParams[$key]);
            }
            if (count($dispatchs[$key]) > 0) {
                dump("dispatch $key :", $dispatchs[$key]);
            }
        }
        //dump("return:", $dispatchs);

        return $dispatchs['success'];
    }//function importFtp

	/**
	 * Positionne la timezone.
	 */
	public static function setTimezone($timezone)
	{
		self::$timezone = $timezone;
	}

	/**
	 * Retourne la timezone.
	 */
	public static function getTimezone()
	{
		return self::$timezone;
	}

	/**
     * Retourne la date au format demandé en utilisant une timezone GMT/UTC.
     *
     * @param string $format
     * @param int $timestamp
     * @return string
     */
	public static function date($format, $timestamp=NULL)
	{
		if (isset($timestamp)) {
			$date = gmdate($format, $timestamp);
		}
		else {
			$date = gmdate($format);
		}
		return $date;
	}

  /**
   * Lit la valeur du compteur $name et l'initialise si besoin
   */
  public static function counter($name = null, $length = null)
  {
    // Si le paramètre $name n'est pas défini ou null on récupère l'identifier de l'interface
    if ($name === null) {
      if (isset(static::$registry['identifier'])) {
        $name = static::$registry['identifier'];
      }
      else {
        throw new Exception('Esb registry key "identifier" doesn\'t exists '. EaiDebug::getFunctionsTrace());
      }
    }

    if (isset(static::$registry['counters'])) {
      $counters = static::$registry['counters'];
    }
    else {
      $counters = array();
    }

    if (isset($counters[$name])) {
		  // On récupère le compteur depuis le $registry
      $counter = $counters[$name];
		}
		else {
      // On crée le compteur dans le $registry
		  $db = new EaiDbGateway();
      $table = $db->getTable('counter');

      $rowset = $table->select(array('name'=>$name));
      if ($rowset->count() <= 0) {
        // On crée le compteur dans la base de données et on l'initialise à 1.
        $table->insert(array('name'=>$name, 'value'=>'1'));
     	  $rowset = $table->select(array('name'=>$name));
     	  if ($rowset->count() <= 0) {
   	      throw new Exception("Can't read counter ". EaiDebug::getFunctionsTrace());
   	    }
      }

      $counter = $rowset->current()->value;
      $counters[$name] = $counter;
      static::register('counters', $counters);
    }

    return $length ? str_pad($counter, $length, '0', STR_PAD_LEFT) : $counter;
  }


  /**
   * Incrémente le compteur $name
   */
  public static function counterIncrement($name = null, $length = null)
  {
   // Si le paramètre $name n'est pas défini ou null on récupère l'identifier de l'interface
    if ($name === null) {
      if (isset(static::$registry['identifier'])) {
        $name = static::$registry['identifier'];
      }
      else {
        throw new Exception('Esb registry key "identifier" doesn\'t exists '. EaiDebug::getFunctionsTrace());
      }
    }

    $db = new EaiDbGateway();
    $table = $db->getTable('counter');

    $table->update(array('value' => $db->exp('LAST_INSERT_ID(value + 1)')), array('name' => $name));
    $counter = $db->getAdapter()->getDriver()->getConnection()->getLastGeneratedValue();

    if (isset(static::$registry['counters'])) {
      $counters = static::$registry['counters'];
    }
    else {
      $counters = array();
    }

    $counters[$name] = $counter;
    static::unregister('counters'); // Solution peu élégante pour mettre à jour une valeur du registry.
    static::register('counters', $counters);

    return $length ? str_pad($counter, $length, '0', STR_PAD_LEFT) : $counter;
  }

}//class

/**
 * @param mixed
 *
 * @return false|string
 */
function dump()
{
		if (/*Esb::isCli() or*/ Esb::GET('nodump') >= 1) {
			return false;
		}
		// 7 654 321 = 7,5Mo : permet de pas planter le navigateur
		if( memory_get_usage() > 60654321) {
			echo PHP_EOL." ***** display dump disabled, too high memory usage : ".memory_get_usage();
			return false;
		}
		ob_start();
		$args = @func_get_args();
		$nbArgs = count($args);
		if ($nbArgs > 1) {
			$s = 's';
		} else {
			$s = '';
		}

		foreach ($args as $key => $arg) {
			if ($s) {
				echo ($key+1).") ";
			}
			var_dump($arg);
		}
		$c = ob_get_contents();
		ob_end_clean();


		if (Esb::isCli()) {
            echo $c;
		    return false;
		}

		$c = preg_replace("/\r\n|\r/", "\n", $c);
		$c = str_replace("]=>\n", '] = ', $c);
		$c = preg_replace('/= {2,}/', '= ', $c);
		$c = preg_replace("/\[\"(.*?)\"\] = /i", "[$1] = ", $c);
		$c = preg_replace('/    /', "        ", $c);
		$c = preg_replace("/\"\"(.*?)\"/i", "\"$1\"", $c);

		$c = htmlspecialchars($c, ENT_NOQUOTES);

		// Expand numbers (ie. int(2) 10 => int(1) 2 10, float(6) 128.64 => float(1) 6 128.64 etc.)
// 		$c = preg_replace("/(int|float)\(([0-9\.]+)\)/ie", "'$1('.strlen('$2').') <span class=\"number\">$2</span>'", $c);
		$c = preg_replace("/(int|float)\(([0-9\.]+)\)/i", "'$1('.strlen('$2').') <span class=\"number\">$2</span>'", $c);
		
		// Syntax Highlighting of Strings. This seems cryptic, but it will also allow non-terminated strings to get parsed.
		$c = preg_replace("/(\[[\w ]+\] = string\([0-9]+\) )\"(.*?)/sim", "$1<span class=\"string\">\"", $c);
		$c = preg_replace("/(\"\n{1,})( {0,}\})/sim", "$1</span>$2", $c);
		$c = preg_replace("/(\"\n{1,})( {0,}\[)/sim", "$1</span>$2", $c);
		$c = preg_replace("/(string\([0-9]+\) )\"(.*?)\"\n/sim", "$1<span class=\"string\">\"$2\"</span>\n", $c);

		$regex = array(
				// Numbers
				'numbers' => array('/(^|] = )(array|float|int|string|resource|object\(.*\)|\&amp;object\(.*\))\(([0-9\.]+)\)/i', '$1$2(<span class="number">$3</span>)'),
				// Keywords
				'null' => array('/(^|] = )(null)/i', '$1<span class="keyword">$2</span>'),
				'bool' => array('/(bool)\((true|false)\)/i', '$1(<span class="keyword">$2</span>)'),
				// Types
				'types' => array('/(of type )\((.*)\)/i', '$1(<span class="type">$2</span>)'),
				// Objects
				'object' => array('/(object|\&amp;object)\(([\w]+)\)/i', '$1(<span class="object">$2</span>)'),
				// Function
				'function' => array('/(^|] = )(array|string|int|float|bool|resource|object|\&amp;object)\(/i', '$1<span class="function">$2</span>('),
		);

		foreach ($regex as $x) {
			$c = preg_replace($x[0], $x[1], $c);
		}
		$style = '
	    /* outside div - it will float and match the screen */
	    .dumpr {
	            margin: 0 2px 2px;
	            padding: 0 2px 2px;
	            background-color: #fbfbfb;
	            float: left;
	            clear: both;
	    }
	    /* font size and family */
	    .dumpr pre {
	            color: #000000;
	            font-size: 9pt;
	            font-family: "Courier New",Courier,Monaco,monospace;
	            margin: 0px;
	            padding-top: 5px;
	            padding-bottom: 7px;
	            padding-left: 9px;
	            padding-right: 9px;
	    }
	    /* inside div */
	    .dumpr div {
	            background-color: #fcfcfc;
	            border: 1px solid #d9d9d9;
	            float: left;
	            clear: both;
	    }
	    /* syntax highlighting */
	    .dumpr span.string {color: #c40000;}
	    .dumpr span.number {color: #ff0000;}
	    .dumpr span.keyword {color: #007200;}
	    .dumpr span.function {color: #0000c4;}
	    .dumpr span.object {color: #ac00ac;}
	    .dumpr span.type {color: #0072c4;}
	    .legenddumpr {
	   		      background-color: #fcfcfc;
	            border: 1px solid #d9d9d9;
	            padding: 1px;
	    }
	    ';

		$style = preg_replace("/ {2,}/", "", $style);
		$style = preg_replace("/\t|\r\n|\r|\n/", "", $style);
		$style = preg_replace("/\/\*.*?\*\//i", '', $style);
		$style = str_replace('}', '} ', $style);
		$style = str_replace(' {', '{', $style);
		$style = trim($style);

		$c = trim($c);
		$c = preg_replace("/\n<\/span>/", "</span>\n", $c);

		$S_from	= '';
		// --- Affichage de la provenance du print_rn
		$A_backTrace	= debug_backtrace();
		if (is_array($A_backTrace) && array_key_exists(0, $A_backTrace)) {
			$S_from = <<< BACKTRACE
dump({$nbArgs} arg$s) {$A_backTrace[0]{'file'}} L{$A_backTrace[0]{'line'}}
BACKTRACE;

			} else {
				$S_from = basename($_SERVER['SCRIPT_FILENAME']);
			}
			$S_from.= " - ".EaiDebug::getFunctionsTrace();

			$S_from = str_replace(array(Esb::ROOT), '', $S_from);
			$S_out	= '';
			$S_out	.= "<style type=\"text/css\">".$style."</style>\n";
			$S_out	.= '<fieldset class="dumpr">';
			$S_out	.= '<legend class="legenddumpr">'.$S_from.'</legend>';
			$S_out	.= '<pre>'.$c.'</pre>';
			$S_out	.= '</fieldset>';
			$S_out	.= "<div style=\"clear:both;\">&nbsp;</div>";

			echo $S_out;
	}//dump()

/**
 * affiche le parametre et  fait un exit si Esb::KILL_ALLOWED
 * @param bit
 */
function kill($dump = 'kill')
{
		if (Esb::KILL_ALLOWED) {
			$level = EaiLogger::FATAL;
		} else {
			$level = EaiLogger::DEBUG;
		}
		dump($dump);
		EaiObject::log("kill() [KILL_ALLOWED ".(int)Esb::KILL_ALLOWED."] called in ".EaiDebug::getFunctionsTrace(), $level);
		if (Esb::KILL_ALLOWED) {
			exit("exit");
		}
		return Esb::KILL_ALLOWED;
}


/**
 * Return the current date, SQL-friendly (year-month-day)
 * @return string
 */
function dateday()
{
	return date("Y-m-d");
}

/**
 * Return the curent datetime, SQL-friendly (year-month-day hour:min:sec)
 * @return string
 */
function datetime()
{
	return date("Y-m-d H:i:s");
}
