<pre>
<?php
error_reporting(E_ALL);
include_once 'esb.php';
// TimeZone utilisée pour l'affichage des dates.
Esb::setTimezone('Europe/Paris');

require_once Esb::LIBS . 'monitor' . DIRECTORY_SEPARATOR . 'Dataflow.php';
$options = getopt("i:");

function WriteTruc($text){
	echo "<h1>" . $text . "</h1>";
}

ini_set('display_errors', '1');
$eaiCode= false;
if (!Esb::isCli() and empty($options) ) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Esb::ETC),
            RecursiveIteratorIterator::CHILD_FIRST);

    $identifiers= array();
    foreach ($iterator as $path) {
        if (!$path->isDir() and strpos($path->__toString(), 'interface.xml')!==false) {
            $idetifierKey= dirname(str_replace(Esb::ETC, '', $path->__toString()));
            $identifiers[$idetifierKey]=array('code'=>$idetifierKey, 'path'=>$path);
        }
    }
    ksort($identifiers);
    //echo '<ul style="padding:0px;margin:0px">';
    foreach ($identifiers as $identifier)
        echo '<b><a href="'.$_SERVER['PHP_SELF'].'?code='.$identifier['code'].'">'.$identifier['code'].'</a></b> '.
             '<a href="'.dirname($_SERVER['PHP_SELF']).'/etc/'.$identifier['code'].'/interface.xml">[View interface.xml]</a>'.
             ''.PHP_EOL;
        //echo '<li  style="padding:0px;margin:0px"><a href="'.$identifier.'">'.$identifier.'</a></li>'.PHP_EOL;
    //echo '</ul>';
    if (isset($_REQUEST['code'])) {
        $eaiCode= $_REQUEST['code'];
    }
} else {
    if (!empty($options['i'])) {
        $eaiCode= $options['i'];
    }
}

if ($eaiCode) {
    echo '<h3>'.$eaiCode.'</h3>';
    Esb::start($eaiCode);
} else {
    echo '<h3>My lord, could you, please, select an interface</h3>';
    echo "<h4>Working with a such profesional is till a real pleasure ;-)</h3>";
    //Esb::start('ocp/init/product_attributes/import_xmlsoap');
}

exit; 
?>
</pre>