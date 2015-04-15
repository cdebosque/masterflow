Usage : ?cdm=[cdmPath]&type=[import|export]&dump=1
<pre>
<?php
include_once 'esb.php';

$name = Esb::GET('cdm');
$type = Esb::GET('type', 'import');
if($name){
	echo "Launching display for cdm $name :";
	echo "Resultat :<hr>";
	echo htmlentities(Esb::helper_getMapping($name, $type));
}
?>
</pre>