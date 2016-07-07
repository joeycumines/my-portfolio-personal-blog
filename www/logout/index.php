<?php
	session_start();
	require('../php/pageRedirect.php');
	
	$sessionName = session_name();
	$sessionCookie = session_get_cookie_params();
	session_destroy();
	setcookie($sessionName, false, $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure']);
	
	echo('You have been logged out if you were not already.');
	
	if (isset($_GET['redirectUrl']))
		echoRedirectPage($_GET['redirectUrl']);
?>