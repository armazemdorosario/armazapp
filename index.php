<?php
/**
 * Eventzapp public application handler file
 *
 * @package Eventzapp
 */
use Eventzapp\Debugger;
use Eventzapp\Exception;

session_start();
require 'vendor/autoload.php';

if(class_exists('Dotenv')) {
	$dotenv = Dotenv::load(__DIR__);
}
elseif(class_exists('Dotenv\Dotenv')) {
	try {
		$dotenv = new Dotenv\Dotenv(__DIR__);
		$dotenv->load();
		$dotenv->required(['APP_ID', 'APP_SECRET', 'APP_URL', 'LOGIN_URL']);
		#$dotenv->required('ENV')->allowedValues(['development', 'staging', 'production']);
	}
	catch(\RuntimeException $e) {
		Debugger::log('Problem with .env file: ' . $e->getMessage() . ' ' . $e->getCode());
		die();
	}
}
else {
	Debugger::log('.env file is missing or Dotenv class was not loaded. Please: <ol><li>create or check .env file on ' . __DIR__ . '</li><li>Check your Dotenv version. Version 1 loads \Dotenv class, version 2 loads \Dotenv\Dotenv class.</li></ul>');
	die();
}

try {
	$app = new Eventzapp\App(__DIR__);
	$app->run();
}
catch(Exception $ex) {
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<link rel="dns-prefetch" href="https://graph.facebook.com" />
		<meta http-equiv="refresh" content="10" />
		<meta http-equiv="x-dns-prefetch-control" content="on" />
		<style><!-- body {font-family: 'Courier New', Courier; position: relative; background-color: #000000; color: #FFFFFF; }div {width: 30%; margin-left: auto; margin-right: auto; margin-top: 20%;}h1 {font-weight: normal;}pre {display: none;} --></style>
	</head>
	<body>
		<div>
			<h1>Armaria!<br />Nosso app n&atilde;o t&aacute; funfando.</h1>
			<p>Nossa equipe já foi informada do biziu... Por favor, dê uma atualizada na página e tente de novo ou volte mais tarde.</p>
			<?php  ?>
		</div>
	</body>
	<?php
	$message = 'App response: ' . $ex->getMessage() . ' ' . $ex->getCode();
	Debugger::log($message);
	?>
</html>
<?php
	die();
}
