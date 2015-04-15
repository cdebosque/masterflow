<?php
/**
 * Manage the in>connector>plugins>plugin type='ftp'
 *
 * @uses EaiConnectorFile, EaiConnectorSystem
 * @example interface.xml :
 <connection type="file">
	 <file>test.csv</file>
	 <plugins>
		 <plugin type="sftp">
			 <server>ptdr.com</server>
			 <user>mdr</user>
			 <password>lol</password>
			 <remoteDir></remoteDir>
			 <allowRetransfert>1</allowRetransfert>
		 </plugin>
	 <plugins>
 *
 * @author tbondois
 */
class EaiPluginSftp extends EaiPluginFtp
{
	const PLUGIN_TYPE = 'sftp';


    /**
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _connect($obj)
    {

        $r = false;
        $callback = array();

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

        $pluginDefaultValues = array( 'server'            => null
                                , 'user'              => null
                                , 'password'          => null
                                , 'port'              => 22
                                , 'remoteDir'         => ''
                                , 'remoteFile'        => ''
                                , 'pubkeyfile'        => ''
                                , 'privkeyfile'        => ''
                                , 'passphrase'        => ''
                                //, 'passive'           => 1
                                //, 'transfertMode'     => FTP_BINARY
                                , 'override'          => 0   //TODO mode 2 qui renomme a coté (pas utile ici)
                                , 'deleteSourceAfter' => 0 //TODO implement
                                , 'localFile'         => ''
                                , 'localDir'          => ''
                                , 'success'           => false
                                , 'connection'        => null
        );

        $plugin = array_merge($pluginDefaultValues, $plugin);//ordre de surcharge important

        if (ENV != 'prod') {
            //Sécurité : à désactiver pour tester la suppression de fichier sur le serveur distant
            $plugin['deleteSourceAfter'] = 0;
        }

        if (strlen($plugin['localDir']) == 0 && strlen($obj->getDir()) > 0) {
            //$plugin['localDir'] = $obj->getDir().'/'.$obj->getProp('dirProcessing');
            $plugin['localDir'] = $obj->getDir().'/'. ($obj->getProp('workflow') ? $obj->getProp('dirProcessing') : '');
        }

//         if (!$plugin['remoteFile'] && $obj->getWay() == Esb::WAY_OUT) {
//             $plugin['remoteFile'] = $obj->getProp('file');
//         }

        if ($obj->getWay() == Esb::WAY_OUT) {

            //En export le fichier local est le plus important et doit être systématiquement renseigné
            if(!$plugin['remoteFile']) {
                if (!empty($plugin['localFile'])) {
                    $plugin['remoteFile']= $plugin['localFile'];
                } else {
                    $plugin['remoteFile'] = $objFile;
                }
            }

            if (empty($plugin['localFile'])) {
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

            if (empty($plugin['remoteFile'])) {
                $plugin['remoteFile'] = $objFile;
            }
        }

//         $origLocalDir = $plugin['localDir'];
//         if (strlen($origLocalDir) > 0 && (string)$origLocalDir[strlen($origLocalDir)-1] !== '/') {
//             //on cast en string au cas ou, et on regarde le dernier caractère de la variable string avec []
//             $plugin['localDir'].= '/'; //remoteDir doit contenir un / de fin
//         }

        if(strlen($plugin['localDir']) == 0 && strlen($obj->getDir()) > 0) {
            if ($obj->getPathProcessing()) {
                $plugin['localDir'] = $obj->getPathProcessing();
            } else {
                $plugin['localDir'] = $obj->getDir();
            }
        }

        $origRemoteDir = $plugin['remoteDir'];
        if (strlen($origRemoteDir) > 0 && (string)$origRemoteDir[strlen($origRemoteDir)-1] !== '/') {
            //on cast en string au cas ou, et on regarde le dernier caractère de la variable string avec []
            $plugin['remoteDir'].= '/'; //remoteDir doit contenir un / de fin
        }


        if(!empty($plugin['user']) and !empty($plugin['pubkeyfile']) and !empty($plugin['privkeyfile'])) {
            $connectionArgs= array('user'=>$plugin['user'], 'pubkeyfile'=>$plugin['pubkeyfile'], 'privkeyfile'=>$plugin['privkeyfile']);
        } else {
            $connectionArgs= array();
        }


        if (is_null($plugin['connection'])) {

            if (strlen($plugin['server']) > 0
             && strlen($plugin['user']   ) > 0
             && strlen($plugin['password']) > 0
             && strlen($plugin['localFile']) > 0
            ) {
                $localDirExist = false;
                if (strlen(trim($localDirExist, '/')) == 0 or file_exists($plugin['localDir'])) {
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
                        try {

                            dump($connectionArgs);


                            if( $connectionArgs ) {
                                $connection = new SFTPConnection($plugin['server'], $plugin['port'], $connectionArgs);
                                $r = $connection->isAvailable();

                            } else {

                                $connection = new SFTPConnection($plugin['server'], $plugin['port']);

                                if ($connection) {
                                    $r = $connection->login($plugin['user'], $plugin['password']);
                                    $r = $connection->isAvailable();
                                } else {
                                    $obj->log("connecting : bad SFTP Connection({$plugin['server']}, '{$plugin['port']}')", 'fatal');
                                }
                            }

                            if (!$r) {
                                $obj->log("connecting : bad sftp login ('{$plugin['user']}', [password])", 'fatal');
                            } else {
                                $plugin['connection'] = $connection;
                            }
                            dump($r);
                            exit;

                        } catch (Exception $e) {
                            $obj->log("Exception SFTP Connexion/authentification : ".$e->getMessage(), 'err');
                        }
                    } else $obj->log("checking : local file {$plugin['localDir']}{$plugin['localFile']} already exist, mode override = 0", 'fatal');
                } else $obj->log("checking : local dir {$plugin['localDir']} not exist", 'fatal');
            } else {
                $obj->log("checking : bad configuration : required variable(s) missing (required : server, user, password, file)", 'fatal');

            }

        } else {
            $r = true;
            $obj->log("connecting : already done, aborted", 'info');
        }

        $obj->plugins[static::PLUGIN_TYPE] = $plugin;
        if ($obj->hasProperty('dir')) {
            $obj->setProp('dir', $plugin['localDir']);
        }
        //dump("SFTP Callback :", $plugin);
        return $r;
    }

    /**
     * @todo trouver vrai déconnexion
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _disconnect($obj)
    {
        $obj->plugins[static::PLUGIN_TYPE]['connection'] = null;
        return true;
    }

    /**
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _get($obj)
    {
        $r = false;
        $connection = $obj->plugins[static::PLUGIN_TYPE]['connection'];

        try {
            $r = $connection->get($obj->plugins[static::PLUGIN_TYPE]['remoteDir']
                                . $obj->plugins[static::PLUGIN_TYPE]['remoteFile']
                                , $obj->plugins[static::PLUGIN_TYPE]['localDir']
                                . $obj->plugins[static::PLUGIN_TYPE]['localFile']
            )  ;
            dump($obj->plugins[static::PLUGIN_TYPE]['remoteDir']
               . $obj->plugins[static::PLUGIN_TYPE]['remoteFile']
               , $obj->plugins[static::PLUGIN_TYPE]['localDir']
               . $obj->plugins[static::PLUGIN_TYPE]['localFile']
            )  ;
        } catch (Exception $e) {
            $obj->log("Exception on SFTP GET : ".$e->getMessage(), 'err');
        }
        return $r;
    }

    /**
     * @param EaiConnectorFile $obj
     * @return bool
     */
    static protected function _put($obj)
    {
        $r = false;
        $connection = $obj->plugins[static::PLUGIN_TYPE]['connection'];

        $localFile  = $obj->plugins[static::PLUGIN_TYPE]['localDir'].$obj->plugins[static::PLUGIN_TYPE]['localFile'];
        $remoteFile = $obj->plugins[static::PLUGIN_TYPE]['remoteDir'].$obj->plugins[static::PLUGIN_TYPE]['remoteFile'];


        try {
            $r = $connection->put($localFile , $remoteFile);
        } catch (Exception $e) {
            $obj->log("Exception on SFTP PUT : ".$e->getMessage(), 'err');
        }

        if (!$r) {
            dump($obj->plugins[static::PLUGIN_TYPE]['connection'], $localFile , $remoteFile);
        }
        return $r;
    }
}

?>