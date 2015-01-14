<?php
//include_once '/filer/www/dpam.com/httpdocs/shell/esb/esb.php';
include_once '../../esb.php';
//TODO use this instead of import_log.php
$groupDir = Esb::WORKBASE.'partners/colombus/';
$taskDir  = $groupDir.'colombus_out_log/';

//On se connecte au FTP, et on récupère la liste des fichier
$ftp = array('server'            => "95.143.69.197"
           , 'user'              => "colombus_out_log"
           , 'password'          => "TG4521jhk"
           , 'remoteDir'         => ''
           , 'searchFile'        => "log"   //indique par quoi les fichiers cible doivent commencer (non sensible à la casse)
           , 'deleteSourceAfter' => 1   //TODO Attention 1 seulement en environnement de prod
);
$routes = array($groupDir.'bt1/' => 'bto.*\.paq'
            //, $groupDir.'ob/'  => 'DPAM_OPENBAR_.*\.TXT'
);

$imports = Esb::importFtp($taskDir, $routes, $ftp, true);

//dump("Imports: $taskDir", count($imports));