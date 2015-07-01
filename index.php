<?php
/**
 * Eventzapp public application handler file
 *
 * @package Eventzapp
 */
use Eventzapp\Debugger;
session_start();
require 'vendor/autoload.php';
$dotenv = Dotenv::load(__DIR__);
if('production'===getenv('ENV')) {
	error_reporting(0);
	ini_set('display_errors', 0);
	ini_set('safe_mode', 'off');
}
try {
	$app = new Eventzapp\App(__DIR__);
	$app->run();
}
catch(\Eventzapp\Exception $ex) {
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<link rel="dns-prefetch" href="https://graph.facebook.com" />
		<meta http-equiv="refresh" content="10" />
		<meta http-equiv="x-dns-prefetch-control" content="on" />
		<link rel="prerender" href="./" />
		<style><!-- body {font-family: 'Courier New', Courier; position: relative; background-color: #000000; color: #FFFFFF; }div {width: 30%; margin-left: auto; margin-right: auto; margin-top: 20%;}h1 {font-weight: normal;}pre {display: none;} --></style>
	</head>
	<body>
		<div>
			<h1>Armaria!<br />Nosso app n&atilde;o t&aacute; funfando.</h1>
			<p>Nossa equipe já foi informada do biziu... Por favor, dê uma atualizada na página e tente de novo ou volte mais tarde.</p>
			<?php  ?>
		</div>
	</body>
	<?php Debugger::log('App response: ' . $ex->getMessage() . ' ' . $ex->getCode()); ?>
</html>
<?php
}
