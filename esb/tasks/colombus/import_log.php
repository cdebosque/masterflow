<pre>
<?php
include_once '/filer/www/dpam.com/httpdocs/shell/esb/esb.php';
//include_once '../../esb.php';

$workDir = Esb::ROOT.'var/partners/colombus/colombus_out_log/';

//On se connecte au FTP, et on récupère la liste des fichier
$ftp = array('server'            => "95.143.69.197"
           , 'user'              => "colombus_out_log"
           , 'password'          => "TG4521jhk"
           , 'port'              => 21
           , 'passive'           => 1
           , 'transfertMode'     => FTP_BINARY
           , 'remoteDir'         => ''
           , 'searchFile'        => "log"   //indique par quoi les fichiers cible doivent commencer
           , 'override'          => 0   //TODO mode 2 qui renomme a coté (pas utile ici)   //Mettre a 1 en production
           , 'deleteSourceAfter' => (Esb::ENV == 'prod' ? 1 : 0)    //Attention seulement en environnement de prod
           , 'localDir'          => $workDir.'ftp/'
           , 'remotelist'        => array()
           , 'success'           => array()
           , 'ignored'           => array()
           , 'fail'              => array()
           , 'connection'        => null
);
$unzip = array('extension' => ".zip"
             , 'destDir'   => $workDir.'unzip/'
             , 'success'   => array()
             , 'ignored'   => array()
             , 'fail'      => array()
);

$interfaces = process($ftp, $unzip);

/**
 * TODO variabiliser les interfaces a lancer suivant le nom du fichier pour utiliser cette fonction en standard
 * @param $ftp
 * @param $unzip
 * @return array
 */
function process($ftp, $unzip)
{

    $interfaces = array();


    $ftpProcessedDir = $ftp['localDir']."processed/";
    $queueDir      = $unzip['destDir'].'queue/';
    $processingDir = $unzip['destDir'].'processing/';
    $processedDir  = $unzip['destDir'].'processed/';

    if (!file_exists($ftpProcessedDir)) {
        mkdir($ftpProcessedDir, 0777, true);
    }
    if (!file_exists($processingDir)) {
        mkdir($processingDir, 0777, true);
    }
    if (!file_exists($processedDir)) {
        mkdir($processedDir, 0777, true);
    }

    if (strlen($ftp['server']  ) > 0
        && strlen($ftp['user']    ) > 0
        && strlen($ftp['password']) > 0
    ) {
        $localDirExist = false;
        if (file_exists($ftp['localDir'])) {
            $localDirExist = true;
        } else {
            if (mkdir($ftp['localDir'], 0777, true)) {
                $localDirExist = true;
                dump('Creating local directory recursively '.$ftp['localDir']);
            } else {
                dump("Bad local mkdir '{$ftp['localDir']}'", 'err');
            }
        }
        if ($localDirExist) {

            $ftp['connection'] = ftp_connect($ftp['server'], $ftp['port']);
            if ($ftp['connection']) {
                if (ftp_login($ftp['connection'], $ftp['user'], $ftp['password'])) {
                    if (ftp_pasv($ftp['connection'], (bool)$ftp['passive'])) {
                        if (strlen($ftp['remoteDir']) == 0
                            || ftp_chdir($ftp['connection'], $ftp['remoteDir'])
                        ) {
                            $ftp['remotelist'] = ftp_nlist($ftp['connection'], '.');
                            foreach($ftp['remotelist'] as $num => $remoteFile) {
                                $localFile  = $ftp['localDir'].$remoteFile;

                                if ( ($ftp['override'] || !file_exists($localFile))
                                    && stripos($remoteFile, $ftp['searchFile']) === 0
                                    && strripos($remoteFile, $unzip['extension']) == strlen($remoteFile)-strlen($unzip['extension'])
                                ) {

                                    if (ftp_get($ftp['connection'], $localFile, $remoteFile, $ftp['transfertMode']) ) {

                                        $ftp['success'][$num]   = $localFile;
                                        dump("successfuly FTP downloaded file $remoteFile to $localFile");

                                        if($ftp['deleteSourceAfter'] /*&& Esb::ENV == 'prod'*/) {
                                            $deleting = ftp_delete($ftp['connection'], $remoteFile);
                                            if($deleting) {
                                                dump("ftp_delete ok for $remoteFile");
                                            } else {
                                                dump("erreur ftp_delete $remoteFile");
                                            }
                                        }
                                    } else {
                                        $ftp['fail'][$num]   = $remoteFile;
                                        dump("connecting : bad ftp_get(connection, '$localFile' << $remoteFile')", 'err');
                                    }
                                } else {
                                    $ftp['ignored'][$num]   = $remoteFile;
                                    dump("checking : local file $localFile already exist (mode override = ".(int)$ftp['override'].") or invalid filename / extension. , next"
                                        /*, $remoteFile
                                        , ($ftp['override'] || !file_exists($localFile))
                                        , stripos($remoteFile, $ftp['searchFile'])
                                        , strripos($remoteFile, $unzip['extension']) == strlen($remoteFile)-strlen($unzip['extension'])*/
                                    );
                                }
                            }//foreach $remoteFile

                        } else dump("connecting : bad ftp_chdir(connection, '{$ftp['remoteDir']}')", 'fatal');
                    } else dump("connecting : bad ftp_pasv(connection, '{$ftp['passive']}')", 'fatal');
                } else dump("connecting : bad ftp_login(connection, '{$ftp['user']}', password)", 'fatal');

                ftp_close($ftp['connection']);

            } else  dump("connecting : bad ftp_connect({$ftp['server']}, '{$ftp['port']}')", 'fatal');
        }
    }

    dump("ftp status:", $ftp);



    //traiter tous les fichiers dans to_deal, pas seulement la liste du ftp  intelligement
    // car si un vieux fichier openbar est retraité, on écrase tout le stock
    //faire la décompression et le traitement dans la meme boucle

    $localDirExist = false;

    if (!empty($ftp['success'])) {
        if (file_exists($queueDir)) {
            $localDirExist = true;
        } else {
            if (mkdir($queueDir, 0777, true)) {
                $localDirExist = true;
                dump('Creating local directory recursively '.$queueDir);
            } else {
                dump("Bad local mkdir '{$queueDir}'", 'err');
            }
        }

        if ($localDirExist) {

            foreach ($ftp['success'] as $num => $filePath) {

                $file = end(explode('/', $filePath)); //nom de fichier sans les dossiers

                if (strripos($filePath, $unzip['extension']) == strlen($filePath)-strlen($unzip['extension'])) {
                    $unzipFiles = EaiPluginUnzip::unzip($filePath, $queueDir, false);

                    if (empty($unzipFiles)) {
                        $unzip['fail'][$num] = $filePath;
                    } else {
                        $unzip['success'] = array_merge($unzip['success'], $unzipFiles);

                        if(!file_exists($ftpProcessedDir.$file)) {
                            chmod($filePath, 0666);
                            $renamed = rename($filePath, $ftpProcessedDir.$file);
                            if (!$renamed) {
                                dump("erreur rename ftp->unzip ($filePath, {$ftpProcessedDir}$file)");
                            }
                        } else {
                            if(!$ftp['deleteSourceAfter']){
                                //on supprime pas la source ftp à chaque fois, donc normal que le fichier existe déja dans les processed
                                unlink($filePath);
                                dump("unlink($filePath) : on supprime pas la source ftp à chaque fois, donc normal que le fichier existe déja dans les processed");
                            } else {    //on renomme pour garder une copie
                                dump("warning : already exist $ftpProcessedDir.$file, rename ".$ftpProcessedDir.$file.time());
                                rename($filePath, $ftpProcessedDir.$file.time());
                            }
                        }
                    }
                } else {
                    $unzip['ignored'][$num] = $filePath;
                }
            }
        }
    }

    dump("unzip status:", $unzip);





    if (!empty($unzip['success'])) {


        foreach($unzip['success'] as $num => $file) {   //pour chaque fichier dans unzip-destDir


            $renamed = rename($queueDir.$file, $processingDir.$file);
            if (!$renamed) {
                dump("Erreur rename($queueDir.$file, $processingDir)");
                //$processingDir = $queueDir;//panic modeX
            }

            $identifier = false;
            if (stripos($file, 'bt') === 0 ) {
                $identifier = "partners/colombus/bt1";
            } elseif (stripos($file, 'dpam-openbar') === 0 ) {
                //$identifier = "partners/colombus/ob";
                $identifier = false;
            }

            if ($identifier) {
                $config = "
                    <interface>
                        <in>
                            <connection>
                                <workflow>0</workflow>
                                <dir>$processingDir</dir>
                                <file>$file</file>
                            </connection>
                        </in>
                    </interface>";

                $cmd = 'php '.Esb::ROOT.'cli_launch.php --id="'.$identifier.'" --config="'.str_replace(array("  ", "\r", "\n", PHP_EOL), '',  $config).'"';
                $interfaces[$num] = array('identifier' => $identifier, 'dir-file' => $processingDir.$file, 'exec' => $cmd);
                //dump($interfaces);

                $last = exec($cmd);

                dump("dernier print:", $last);
                if (stripos($last,'stop') !== false
                 || stripos($last,'error') !== false
                ) {
                  $finished = false;///Esb::start($identifier, $config);
                } else {
                    $finished = true;
                }
                $interfaces[$num]['finished'] = $finished;
                $interfaces[$num]['lastPrint'] = $last;

                if ($finished) {

                    if (is_file($processingDir.$file)) {

                        if(!file_exists($processedDir.$file)) {

                            $renamed = rename($processingDir.$file, $processedDir.$file);
                            if (!$renamed) {
                                dump("error : rename processing->processed ($queueDir $file, $processingDir)");
                            }
                        } else {
                            dump("warning : already exist $processedDir $file, deleting $processingDir $file");
                            @chmod($processingDir.$file, 0666);
                            $deleted = unlink($processingDir.$file);
                            if(!$deleted) {
                                dump("error : deleting $processingDir $file");
                            }
                        }
                    } else {
                        dump("warning : not is_file $processingDir.$file");
                    }
                }
            }
        }
    } else {
        dump("no unzip files success");
    }
    dump("interface status:", $interfaces);

    return $interfaces;
}