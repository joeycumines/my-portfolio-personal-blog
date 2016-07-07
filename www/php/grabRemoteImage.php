<?php
	//if get url is set, then we download and save this image. Only works for admins, for security reasons.
	
	session_start();
	require('../php/db_connection.php');
	if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		http_response_code (401);
		echo('Unauthorized (401)');
		die();
	}
	
	$result = array();
	$result['success'] = false;
	$result['imageId'] = intval($_GET["imageId"]);
	$target_file = null;
	try {
		if(isset($_GET["url"]) && isset($_GET["imageId"])) {
			$root = getcwd();
			$target_dir = $root.'/../'.$DB_IMAGE_LOCATION;
			
			$tempP = explode('/', $_GET["url"]);
			$tempP = explode('/', $tempP[count($tempP)-1]);
			$tempP = explode('?', $tempP[0]);
			$tempP = explode('.', $tempP[count($tempP)-1]);
			$fT = 'png';
			if (count($tempP) > 1)
				$fT = $tempP[count($tempP)-1];
			if (count($fT) > 10)
				$fT = 'png';
			$newFName = date("Ymd") . '-' . uniqid() . '.' . $fT;
			
			$target_file = $target_dir . $newFName;
			
			file_put_contents($target_file, file_get_contents($_GET["url"]));
			
			$check = getimagesize($target_file);
			if($check !== false) {
				$result['success'] = true;
				$result['link'] = $DB_IMAGE_LOCATION.$newFName;
			} else {
				unlink($target_file);
			}
		}
	} catch (Exception $e) {
		$result['success'] = false;
		if ($target_file != null) {
			unlink($target_file);
		}
	}
	
	echo json_encode($result);
	
?>