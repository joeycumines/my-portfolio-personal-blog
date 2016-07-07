<?php require('php/db_connection.php'); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('templates/meta.php'); ?>
		<?php require('templates/icons.php'); ?>
		<title>404</title>
		<?php require('templates/core_css.php'); ?>
	</head>
	<body>
		<?php require('templates/menubar.php'); ?>
		<div class="jumbotron">
			<div class="container text-center">
				<h1>404 - Page Not Found</h1>
				<p>Couldn't find what you were looking for! Try another link?</p>
			</div>
		</div>
		<?php require('templates/footer.php'); ?>
		<?php require('templates/core_js.php'); ?>
	</body>
</html>