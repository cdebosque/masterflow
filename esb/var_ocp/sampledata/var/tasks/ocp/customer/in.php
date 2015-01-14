<pre><?php
include_once '../../../../esb.php';

$eaiHandler = new EaiHandler('ocp/default/customer/in');
$eaiHandler->run();
dump('$_SESSION'
		, $_SESSION
		, '-------------------- Rapport : $eaiHandler ---------------------'
		, $eaiHandler
		);
$eaiHandler = null;
?>/End
</pre>