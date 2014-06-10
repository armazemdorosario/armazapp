<?php
$loader = require 'vendor/autoload.php';

use armazemapp\Locale;
use armazemapp\FacebookAdapter;
use armazemapp\Mail;
use armazemapp\Router;

$locale = new Locale('pt_BR', 'armazemapp');
$facebook = new FacebookAdapter();
$user = $facebook->getUser();
$current_user = $facebook->getUser();
$router = new Router();

if(!$facebook->isUserOver18() || !$facebook->userSignedUp() || !isset($_GET['eventfbid']) || !isset($_GET['benefit_type'])) {
	header("Location: index.php");
	die();
}

include_once 'views/head.phtml';
include_once 'views/navbar.phtml';
include_once 'views/header.phtml';

$eventfbid = trim($_GET['eventfbid']);
$benefit_type = filter_var($_GET['benefit_type'], FILTER_VALIDATE_INT);
$benefit_results = FacebookAdapter::getBenefitInfo($eventfbid, $benefit_type);

if(!isset($benefit_results) || !$benefit_results || empty($benefit_results) || count($benefit_results)<1) {
?>
<div class="alert alert-danger">
	<h3>Benefício não encontrado</h3>
	<p>Por favor, volte ao <a href="/">início do aplicativo</a> para ver todos os eventos.</p>
</div>
<?php
}
else {
	$benefit = $benefit_results[0];
	$claimed = FacebookAdapter::claimedBenefit($benefit_type, $eventfbid);
	$isUserChosen = FacebookAdapter::isUserChosen($benefit_type, $eventfbid);
	extract($benefit);
	include_once "views/benefit-$benefit_type-item.phtml";
}

include 'views/foot.phtml';