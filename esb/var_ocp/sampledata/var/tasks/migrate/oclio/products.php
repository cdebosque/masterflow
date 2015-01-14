<pre><?php
// include_once '../../../../libs/funcs/head.php';
include_once '../../../../esb.php';

$eaiHandler = new EaiHandler('migrate/oclio/products');
$eaiHandler->run();
dump('$_SESSION'
		, $_SESSION
		, '-------------------- Rapport : $eaiHandler -------------------- -'
		, $eaiHandler
		);
$eaiHandler = null;
?>/End
</pre>