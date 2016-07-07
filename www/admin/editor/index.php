<?php
	/*
		This is a basic but effective editing tool, supporting markdown syntax via ckeditor.
		We also have basic image upload tools, and can use the content viewer/unpublished
		flag to preview while writing.
		
		Structure:
		When we load pages the content is taken directly from the html field in the posts table.
		The JavaScript side of things operates independantly, all saving etc is handled in php.
		
		See the JS code for more details about the actual editor.
	*/
	
	session_start();
	if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
		http_response_code (401);
		echo('Unauthorized (401)');
		die();
	}
	
	require('../../php/db_connection.php');
	
	$pdo = getNewPDO();
	
	//get the post id we are working with (it is null if a new post)
	$id = isset($_GET['id']) ? $_GET['id'] : null;
	$id = isset($_POST['id']) ? $_POST['id'] : $id;
	
	//message box for save error will appear if we set this
	$saveError = null;
	
	//if we are saving
	if (isset($_POST['editor1'])) {
		//load the metadata tags into a string array
		$splitTags = explode(',', $_POST['tags']);
		$cleanedTags = array();
		foreach ($splitTags as $key=>$value) {
			$temp = strtolower(validate($value));
			if (!empty($temp))
				array_push($cleanedTags, $temp);
		}
		unset($splitTags);
		
		if (empty($id)) {
			//new post
			try {
				runUpdatePrepared($pdo, 'INSERT INTO posts (title, date, html, published)
				VALUES (:title, :date, :html, IF(:published = 1,TRUE,FALSE));',
				array(':title'=>$_POST['title'], ':date'=>date("Y-m-d H:i:s"), ':html'=>$_POST['editor1'],
				':published'=>((isset($_POST['published']) && $_POST['published']) == 'true' ? 1 : 0) ));
			} catch (PDOException $e) {
				$saveError = $e->getMessage();
			}
		} else {
			//editing a post
			try {
				$vars = array(':id'=>$id,':title'=>$_POST['title'], ':html'=>$_POST['editor1'],
				':published'=>(isset($_POST['published']) && $_POST['published'] == 'true' ? 1 : 0) );
				if (isset($_POST['setDate']) && $_POST['setDate'] == 'true')
					$vars[':date'] = date("Y-m-d H:i:s");
				
				runUpdatePrepared($pdo, 'UPDATE posts
				SET title = :title, '.(isset($_POST['setDate']) && $_POST['setDate'] == 'true' ? 'date = :date,' : '').' html = :html,
				published = IF(:published = 1,TRUE,FALSE)
				WHERE id = :id;', $vars);
			} catch (PDOException $e) {
				$saveError = $e->getMessage();
			}
		}
		
		//theoretically we saved, set the insert id for prefill 
		if (empty($id))
			$id = $pdo->lastInsertId();
		
		//remove old tags associated to this
		runUpdatePrepared($pdo, 'DELETE FROM tags WHERE id = :id;', array(':id'=>$id));
		
		//add the tags
		foreach($cleanedTags as $tag) {
			try {
				runUpdatePrepared($pdo, 'INSERT INTO tags (id, tagname) VALUES (:id, :tagname);', array(':id'=>$id, ':tagname'=>$tag));
			} catch (PDOException $e) {
				//do nothing, will throw exception for duplicate existing tags for things.
			}
		}
	}
	
	//initialize post content, we will now prefil the data.
	if (empty($id))
		$id = '';
	$title = '';
	$html = '';
	$published = 0;
	$tags = '';
	if (!empty($id)) {
		$rows = runQueryPrepared($pdo, 'SELECT * FROM posts WHERE id = :id;', array(':id'=>$id));
		foreach($rows as $row) {
			$title = $row['title'];
			$html = $row['html'];
			$published = $row['published'];
			$temp = runQueryPrepared($pdo, 'SELECT * FROM tags WHERE id = :id;', array(':id'=>$id));
			foreach($temp as $tag) {
				if ($tags != '')
					$tags .= ', ';
				$tags .= $tag['tagname'];
			}
			break;
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('../../templates/meta.php'); ?>
		<?php require('../../templates/icons.php'); ?>
		<title>Editor</title>
		<?php require('../../templates/core_css.php'); ?>
		<?php require('../../templates/core_js.php'); ?>
		<script src="../../ckeditor/ckeditor.js"></script>
		<script src="../../js/editor.js"></script>
	</head>
	<body>
		<div class="">
			<?php
				//echo the error box if there was a save error
				echo(!empty($saveError) ? '
			<div class="alert alert-danger" role="alert">
				<strong>Save Error! </strong>'.$saveError.'
			</div>' : '');
			?>
			<form method="POST" onsubmit="return stripImages('editor1', 'editSubmit');">
				<input type="hidden" name="id" value="<?php echo($id); ?>">
				
				<label for="title">Title</label>
				<input type="text" name="title" id="title" value="<?php echo($title); ?>">
				
				<label for="tags">Enter tags, separate comma (,):</label>
				<input type="text" name="tags" id="tags" value="<?php echo($tags); ?>">
				
				<label for="published">PUBLISHED?:</label>
				<input type="checkbox" name="published" id="published" value="true" <?php if ($published == 1) echo('checked');?>>
				
				<label for="setDate">Update Date?:</label>
				<input type="checkbox" name="setDate" id="setDate" value="true">
				
				<br>
				<input class="btn btn-primary" id="editSubmit" type="submit" value="SAVE">
				<a href="<?php echoRoot(); ?>posts/<?php echo(!empty($id) ? '?id='.$id : ''); ?>" class="btn btn-warning">Preview</a>
				<a href="../" class="btn btn-warning">Back</a>
				<button type="button" class="btn btn-success" id="indImgButton" onclick="insertALocalImage('indImgButton');">Insert Image at Cursor</button>
				<br>
				
				<label for="editor1">Edit Content:</label>
				<textarea name="editor1" id="editor1" rows="10" cols="80"><?php echo($html); ?></textarea>
				<script>
					CKEDITOR.on('instanceReady',
						function(evt){
							var editor = evt.editor;
							//editor.execCommand('maximize');
							editor.resize( '100%', '600', true);
						}
					);
					CKEDITOR.replace('editor1', {
						toolbarGroups: [
							{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
							{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
							{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
							{ name: 'forms', groups: [ 'forms' ] },
							'/',
							{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
							{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
							{ name: 'links', groups: [ 'links' ] },
							{ name: 'insert', groups: [ 'insert' ] },
							'/',
							{ name: 'styles', groups: [ 'styles' ] },
							{ name: 'colors', groups: [ 'colors' ] },
							{ name: 'tools', groups: [ 'tools' ] },
							{ name: 'others', groups: [ 'others' ] }
						],
						extraPlugins: 'markdown'
					});
				</script>
			</form>
		</div>
	</body>
</html>