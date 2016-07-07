<?php

	function enableDebugging() {
		ini_set('display_errors', 'On');
		error_reporting(E_ALL | E_STRICT);
	}
	
	function disableDebugging() {
		ini_set('display_errors', 'Off');
		error_reporting(0);
	}
?>