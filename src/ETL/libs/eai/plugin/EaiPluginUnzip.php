<?php
/**
 * Manage the in>connector>plugins>plugin type='unzip'
 * @uses EaiConnectorSystem
 * @author tbondois
\*/
class EaiPluginUnzip extends EaiPluginArchive
{
		const PLUGIN_TYPE = 'unzip';

    /**
     * Méthode qui permet de décompresser un fichier zip $file dans un répertoire de destination $path
     * et qui retourne un tableau contenant la liste des fichiers extraits
     *
     * @param string $file chemin complet du fichier source
     * @param string $path dossier de destination des fichiers décompressés
     * @param boolean $deleteZip si est égal à true, on essaie d'effacer le fichier zip d'origine $file
     * @return array liste des fichiers décompressés (vide si erreurs)
     */
    public function _archiveUnCompress($file, $path = '', $deleteSrcFile = false)
    {
        $tab_liste_fichiers = array(); //Initialisation

        $zip = zip_open($file);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) { //Pour chaque fichier contenu dans le fichier zip

                if (zip_entry_filesize($zip_entry) > 0) {
                    $complete_path = $path.dirname(zip_entry_name($zip_entry));

                    /*On supprime les éventuels caractères spéciaux et majuscules*/
                    $nom_fichier = zip_entry_name($zip_entry);
                    $nom_fichier = strtr($nom_fichier,"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
                    $nom_fichier = strtolower($nom_fichier);
                    $nom_fichier = preg_replace('[^a-zA-Z0-9.]','-',$nom_fichier);//TODO changer ereg_replace deprecated

                    /*On ajoute le nom du fichier dans le tableau*/
                    array_push($tab_liste_fichiers,$nom_fichier);

                    $complete_name = $path.$nom_fichier; //Nom et chemin de destination

                    if(!file_exists($complete_path)) {
                        $tmp = '';
                        foreach(explode('/',$complete_path) AS $k) {
                            $tmp .= $k.'/';

                            if (!file_exists($tmp)) {
                                mkdir($tmp, 0755);
                            }
                        }
                    }

                    /*On extrait le fichier*/
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $fd = fopen($complete_name, 'w');

                        fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));

                        fclose($fd);
                        zip_entry_close($zip_entry);
                    }
                }
            }

            zip_close($zip);

            /*On efface éventuellement le fichier zip d'origine*/
            if ($deleteSrcFile === true)
                unlink($file);
        }

        return $tab_liste_fichiers;
    }


    public function _archiveCompress($archiveFile, $srcFiles,$overwrite=true)
    {
        if (!is_array($srcFiles)) {
            $srcFiles= array($srcFiles);
        }

        $zip = new ZipArchive();

        if ($zip->open($archiveFile,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE)!==TRUE) {
            throw new Exception("Cannot create archive file {$filename}");
        }



        foreach ($srcFiles as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            } else {
                throw new Exception("File {$file} not exists");
            }
        }

        $zip->close();
    }

}

?>