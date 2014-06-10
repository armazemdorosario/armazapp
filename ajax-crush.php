<?php
header('Content-type: application/json');
$response_array = array();
$loader = require 'vendor/autoload.php';
use armazemapp\FacebookAdapter;
$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();

if(!$current_user) {
	header('HTTP/1.0 401 Unauthorized');
	$response_array['is_logged'] = false;
}
else {
	$response_array['is_logged'] = true;
	if(!isset($_POST)) {
		header('HTTP/1.1 405 Method Not Allowed');
		$response_array['has_post'] = false;
	}
	else {
		$response_array['has_post'] = true;
		if(!isset($_POST['target_userfbid']) || !isset($_POST['status']) || empty($_POST['target_userfbid'])) {
			header('HTTP/1.1 400 Bad Request');
			$response_array['has_fields'] = false;
		}
		else {
			header('HTTP/1.1 200 OK');
			$response_array['has_fields'] = $_POST;
			$response_array['attend_query'] = $facebook->usersCrush($_POST['target_userfbid'], $_POST['status']);
		}
	}
}

echo json_encode($response_array);