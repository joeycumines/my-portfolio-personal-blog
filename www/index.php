<?php
	/*
		This is our landing page.
	*/
	session_start();
	require('php/db_connection.php');
	
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('templates/meta.php'); ?>
		<?php require('templates/icons.php'); ?>
		<title>Portfolio</title>
		<?php require('templates/core_css.php'); ?>
	</head>
	<body>
		<?php require('templates/menubar.php'); ?>
		<?php require('templates/widget_info.php'); ?>
		<div class="jumbotron">
			<div class="container text-center">
				<?php require('templates/widget_me.php'); ?>
			</div>
		</div>
		<?php require('templates/widget_portfolio.php'); ?>
		<?php require('templates/footer.php'); ?>
		<?php require('templates/core_js.php'); ?>
	</body>
</html>

