<?php
	/*
		About me page.
		We use a simple title search to find the latest post called 'about'.
		
		Just echos the content.
*/
	
	$rows = runQueryPrepared(getNewPDO(), 'SELECT html FROM posts WHERE title = :title ORDER BY date desc LIMIT 1;', array(':title'=>'about'));
	$failed = true;
	foreach ($rows as $row) {
		echo($row['html']);
		$failed = false;
		break;
	}
	if ($failed)
		echo('404: Content not found. Post called "about" should be created.');
?>
