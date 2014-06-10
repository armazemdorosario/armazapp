<?php
header('Content-type: text/json');

$response_array = array();

$loader = require 'vendor/autoload.php';

use armazemapp\FacebookAdapter;
$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();

if(!$current_user) {
	header('HTTP/1.0 401 Unauthorized');
	#$response_array['is_logged'] = false;
}
else {
	#$response_array['is_logged'] = true;
	if(!$facebook::userIsAdmin()) {
		header('HTTP/1.0 401 Unauthorized');
		#$response_array['is_admin'] = false;
	}
	else {
		#$response_array['is_admin'] = true;
		$response_array = $facebook->searchUser( isset($_GET['query']) ? $_GET['query'] : '' );
	}
}
echo json_encode($response_array);