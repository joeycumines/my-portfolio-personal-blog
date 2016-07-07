<?php
	/*
		Individual blog style posts are served from here.
	*/
	
	session_start();
	require('../php/db_connection.php');
	require('../php/pageRedirect.php');
	
	$pdo = getNewPDO();
	
	//load the post id we are working with
	$id = isset($_GET['id']) ? $_GET['id'] : null;
	$postData = null;
	
	if (!empty($id)) {
		$rows = runQueryPrepared($pdo, 'SELECT * FROM posts WHERE id = :id;', array(':id'=>$id));
		foreach($rows as $row) {
			$postData = $row;
			break;
		}
	}
	
	//if we are not published and not admin then we set it to null.
	if ($postData != null && $postData['published'] != 1 && (!isset($_SESSION['admin']) || $_SESSION['admin'] != true))
		$postData = null;
	
	if ($postData == null) {
		//redirect to a 404 page.
		echoRedirectPage(getRootUrl().'404.php');
		die();
	}
	
	//reaches here only if we have a post to display
	
	//find the tags for this post, and populate a link list to search for related articles.
	$tagLinks = '
					<div class="list-group">
	';
	$rows = runQueryPrepared($pdo, 'SELECT tagname,
	(SELECT COUNT(*) FROM tags t2 WHERE t2.tagname = t1.tagname AND t2.id IN (SELECT id FROM posts p1 WHERE p1.id = t2.id AND p1.published = 1)) AS amount
	FROM tags t1 WHERE t1.id = :id
	ORDER BY tagname asc;', 
			array(':id'=>$postData['id']));
	foreach($rows as $row) {
		$tagLinks.= '
					<a href="'.getRootUrl().'search/?tag='.urlencode($row['tagname']).'" class="list-group-item">'.$row['tagname'].'<span class="badge">'.$row['amount'].'</span></a>
		';
	}
	$tagLinks.= '
					</div>
	';
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('../templates/meta.php'); ?>
		<?php require('../templates/icons.php'); ?>
		<title><?php echo($postData['title']); ?></title>
		<?php require('../templates/core_css.php'); ?>
	</head>
	<body>
		<?php
			//if we are an admin, echo the link to open in editor
			if (isset($_SESSION['admin']) && $_SESSION['admin'] == true)
				echo('
		<div class="container">
			<a class="btn btn-primary" href="'.getRootUrl().'admin/editor/'.(!empty($id) ? '?id='.$id : '').'">Open in Editor</a>
			<a class="btn btn-danger" href="'.getRootUrl().'admin/delete/'.(!empty($id) ? '?id='.$id : '').'">Delete this Post</a>
		</div>
				');
		?>
		<?php require('../templates/menubar.php'); ?>
		<?php require('../templates/widget_info.php'); ?>
		<div class="container">
			<div class="articleHeader text-center">
				<h1><?php echo($postData['title']); ?></h1>
				<p>Last update at AEST <?php echo(date("F j, Y, g:i a", strtotime($postData['date']))); ?></p>
			</div>
			<div class="row">
				<div class="col-md-10 col-lg-10">
					<div id="articleBody">
						<?php echo($postData['html']); ?>
					</div>
				</div>
				<div class="col-md-2 col-lg-2">
					<h4>Tags</h4>
					<?php echo($tagLinks); ?>
				</div>
			</div>
		</div>
		<?php require('../templates/footer.php'); ?>
		<?php require('../templates/core_js.php'); ?>
	</body>
</html>
