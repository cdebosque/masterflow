<?php
/**
 * Fichier a utiliser pour lancer les cron en mode console
 */

include_once 'esb.php';

$matching = array('id:'        => 'identifier'   //obligatoire
                , 'config::'   => 'config'    //optionnel
);//matching entre les paramètres shell => esb
$params   = array('identifier' => null
                , 'config'     => null
);//variable passées à l'esb

$request  = getopt(null, array_keys($matching));

   //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //
  // exemple  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //
 // php /home/tbondois/www/dev_esb/cli_launch.php --id="partners/colombus/bt1" --config="<interface><in><connection><dir>/home/tbondois/www/dev_esb/var/feeds/colombus/colombus_out_log/unzip/processing</dir><file>bto20121214657.paq</file></connection></in></interface>"
//  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //  //

foreach ($matching as $shellParam => $esbParam) {
    $var = rtrim($shellParam, ':'); //on supprime les : a la fin
    $val = isset($request[$var]) ? $request[$var] : null;
    $params[$matching[$shellParam]] = $val;
}
if (isset($params['config']) && count($params['config'])) {
    var_dump($params);
}
if ($params['identifier']) {
    Esb::start($params['identifier'], $params['config']);
}


?>