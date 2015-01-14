<?php
/**
 * Manage the in>connector>plugins>plugin type='unzip'
 * @uses EaiConnectorSystem
 * @author tbondois
\*/
abstract class EaiPluginArchive extends EaiObject
{
		//const PLUGIN_TYPE = 'unzip';

    /**
     * Méthode qui permet de compresser un ou plusieurs fichiers $srcFile dans une archive $archiveFile
     *
     * @param string $archiveFile chemin complet du fichier archive
     * @param mixed  $srcFile liste du ou des fichiers à compresser
     */
    abstract protected function _archiveCompress($archiveFile, $srcFile);

    /**
     * Méthode qui permet de décompresser une archive $file dans un répertoire de destination $path
     * et qui retourne un tableau contenant la liste des fichiers extraits
     *
     * @param string $file chemin complet du fichier source
     * @param string $path dossier de destination des fichiers décompressés
     * @param boolean $deleteSrcFile si est égal à true, on essaie d'effacer le fichier archive d'origine $file
     * @return array liste des fichiers décompressés (vide si erreurs)
     */
    abstract protected function _archiveUnCompress($file, $path = '', $deleteSrcFile = false);


    protected function getPluginParams(EaiEvent $event)
    {
        $connector = $event->getObj();


        if (!empty($connector->plugins[static::PLUGIN_TYPE])) {
            $plugin    = $connector->plugins[static::PLUGIN_TYPE];

            $pluginDefaultValues = array('deleteSource' => 0 , 'override'     => true);

            $fileProcessing= $connector->getProp('fileProcessing');
            if ($fileProcessing) {
                $pluginDefaultValues['srcFile']= $fileProcessing;
                $pluginDefaultValues['archiveFile']= $fileProcessing.'.compressed';
//                 $pluginDefaultValues['archivePath']= dirname($fileProcessing);

            }

            $plugin = array_merge($pluginDefaultValues, $plugin);//ordre de surcharge important

            if(strpos($plugin['archiveFile'], '/')===false) {
                $plugin['archivePath']= dirname($plugin['srcFile']);
            }


        } else {
            $plugin= false;
        }



        return $plugin;
    }



		protected function onConnectInStart(EaiEvent $event)
		{

		  $obj  = $event->getObj();

		  if ($obj->getStopOnError()) {
		      return false;
		  }


      $plugin= self::getPluginParams($event);
      if (empty($plugin)) {
          return;
      }


			$r = false;

			if (isset($plugin['file']) && !empty($plugin['file']) && isset($connector->dir) && !empty($connector->dir)) {
				$srcFile = $connector->dir."/".$plugin['file'];
				$destDir = Esb::ROOT . 'var/'.static::PLUGIN_TYPE.'/' . Esb::registry('identifier').'/'.$plugin['file'] ;
				if (!$plugin['override']) {
					$destDir.= '-'.microtime(1);
				}
				$destDir.= '/';

				$localDirExist = false;

				if (file_exists($destDir)) {
					$localDirExist = true;
				} else {
					if (mkdir($destDir, 0777, true)) {
						$connector->log('Creating local directory recursively '.$destDir);
						$localDirExist = true;
					} else {
						$connector->log("Bad local mkdir '$destDir'", 'err');
					}
				}

				if ($localDirExist) {
		 			$r = static::_archiveUnCompress($srcFile, $destDir, (bool)$plugin['deleteSource']);
          $connector->log("_uncompress($srcFile, $destDir, {$plugin['deleteSource']}) return ".(int)$r, 'debug');

		 			if (!empty($r)) {
		 				$connector->log("changement propriété dir: avant = '{$connector->getProp('dir')}', maintenant = '$destDir'");

		 				$connector->setProp('dir', $destDir);
		 				static::log("Uncompressing $srcFile in $destDir successfuly");
                        //dump($connector);
		 			}
				} else static::log("Param file not defined", 'warn');
			} else static::log("Param connector/dir or connector plugin/file not defined", 'err');

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
        $obj  = $event->getObj();

        if ($obj->getStopOnError()) {
            return false;
        }


        $plugin= self::getPluginParams($event);
        if (empty($plugin)) {
            return;
        }

        $r = false;



        /** @var $obj EaiConnectorFile */
        if (!isset($obj->plugins[static::PLUGIN_TYPE]) or $obj->getWay() != Esb::WAY_OUT or $obj->plugins[static::PLUGIN_TYPE]['server'] === 'disabled') {
            return true;
        }

        if(strpos($plugin['archiveFile'], '/')===false) {
            $plugin['archiveFile']= Esb::fullPath($plugin['archivePath']).$plugin['archiveFile'];
        }

        try {
            $r = static::_archiveCompress($plugin['archiveFile'], $plugin['srcFile']);
            $event->getObj()->log("_compress: ".implode('::',$plugin));
        } catch (Exception $e) {
            $obj->fault($e->getMessage());
        }
    }

}

?>