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
	if(!$facebook::userIsAdmin()) {
		header('HTTP/1.0 401 Unauthorized');
		$response_array['is_admin'] = false;
	}
	else {
		$response_array['is_admin'] = true;
		
		if(!isset($_POST)) {
			header('HTTP/1.1 405 Method Not Allowed');
			$response_array['has_post'] = false;
		}
		else {
			$response_array['has_post'] = true;
			
			if(
				!isset($_POST['eventfbid']) || !isset($_POST['userfbid']) || !isset($_POST['actually_attended']) || !isset($_POST['benefit']) || empty($_POST['eventfbid']) || empty($_POST['userfbid']) || empty($_POST['benefit'])) {
				header('HTTP/1.1 400 Bad Request');
				$response_array['has_fields'] = false;
			}
			else {
				header('HTTP/1.1 200 OK');
				$response_array['has_fields'] = $_POST;
				$response_array['attend_query'] = $facebook->attendUserToBenefit($_POST['userfbid'], $_POST['eventfbid'], $_POST['benefit'], $_POST['actually_attended']);
			}
			
		}
		
	}

}

echo json_encode($response_array);