<?php
	/*
		Introduction to me and the site.
		We use a simple title search to find the latest post called 'me'.
		
		Just echos the content.
*/
	
	$rows = runQueryPrepared(getNewPDO(), 'SELECT html FROM posts WHERE title = :title ORDER BY date desc LIMIT 1;', array(':title'=>'me'));
	$failed = true;
	foreach ($rows as $row) {
		echo($row['html']);
		$failed = false;
		break;
	}
	if ($failed)
		echo('404: Content not found. Post called "me" should be created.');
?>
