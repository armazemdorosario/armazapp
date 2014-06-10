<?php
$loader = require 'vendor/autoload.php';

use armazemapp\FacebookAdapter;
use armazemapp\Locale;

$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();
$current_user_is_admin = $facebook::userIsAdmin();

if(!$current_user_is_admin) {
	header("Location: /index.php?accessDeniedFromAdmin=1");
}

$locale = new Locale('pt_BR', 'armazemapp');

if(isset($_POST) && isset($_POST['fbid']) && isset($_POST['limite']) && isset($_POST['genero'])) {
	FacebookAdapter::addEvent($_POST);
	header('Location: /?msg=new_benefit');
}

else {
	if (FacebookAdapter::userIsAdmin()) {
		include_once 'views/head.phtml';
		include_once 'views/navbar.phtml';
		include_once 'views/add-benefit.phtml';
		include_once 'views/foot.phtml';
	}
}