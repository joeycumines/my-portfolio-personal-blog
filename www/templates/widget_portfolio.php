<?php
	/*
		This widget echos all a boostrap grid based view of select projects.
		
		A tag search based criteria is used instead of a GUI. This has
		limitations, so I may redo this in future.
		
		To use this tool create a post with two tags, 'portfolio' and
		a unique identifer for this project. The post must be published.
		
		Sample based on:
		<div class="container-fluid bg-3 text-center">
			<h3>Some of my Work</h3><br>
			<div class="row">
				<div class="col-sm-3">
					<p>Some text..</p>
					<img src="http://placehold.it/150x80?text=IMAGE" class="img-responsive" style="width:100%" alt="Image">
				</div>
				<div class="col-sm-3">
					<p>Some text..</p>
					<img src="http://placehold.it/150x80?text=IMAGE" class="img-responsive" style="width:100%" alt="Image">
				</div>
				<div class="col-sm-3">
					<p>Some text..</p>
					<img src="http://placehold.it/150x80?text=IMAGE" class="img-responsive" style="width:100%" alt="Image">
				</div>
				<div class="col-sm-3">
					<p>Some text..</p>
					<img src="http://placehold.it/150x80?text=IMAGE" class="img-responsive" style="width:100%" alt="Image">
				</div>
			</div>
		</div><br><br>
	*/
	
	$rows = runQueryPrepared(getNewPDO(), 'SELECT id, title, date, html, 
	(SELECT tagname FROM tags t1 WHERE p1.id = t1.id AND tagname != :criteria LIMIT 1) AS tag 
	FROM posts p1 WHERE p1.published = 1
	AND p1.id IN 
	(SELECT id FROM tags t1 WHERE tagname = :criteria AND t1.id = p1.id)
	ORDER BY date desc;', array(':criteria'=>'portfolio'));
	$failed = true;
	$column = 1;
	$maxColumn = 4;
	$content = '
		<div class="container-fluid bg-3 text-center">
			<h3>Some of my Work</h3><br>
	';
	foreach ($rows as $row) {
		
		//if we dont have a tag to use we continue (skipping)
		if ($row['tag'] == null)
			continue;
		
		$failed = false;
		
		//find the first image in the article (or placeholder)
		$imageLink = 'http://placehold.it/225x120?text='.urlencode($row['title']);
		$imgSplit = explode('img', $row['html'], 2);
		if (count($imgSplit) > 1) {//if we found img
			//find the first occurrence of src
			$imgSplit = explode('src', $imgSplit[1], 2);
			if (count($imgSplit) > 1) {//if we found src
				$imgSplit = explode('"', $imgSplit[1], 3);
				$imageLink = strip_tags($imgSplit[1]);
			}
		}
		
		//open the row if we are on column 1
		if ($column == 1) {
			$content.= '
			<div class="row">
			';
		}
		
		//append content here.
		$content.= '
				<div class="col-sm-3">
					<p>
						<a href="'.getRootUrl().'posts/?id='.urlencode($row['id']).'">
							<span class="glyphicon glyphicon-book" aria-hidden="true"></span>&nbsp;'.$row['title'].'
						</a>
						&nbsp;&nbsp;
						<a href="'.getRootUrl().'search/?tag='.urlencode($row['tag']).'">
							<span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Blog
						</a>
					</p>
					<img src="'.$imageLink.'" class="img-responsive" style="width:100%" alt="portfolio image">
				</div>
		';
		
		$column++;
		//close the row if we are on > then the last column
		if ($column > $maxColumn) {
			$content.= '
			</div>
			';
			//reset the column
			$column = 1;
		}
	}
	
	//close the row if we are not col 1
	if ($column != 1) {
		$content.= '
			</div>
		';
	}
	
	$content.= '
		</div><br><br>
	';
	if ($failed)
		echo('<p>The admin has not created any portfolio entries yet :(.</p>');
	else
		echo($content);
?>
