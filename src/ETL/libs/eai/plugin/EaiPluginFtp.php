<?php
/**
 * Manage the in>connector>plugins>plugin type='ftp'
 *
 * @uses EaiConnectorFile, EaiConnectorSystem
 * @example interface.xml :
 <connection type="file">
	 <file>test.csv</file>
	 <plugins>
		 <plugin type="ftp">
			 <server>disabled|ptdr.com</server>
			 <user>mdr</user>
			 <password>lol</password>
			 <remoteDir></remoteDir>
			 <allowRetransfert>1</allowRetransfert>
			 <checksum>0</checksum>
		 </plugin>
	 <plugins>
 *
 * @author tbondois
 */
class EaiPluginFtp extends EaiObject
{
	const PLUGIN_TYPE = 'ftp';

  static $conf = array();







    /**
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _connect($obj)
    {
        $r = false;

        if (!isset($obj->plugins[static::PLUGIN_TYPE])) {
            $obj->log("plugin enabled only for the other way", 'debug');
            return false;
        } else {
            $obj->log(__CLASS__.'.'.__FUNCTION__. " launched for way: ".$obj->getWay(), 'debug');
        }


        $plugin = $obj->plugins[static::PLUGIN_TYPE];

        if (empty($plugin)) {
            $obj->log("variable plugin[".static::PLUGIN_TYPE."] vide, return", 'err');
            return false;
        }

        $pluginDefaultValues = array('server'            => null
                                   , 'user'              => null
                                   , 'password'          => null
                                   , 'port'              => 21
                                   , 'remoteDir'         => ''
                                   , 'remoteFile'        => ''
                                   , 'passive'           => 1
                                   , 'transfertMode'     => FTP_BINARY
                                   , 'override'          => 0   //TODO mode 2 qui renomme a coté (pas utile ici)
                                   , 'deleteSourceAfter' => 0 //TODO implement
                                   , 'localFile'         => ''
                                   , 'localDir'          => ''
                                   , 'success'           => false
                                   , 'connection'        => null
                                   , 'cumulative'        => 0
                                   , 'cumulativeLimit'        => 0
                                   , 'searchMode'        => 0
                                   , 'checksum'        => 0
                                   , 'deleteAuto'        => 0
                                   , 'filePattern'       => $obj->getFilePattern()
        );


        $plugin = array_merge($pluginDefaultValues, $plugin);//ordre de surcharge important

        if (ENV != 'prod') {
            //Sécurité : à désactiver pour tester la suppression de fichier sur le serveur distant
            $plugin['deleteSourceAfter'] = 0;
        }

        if(strlen($plugin['localDir']) == 0 && strlen($obj->getDir()) > 0) {
            if ($obj->getPathProcessing()) {
                $plugin['localDir'] = $obj->getPathProcessing();
            } else {
                $plugin['localDir'] = $obj->getDir();
            }
        }

        $objFile= $obj->getProp('file');
        if( empty($objFile) ) {
            $obj->log("Property File not exist", 'err');
        }


        if ($obj->getWay() == Esb::WAY_OUT) {

            //En export le fichier local est le plus important et doit être systématiquement renseigné
            if(!$plugin['remoteFile']) {
                if (!empty($plugin['localFile'])) {
                    $plugin['remoteFile']= $plugin['localFile'];
                } else {
                    $plugin['remoteFile'] = $objFile;
                }
            }

            if (empty($plugin['localFile']) && empty($plugin['filePattern'])) {
                $plugin['localFile'] = $objFile;
            }
        } else {
            //Symétrique para rapport au if précédent
            //En import le fichier distant (remote) est le plus important
            if(!$plugin['localFile']) {
                if (!empty($plugin['remoteFile'])) {
                    $plugin['localFile']= $plugin['remoteFile'];
                } else {
                    $plugin['localFile'] = $objFile;
                }
            }

            if (empty($plugin['remoteFile']) && empty($plugin['filePattern'])) {
                $plugin['remoteFile'] = $objFile;
            }
        }


        if (is_null($plugin['connection'])) {

            if (strlen($plugin['server']) > 0
             && strlen($plugin['user']) > 0
             && strlen($plugin['password']) > 0
            ) {
                $localDirExist = false;
                if (file_exists($plugin['localDir'])) {
                    $localDirExist = true;
                } else {
                    if (mkdir($plugin['localDir'], 0777, true)) {
                        $localDirExist = true;
                        $obj->log('Creating local directory recursively '.$plugin['localDir']);
                    } else {
                        $obj->log("Bad local mkdir '{$plugin['localDir']}'", 'err');
                    }
                }

                if ($localDirExist) {
                    if ($plugin['override'] or !file_exists($plugin['localDir'].$plugin['localFile'])) {
                        $plugin['connection'] = ftp_connect($plugin['server'], $plugin['port']);
                        if ($plugin['connection']) {
                            if (ftp_login($plugin['connection'], $plugin['user'], $plugin['password'])) {
                                if (ftp_pasv($plugin['connection'], (bool)$plugin['passive'])) {
                                    if (strlen((string)$plugin['remoteDir']) == 0
                                     || ftp_chdir($plugin['connection'], $plugin['remoteDir'])
                                    ) {
                                        $r = true;

                                    } else $obj->log("connecting : bad ftp_chdir(connection, '{$plugin['remoteDir']}')", 'fatal');
                                } else $obj->log("connecting : bad ftp_pasv(connection, '{$obj->passive}')", 'fatal');
                            } else $obj->log("connecting : bad ftp_login(connection, '{$obj->user}', password)", 'fatal');
                        } else  $obj->log("connecting : bad ftp_connect({$obj->server}, '{$obj->port}')", 'fatal');
                    } else $obj->log("checking : local file {$plugin['localDir']}{$plugin['localFile']} already exist, mode override = 0", 'fatal');
                } else $obj->log("checking : local dir {$plugin['localDir']} not exist", 'fatal');
            } else $obj->log("checking : bad configuration : required variable(s) missing (required : server, user, password, file)", 'fatal');

        } else {
            $r = true;
            $obj->log("connecting : already done, aborted", 'debug');
        }

        $obj->plugins[static::PLUGIN_TYPE] = $plugin;
        if($obj->hasProperty('dir')) {
            $obj->setProp('dir', $plugin['localDir']);
        }

        static::$conf = $obj->plugins[static::PLUGIN_TYPE];
        return $r;
    }

    /**
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _disconnect($obj)
    {
        $r = ftp_close($obj->plugins[static::PLUGIN_TYPE]['connection']);
        return $r;
    }

    /**
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _get($obj)
    {
        $r = ftp_get($obj->plugins[static::PLUGIN_TYPE]['connection']
                   , $obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$obj->plugins[static::PLUGIN_TYPE]['localFile']
                   , $obj->plugins[static::PLUGIN_TYPE]['remoteFile']
                   , $obj->plugins[static::PLUGIN_TYPE]['transfertMode']
        );

        if ($r && static::$conf['deleteAuto']) {
          ftp_delete($f);
        }
        return $r;
    }

    /**
     * Recupere les fichiers d'un repertoire distant
     * @param EaiConnectorFile $obj
     * @return mixed
     */
    static protected function _getInDir($obj)
    {
        $files = ftp_nlist($obj->plugins[static::PLUGIN_TYPE]['connection'], '');

        $cumulative = $obj->plugins[static::PLUGIN_TYPE]['cumulative'] && !empty($files);
        $cumulativeLimit = $obj->plugins[static::PLUGIN_TYPE]['cumulativeLimit'];
        
        $searchMode = $obj->plugins[static::PLUGIN_TYPE]['searchMode']
          ? $obj->plugins[static::PLUGIN_TYPE]['searchMode']
          : 'fifo';

        $pattern = $obj->getFilePattern() ? "/".$obj->getFilePattern()."/" : null;

        $nbFiles = 0;
        $finalFile = '';

        if ($cumulative) {
          $finalFile = $obj->plugins[static::PLUGIN_TYPE]['localFile'];
          $cumulHandle = fopen(
            $obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$finalFile,
            'w+');
        }

        Foreach ($files as $f) {

          if (!empty($pattern)
            && !preg_match($pattern, $f)) continue;

          if (static::$conf['checksum']) {
            // Le fichier checksum a le même nom que le fichier d'origine en remplacant l'extension par FIN
            $checksumFileName = preg_replace('/\.[^.]+$/', '.FIN',$f);
            if (!in_array($checksumFileName, $files)) {
              continue;
            }
          }



          $r = ftp_get($obj->plugins[static::PLUGIN_TYPE]['connection']
                   , $obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$f
                   , $f
                   , $obj->plugins[static::PLUGIN_TYPE]['transfertMode']);

          if (!$r) {
            $obj->log("_getInDir ".static::$conf['localDir'].'/'.$f." can't be downloaded", 'err');
          }
          
          if ($r && static::$conf['deleteAuto']) {
            ftp_delete(static::$conf['connection'], $f);
            if (static::$conf['checksum']) {
              ftp_delete(static::$conf['connection'], $checksumFileName);
            }
            $obj->log("_getInDir ".$f." deleted from remote server", 'info');            
          }


          if ($cumulative) {
            if ($r) {
            	fwrite($cumulHandle, file_get_contents($obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$f));
            	unlink($obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$f);
              $nbFiles++;
              $obj->log("_getInDir ".$f." added to cumulative file", 'info');
            }
            echo $cumulativeLimit;
            if ($cumulativeLimit && $nbFiles >= $cumulativeLimit) {
               break;
            }
          } else {
            if ($r) {
              if ($finalFile) {
                // Si on ne cumule pas les fichier est qu'on ne renvoit pas tous les fichiers
                // il faut detruire les fichiers locaux non finaux
                // TODO : l'ideal serait de ne pas télécharger tous les fichiers
                unlink($obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$finalFile);
              }
              $finalFile = $f;
              if ($searchMode == 'fifo') { //$mode != 'newestOnly'
                break;
              }
            }
          }
        }

        if ($cumulative) {
          $obj->log("_getInDir ".$nbFiles." files cumulated", 'info');
          fclose($cumulHandle);
        }
        
        $obj->log("_getInDir ".$finalFile." downloaded", 'info');

        return $finalFile ? $finalFile : 0;
    }

    /**
     * Transfère les fichiers d'un répetoire local
     * @param EaiConnectorFile $obj
     * @return mixed
     */
    static protected function _putFromDir($obj)
    {
        $files = scandir(static::$conf['localDir']);
        $error = false;
        $nb = 0;


        Foreach ($files as $f) {
          /*if (!empty(static::$conf['filePattern'])
            && !preg_match(static::$conf['filePattern'], $f)) continue;*/

          if ($f == '..' || $f == '.') continue;

          $r = ftp_put(static::$conf['connection']
                   , $f
                   , static::$conf['localDir'].'/'.$f
                   , static::$conf['transfertMode']
          );

          if ($r && static::$conf['deleteAuto']) {
            unlink(static::$conf['localDir'].'/'.$f);
          }

          if ($r) {
            $nb++;
          } else {
            $obj->log("_putFromDir ".static::$conf['localDir'].'/'.$f." can't be uploaded", 'err');
          }
        }

        if (static::$conf['checksum']) {
          $handle = fopen($obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.static::$conf['checksum'], 'w+');
          fclose($handle);
          $r = ftp_put($obj->plugins[static::PLUGIN_TYPE]['connection']
                   , static::$conf['checksum']
                   , $obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.static::$conf['checksum']
                   , $obj->plugins[static::PLUGIN_TYPE]['transfertMode']
          );
        }

        return $erreur ? false : $nb;
    }


    /**
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _put($obj)
    {
        /*if(Esb::ENV == 'dev') {
            $obj->log("DEV ENV : file ".$obj->plugins[static::PLUGIN_TYPE]['remoteFile']." not put with FTP (skipped)", 'warn');
            return true;
        }*/

        $r = ftp_put($obj->plugins[static::PLUGIN_TYPE]['connection']
                   , $obj->plugins[static::PLUGIN_TYPE]['remoteFile']
                   , $obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$obj->plugins[static::PLUGIN_TYPE]['localFile']
                   , $obj->plugins[static::PLUGIN_TYPE]['transfertMode']
        );
//         dump($r);
//         exit;

        if ($r && $obj->plugins[static::PLUGIN_TYPE]['checksum']) {
          // On créer un fichier de checksum du même nom que le fichier d'origine en remplacant l'extension par FIN
          $checksumFileName = preg_replace('/\.[^.]+$/', '.FIN',$obj->plugins[static::PLUGIN_TYPE]['remoteFile']);
          $handle = fopen($obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$checksumFileName, 'w+');
          fwrite($handle, md5_file($obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$obj->plugins[static::PLUGIN_TYPE]['localFile']));
          fclose($handle);
          $r = ftp_put($obj->plugins[static::PLUGIN_TYPE]['connection']
                   , $checksumFileName
                   , $obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$checksumFileName
                   , $obj->plugins[static::PLUGIN_TYPE]['transfertMode']
          );
        }
        return $r;
    }

    /**
     * Récupération d'un fichier
     * @param EaiEvent $event
     */
    public function onConnectInStart(EaiEvent $event) {
      /** @var $obj EaiConnectorFile */
      $obj = $event->getObj(); //modifié par référence dans _connect()

      if (!isset($obj->plugins[static::PLUGIN_TYPE]) or $obj->getWay() != Esb::WAY_IN or $obj->plugins[static::PLUGIN_TYPE]['server'] === 'disabled') {
        return true;
      }

      if (static::_connect($obj)) {

        $localFile = $obj->plugins[static::PLUGIN_TYPE]['localDir'] . '/' . $obj->plugins[static::PLUGIN_TYPE]['localFile']; //risque que double-slashs, non-gênant
        if (!empty($obj->plugins[static::PLUGIN_TYPE]['remoteFile'])) {
          // On va chercher un fichier prédéfini
          $remoteFile = $obj->plugins[static::PLUGIN_TYPE]['remoteFile'];
          //on est déja positionné sur le bon dossier distant avec un chdir
          if (static::_get($obj)) {
            $obj->plugins[static::PLUGIN_TYPE]['success'] = true;
            $obj->log("FTP download file [{$obj->plugins[static::PLUGIN_TYPE]['remoteDir']}]$remoteFile to $localFile successfully");
            //dump("ftp_get ok $remoteFile > $localFile");
          } else
            $obj->log("connecting : bad get '$localFile' << [" . $obj->plugins[static::PLUGIN_TYPE]['remoteDir'] . "]$remoteFile', ", 'err');
        } elseif (isset($obj->plugins[static::PLUGIN_TYPE]['remoteDir'])) {
          // On recherche dans un dossier distant
          $file = static::_getInDir($obj);
          if ($file === false) {
            $obj->log("connecting : bad get '$localFile' << [" . $obj->plugins[static::PLUGIN_TYPE]['remoteDir'] . "]$remoteFile', ", 'err');
          } elseif ($file === 0) {
            $obj->log("connecting : No matching files in  [" . $obj->plugins[static::PLUGIN_TYPE]['remoteDir'] . "]', ", 'warn');
            $obj->plugins[static::PLUGIN_TYPE]['success'] = true;
          } else {
            $obj->plugins[static::PLUGIN_TYPE]['success'] = true;
            $obj->log("FTP download file {$obj->plugins[static::PLUGIN_TYPE]['remoteDir']}/$file to {$obj->plugins[static::PLUGIN_TYPE]['localDir']}/$file successfully");
            $obj->setFile($file);
          }
        }
      } else {
        $obj->fault("Erreur connecting " . $obj->plugins[static::PLUGIN_TYPE]['server']);
      }
    }


    /**
     * Déconnexion mode récupération de fichier
     */
    public function onDisconnectInStart(EaiEvent $event)	//onStart_connect_in()
    {
        /** @var $obj EaiConnectorFile */
        $obj = $event->getObj();

        if ($obj->getStopOnError()) {
            return false;
        }

        if (!isset($obj->plugins[static::PLUGIN_TYPE]) or $obj->getWay() != Esb::WAY_IN or $obj->plugins[static::PLUGIN_TYPE]['server'] === 'disabled') {
            return true;
        }

        $r = static::_disconnect($obj);
        if (!$r) {
            $obj->log("disconnecting : error", 'warn');
        }
    }


    /**
     * Envoi d'un fichier (put)
     *
     * @TODO peut-etre se connecter onConnectOutStart ? Mais si on perd la connexion entretemps, il faut gérer de la relancer
     *
     * @author tbondois
     */
    public function onDisconnectOutStart(EaiEvent $event)
    {
        /** @var $obj EaiConnectorFile */
        $obj  = $event->getObj();

        if ($obj->getStopOnError()) {
            return false;
        }


        if (!isset($obj->plugins[static::PLUGIN_TYPE]) or $obj->getWay() != Esb::WAY_OUT or $obj->plugins[static::PLUGIN_TYPE]['server'] === 'disabled') {
           return true;
        }

        if (static::_connect($obj)) {
            if($obj->getRawDatasWrited() < 1){
                return false;
            }
            if (!empty($obj->plugins[static::PLUGIN_TYPE]['localFile'])) {
              // On connait le fichier que l'on doit récupérer
              $localFile  = $obj->plugins[static::PLUGIN_TYPE]['localDir'].'/'.$obj->plugins[static::PLUGIN_TYPE]['localFile']; //risque que double-slashs, non-gênant
              $remoteFile = $obj->plugins[static::PLUGIN_TYPE]['remoteFile'];

              if (static::_put($obj)) {
                  $obj->plugins[static::PLUGIN_TYPE]['success'] = true;
                  $obj->log("FTP uploaded file $localFile to $remoteFile successfully");
              } else {
                  //fault ne sors pas de l'esb ici ??
                  $obj->log("connecting : bad put [{$obj->plugins[static::PLUGIN_TYPE]['remoteDir']}] '$remoteFile' << '$localFile', '{$obj->plugins[static::PLUGIN_TYPE]['transfertMode']}'", 'fatal');
                  $obj->fault("connecting : bad put [{$obj->plugins[static::PLUGIN_TYPE]['remoteDir']}] '$remoteFile' << '$localFile', '{$obj->plugins[static::PLUGIN_TYPE]['transfertMode']}'");
                  //dump('content extrait:', substr(file_get_contents($localFile), 0, 100));
              }
            }  elseif (isset($obj->plugins[static::PLUGIN_TYPE]['localDir'])) {
              // On recherche dans un dossier
              $nb = static::_putFromDir($obj);

              if ($nb === false) {
                $obj->log("connecting : error when uploaded dir {$obj->plugins[static::PLUGIN_TYPE]['localDir']}", 'err');
                $obj->plugins[static::PLUGIN_TYPE]['success'] = false;
              } elseif ($nb === 0) {
                $obj->log("connecting : No matching files in ".$obj->plugins[static::PLUGIN_TYPE]['localDir'], 'warn');
                $obj->plugins[static::PLUGIN_TYPE]['success'] = true;
              } else {
                $obj->plugins[static::PLUGIN_TYPE]['success'] = true;
                $obj->log("FTP upload $nb file(s) from {$obj->plugins[static::PLUGIN_TYPE]['localDir']} to {$obj->plugins[static::PLUGIN_TYPE]['remoteDir']}/$file successfully");
                $obj->setFile($file);
              }
            }
            $r = static::_disconnect($obj);
            if (!$r) {
                $obj->log("disconnecting : error", 'err');
            }
        } else {
            $obj->fault("Erreur connecting {$obj->plugins[static::PLUGIN_TYPE]['server']}");
        }
    }


}

?>