<?php
	/*
		Login page, where we set $_SESSION['admin'] to true if we succeeded.
	*/
	
	session_start();
	require('../php/db_connection.php');
	require('../php/pageRedirect.php');
	
	/**
		Hash and a return a password using the admin salt.
	*/
	function hashPassword($password) {
		global $ADMIN_SALT;
		return hash('sha256', $password.$ADMIN_SALT);
	}
	
	$errorBox = '';
	
	if (isset($_POST['doLogin']) && $_POST['doLogin'] == true) {
		$password = validate(isset($_POST['password']) ? $_POST['password'] : null);
		
		if ($password != null && hashPassword($password) == $ADMIN_HASH) {
			//set session var to login
			$_SESSION['admin'] = true;
			echoRedirectPage(getRootUrl().'admin/');
		} else {
			$errorBox = '
<div class="alert alert-danger" role="alert">
	<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
	<span class="sr-only">Error:</span>
	Password was incorrect
</div>
			';
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('../templates/meta.php'); ?>
		<?php require('../templates/icons.php'); ?>
		<title>Login</title>
		<?php require('../templates/core_css.php'); ?>
	</head>
	
	<body>
		<div class="container">
			<?php echo($errorBox); ?>
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Admin Login</strong></div>
				<div class="panel-body">
					<form method="POST">
						<input type="hidden" name="doLogin" value="true">
						<label for="inputPassword" class="sr-only">enter secret password</label>
						<input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required autofocus>
						<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
					</form>
				</div>
			</div>
		</div>
		<?php require('../templates/core_js.php'); ?>
	</body>
</html>
