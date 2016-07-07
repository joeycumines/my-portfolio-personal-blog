<?php
	session_start();

	if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		http_response_code (401);
		echo('Unauthorized (401)');
		die();
	}
	require('../../php/db_connection.php');
	
	$pdo = getNewPDO();
	
	$postId = isset($_GET['id']) ? $_GET['id'] : null;
	$confirmDel = isset($_GET['confirmDel']) ? $_GET['confirmDel'] : null;
	
	$content = '';
	
	if (!empty($postId) && !empty($confirmDel)) {
		//if we have confirmed deletion
		$message = '';
		
		try {
			//delete
			if (runUpdatePrepared($pdo, 'DELETE FROM posts WHERE id = :id;', array(':id'=>$postId)) > 0) {
				$message = 'Deletion of post succeeded!';
			} else {
				$message = 'Deletion failed, connection ok but check post ID.';
			}
		} catch (PDOException $e) {
			$message = 'Database error.';
		}
		
		$content = ('
				<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
					<p>'.$message.'</p>
					<a href="../">Go back to Admin Dashboard</a>
				</div>
');
	} else if (!empty($postId)) {
		//we have not confirmed deletion, but we have a post.
		//html form to delete.
		//get the post title, so that we can ask with the title.
		$rows = runQueryPrepared($pdo, 'SELECT title FROM posts WHERE id = :id;', array(':id'=>$postId));
		$postTitle = null;
		$postExists = false;
		foreach ($rows as $row) {
			$postExists = true;
			$postTitle = $row['title'];
			break;
		}
		//we exit if this id does not exist
		if (!$postExists)
			$content = '404';
		else
			$content = ('
				<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
					<form>
						<input type="hidden" name="confirmDel" value="true">
						<input type="hidden" name="id" value="'.$postId.'">
						<p>Are you sure you wish to delete "'.$postTitle.'" forever?</p>
						<div class="form-group">
							<a href="../editor/?id='.$postId.'">Click Here</a>&nbsp;if you just wanted to make it no longer published.
						</div>
						<div class="form-group">
							<input type="submit" value="CONFIRM DELETE" class="btn btn-danger">
							<a href="../" class="btn btn-primary">Go Back</a>
						</div>
					</form>
					
				</div>
');
	} else {
		$content = '404';
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('../../templates/meta.php'); ?>
		<?php require('../../templates/icons.php'); ?>
		<title>Admin Tools</title>
		<?php require('../../templates/core_css.php'); ?>
	</head>
	<body>
		<div class="container">
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Delete Post</strong></div>
				<div class="panel-body">
					<?php echo($content); ?>
				</div>
			</div>
		</div>
		<?php require('../../templates/core_js.php'); ?>
	</body>
</html>