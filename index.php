<?php
/**
 * Eventzapp public application handler file
 *
 * @package Eventzapp
 */
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
<!DOCTYPE html><html><head><meta charset="utf-8" /><style><!-- body {font-family: 'Courier New', Courier; position: relative; background-color: #000000; color: #FFFFFF; }div {width: 30%; margin-left: auto; margin-right: auto; margin-top: 20%;}h1 {font-weight: normal;}pre {display: none;} --></style></head><div><h1>Armaria!<br />Nosso app n&atilde;o t&aacute; funfando.</h1><p>
<?php
	switch ($ex->getCode()) {
		case 83:
		case 86:
			session_destroy();
			echo htmlentities('Não estamos conseguindo nos conectar com o danado do Facebook... Dê uma atualizada na página e tente de novo.');
			break;
		
		default:
			Debugger::log('App response: ' . $ex->get);
			break;
	}
?>
</p></div></body></html>
<?php
}
