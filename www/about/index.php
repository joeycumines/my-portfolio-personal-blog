<?php
	/*
		About me page, uses content from posts called 'about'.
	*/
	session_start();
	require('../php/db_connection.php');
	
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('../templates/meta.php'); ?>
		<?php require('../templates/icons.php'); ?>
		<title>About Me</title>
		<?php require('../templates/core_css.php'); ?>
	</head>
	<body>
		<?php require('../templates/menubar.php'); ?>
		<?php require('../templates/widget_info.php'); ?>
		<div class="container">
			<?php require('../templates/widget_about.php'); ?>
		</div>
		<?php require('../templates/footer.php'); ?>
		<?php require('../templates/core_js.php'); ?>
	</body>
</html>

