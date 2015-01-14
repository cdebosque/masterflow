<?php
include_once '/filer/www/dpam.com/httpdocs/shell/esb/esb.php';
//include_once '../../esb.php';
$groupDir = Esb::WORKBASE.'partners/colombus/';
$taskDir  = $groupDir.'colombus_in_cat/';

//On se connecte au FTP, et on récupère la liste des fichier
$ftp = array('server' => "95.143.69.197"
, 'user'              => "colombus_in_cat"
, 'password'          => "tgPO8964T"
, 'remoteDir'         => ''
, 'searchFile'        => "cat"   //indique par quoi les fichiers cible doivent commencer (non sensible à la casse)
, 'deleteSourceAfter' => 1   //Attention 1 seulement en environnement de prod
);
$routes = array($groupDir.'products/'  => 'fa.*\.paq'
, $groupDir.'price/'  => 'pv.*\.paq'
, $groupDir.'cp/'  => 'cp.*\.paq'
, $groupDir.'sy/' => 'sy.*\.paq'
, $groupDir.'cio/' => 'ciorigine\-.*\.txt'
);

$imports = Esb::importFtp($taskDir, $routes, $ftp, true);

//dump("Imports: $taskDir", count($imports));