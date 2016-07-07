<?php
	/*
		Admin tools for managing my posts.
		
		Currently just a table displaying the current posts, and access to 
		tools used to manage, create and edit them.
	*/
	
	session_start();
	require('../php/db_connection.php');
	
	//exit if not valid admin
	if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		http_response_code (401);
		echo('Unauthorized (401)');
		die();
	}
	
	//pdo for database operations
	$pdo = getNewPDO();
	$posts = runQueryPrepared($pdo, 'SELECT * FROM posts ORDER BY date desc;', array());
	//build the post list.
	$htmlPosts = "\n";
	foreach($posts as $post) {
		$tags = runQueryPrepared($pdo, 'SELECT * FROM tags WHERE id = :id;', array(':id'=>$post['id']));
		$tagsComma = '';
		foreach ($tags as $tag) {
			if (!empty($tagsComma))
				$tagsComma.=',<br>';
			$tagsComma .= $tag['tagname'];
		}
		$published = $post['published'] == 1 ? '<a href="'.getRootUrl().'posts/?id='.$post['id'].'">yes</a>' : '<a href="'.getRootUrl().'posts/?id='.$post['id'].'">no</a>';
		$htmlPosts.= '
							<tr>
								<td>'.$post['date'].'</td>
								<td>'.$post['title'].'</td>
								<td>'.$tagsComma.'</td>
								<td><a class="label btn-warning" href="editor/?id='.urlencode($post['id']).'">EDIT</a></td>
								<td><a class="label btn-danger" href="delete/?id='.urlencode($post['id']).'">DELETE</a></td>
								<td>'.$published.'</td>
							</tr>
';
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('../templates/meta.php'); ?>
		<?php require('../templates/icons.php'); ?>
		<title>Admin Tools</title>
		<?php require('../templates/core_css.php'); ?>
	</head>
	<body>
		<div class="container">
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Quick Links</strong></div>
				<div class="panel-body">
					<a href="editor/" class="btn btn-info">New Post</a>
					<a href="../logout/?redirectUrl=<?php echo(getRootUrl()); ?>" class="btn btn-danger">Logout</a>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Current Posts</strong></div>
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Date</th>
									<th>Title</th>
									<th>Tags</th>
									<th></th>
									<th></th>
									<th>Published?</th>
								</tr>
							</thead>
							<tbody>
								<?php echo($htmlPosts); ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php require('../templates/core_js.php'); ?>
	</body>
</html>

