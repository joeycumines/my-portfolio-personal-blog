<?php
	session_start();
	require('../php/db_connection.php');
	if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		http_response_code (401);
		echo('Unauthorized (401)');
		die();
	}
	if(isset($_POST["submit"])) {
		echo('<!DOCTYPE html><html><body>');
		$root = getcwd();
		$target_dir = $root.'/../'.$DB_IMAGE_LOCATION;
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
				} else {
				echo "File is not an image.";
				$uploadOk = 0;
			}
		}
		
		//set target file to our new path
		$tempP = explode('.', $target_file);
		$fT = $tempP[count($tempP)-1];
		$newFName = date("Ymd") . '-' . uniqid() . '.' . $fT;
		$target_file = $target_dir . $newFName;
		//echo($target_file);
		
		// Check if file already exists
		if (file_exists($target_file)) {
			echo "Sorry, file already exists.";
			$uploadOk = 0;
		}
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 2000000) {
			echo "Sorry, your file is too large.";
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Sorry, your file was not uploaded.";
			// if everything is ok, try to upload file
			} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				//set the file to the lastUploaded in session.
				$_SESSION['UP_IMAGE'] = $newFName;
				echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded. Please close this page, the previous page should have your file.<script>window.close();</script>";
				} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
		
		echo('</body></html>');
		die();
	}
?>

<!DOCTYPE html>
<html>
	<body>
		<form action="addImage.php" method="POST" enctype="multipart/form-data">
			Select image to upload:
			<input type="file" name="fileToUpload" id="fileToUpload">
			<input type="submit" value="Upload Image" name="submit">
		</form>
	</body>
</html>