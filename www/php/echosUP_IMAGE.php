<?php
	session_start();
	if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		die();
	}
	//echo $_SESSION['UP_IMAGE'], and set it to null
	if (isset($_SESSION['UP_IMAGE']) && !empty($_SESSION['UP_IMAGE'])) {
		echo($_SESSION['UP_IMAGE']);
		$_SESSION['UP_IMAGE'] = null;
	}
?>