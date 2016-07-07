<?php
	/*
		Contact us page.
		We use a simple title search to find the latest post called 'contact'.
		
		Just echos the content.
*/
	
	$rows = runQueryPrepared(getNewPDO(), 'SELECT html FROM posts WHERE title = :title ORDER BY date desc LIMIT 1;', array(':title'=>'contact'));
	$failed = true;
	foreach ($rows as $row) {
		echo($row['html']);
		$failed = false;
		break;
	}
	if ($failed)
		echo('404: Content not found. Post called "contact" should be created.');
?>
