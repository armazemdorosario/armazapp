<?php
$loader = require 'vendor/autoload.php';

use armazemapp\FacebookAdapter;
use armazemapp\Locale;

$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();

if(!$facebook::userIsAdmin()) {
	header("Location: /index.php?accessDeniedFromAdmin=1");
}

$locale = new Locale('pt_BR', 'armazemapp');

if(isset($_POST)) {
	if( isset($_POST['eventfbid']) && isset($_POST['expiration_date']) && isset($_POST['max_num_people_chosen']) ) {
		var_dump(FacebookAdapter::addEvent($_POST));
		#header('Location: /?msg=new_benefit');
	}
}

if (FacebookAdapter::userIsAdmin()) {
	include_once 'views/head.phtml';
	include_once 'views/navbar.phtml';
	include_once 'views/add-benefit.phtml';
	include_once 'views/foot.phtml';
}