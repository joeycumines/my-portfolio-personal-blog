<?php
	/*
		Basic search queries are served from here. This can be used for
		paginated project article searches and the like.
		
		This functionality is simply built on the search functionality
		written in php, using basic mysql queries.
	*/
	
	/**
		Helper function for result items.
	*/
	function resultItem($readMoreLink, $title, $date, $imageLink, $contentText, $contentLowerHTML) {
		$result='
					<div class="row search-post-box">
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-2">
									<a href="'.$readMoreLink.'">
										<img class="img-thumbnail img-responsive" src="'.$imageLink.'" alt="Post Image">
									</a>
								</div>
								<div class="col-xs-9 col-sm-9 col-md-10">
									<h4 class="titleOfPost">'.$title.'</h4>
									<h5>'.$date.'</h5>
									<div class="row">
										<div class="col-xs-12 col-sm-12 col-md-12">
											<p>'.$contentText.'</p>
										</div>
									</div>
									<div class="row">
										'.$contentLowerHTML.'
									</div>
								</div>
							</div>
						</div>
					</div>
';
		return $result;
	}
	
	session_start();
	require('../php/db_connection.php');
	require('../php/search.php');
	
	$pdo = getNewPDO();
	
	//default search populated down the bottom if we dont set this.
	$filters = '';
	$sort = '';
	$params = array();
	$limit = 10;
	$pagination = 1;
	
	//counter for query building
	$counter = 1;
	
	//load any other settings from $_GET
	if (isset($_GET['limit'])) $limit = intval($_GET['limit']);
	if (isset($_GET['page'])) $pagination = (intval($_GET['page'])-1) * $limit + 1;
	//filter building
	if (isset($_GET['search']) && !empty(validate($_GET['search']))) {
		//text search string for partial title matches or tag matches
		//we split by spaces, OR for tag or title for every word.
		$terms = explode (' ', validate($_GET['search']));
		$tempFilters = '';
		foreach ($terms as $term) {
			$key = ':search'.$counter;
			if ($counter > 1)
				$tempFilters .= ' OR ';
			$tempFilters .= "title LIKE CONCAT('%', ".$key.", '%') OR ".$key." IN (SELECT tagname FROM tags t1 WHERE p1.id = t1.id)";
			$params[$key] = $term;
			$counter++;
		}
		$filters .= '('.$tempFilters.')';
	}
	//search within tag filter (tag). anytagname = all
	$tagFilter = validate(isset($_GET['tag']) ? $_GET['tag'] : null);
	if ($tagFilter != null && $tagFilter != 'anytagname') {
		$key = ':tag'.$counter;
		if ($counter > 1)
			$filters .= ' AND ';
		$filters.= $key.' IN (SELECT tagname FROM tags t1 WHERE p1.id = t1.id)';
		$params[$key] = $tagFilter;
		$counter++;
	}
	
	//sort building (orderby)
	$orderby = validate(isset($_GET['orderby']) ? $_GET['orderby'] : null);
	if ($orderby != null) {
		if ($orderby == '0') {
			//newest first
			$sort = 'date desc, title asc';
		} else if ($orderby == '1') {
			//oldest first
			$sort = 'date asc, title asc';
		}
	}
	
	//sort by time by default
	if (empty($sort))
		$sort = 'date desc, title asc';
	
	//our results
	$results = search_posts($params, $filters, $sort, $limit, $pagination, $pdo);
	
	//build the result html
	$resultHtml = '';
	foreach($results as $id) {
		$result = null;
		$rows = runQueryPrepared($pdo, 'SELECT * FROM posts WHERE id = :id;', array(':id'=>$id));
		foreach ($rows as $row) {
			$result = $row;
			break;
		}
		if ($result == null)
			continue;
		//add to $resultHtml
		$push = array();
		$push['readMoreLink'] = getRootUrl().'posts/?id='.$result['id'];
		$push['title'] = $result['title'];
		$push['date']  = $result['date'];
		$push['imageLink'] = getRootUrl().'resource/blog-post-icon.png';
		$push['contentText'] = strip_tags($result['html']);
		if (strlen($push['contentText']) > 100)
			$push['contentText'] = substr($push['contentText'], 0, 100).' ...<a href="'.$push['readMoreLink'].'">(cont.)</a>';
		//extract the first image if there is one (otherwise leave the placeholder in)
		$imgSplit = explode('img', $result['html'], 2);
		if (count($imgSplit) > 1) {//if we found img
			//find the first occurrence of src
			$imgSplit = explode('src', $imgSplit[1], 2);
			if (count($imgSplit) > 1) {//if we found src
				$imgSplit = explode('"', $imgSplit[1], 3);
				$push['imageLink'] = strip_tags($imgSplit[1]);
			}
		}
		$push['contentLowerHTML'] = '<div class="col-sm-4 col-md-4"><div class="col-xs-12 col-sm-8 col-md-8"><a class="btn btn-primary btn-post" href="'.
				$push['readMoreLink'].'">Read More <span class="glyphicon glyphicon-chevron-right"></span></a></div></div>';
		$resultHtml .= "\n".resultItem($push['readMoreLink'], $push['title'], $push['date'], $push['imageLink'], $push['contentText'], $push['contentLowerHTML'])."\n";
	}
	
	//build $pageLinksHtml
	$pageLinksHtml = '<div class="row"><div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2"><span>Page: ';
	$totalPages = ceil(search_totalResults($params, $filters, $limit, $pdo)/$limit);
	for ($x = 0; $x < $totalPages; $x++) {
		if ($x > 0)
			$pageLinksHtml.= '&nbsp;&nbsp;';
		$page = $x + 1;
		if ($page == ceil($pagination/$limit)) {
			$pageLinksHtml.= '([ '.$page.' ])';
		} else {
			//we need to carry on any search terms
			$partialQuery = '';
			foreach ($_GET as $key=>$value) {
				if ($key != 'page') {
					$partialQuery.= '&'.$key.'='.$value;
				}
			}
			$pageLinksHtml.= '<a href=".?page='.$page.$partialQuery.'">['.$page.']</a>';
		}
	}
	$pageLinksHtml.= '</span></div></div>';
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require('../templates/meta.php'); ?>
		<?php require('../templates/icons.php'); ?>
		<title>Search</title>
		<?php require('../templates/core_css.php'); ?>
		<style>
			.search-post-box {
				border-top-style: dotted;
				border-top-width: 3px;
				box-sizing: border-box;
				border-top-color: rgb(0,112,204);
				padding-top: 15px;
				padding-bottom: 15px;
			}
			a.btn-post {
				padding: 10px 5px;
			}
			
			/* For the dropdown search box */
			
			.dropdown.dropdown-lg .dropdown-menu {
				margin-top: -1px;
				padding: 6px 20px;
			}
			.input-group-btn .btn-group {
				display: flex !important;
			}
			.btn-group .btn {
				border-radius: 0;
				margin-left: -1px;
			}
			.btn-group .btn:last-child {
				border-top-right-radius: 4px;
				border-bottom-right-radius: 4px;
			}
			.btn-group .form-horizontal .btn[type="submit"] {
				border-top-left-radius: 4px;
				border-bottom-left-radius: 4px;
			}
			.form-horizontal .form-group {
				margin-left: 0;
				margin-right: 0;
			}
			.form-group .form-control:last-child {
				border-top-left-radius: 4px;
				border-bottom-left-radius: 4px;
			}
			
			@media screen and (min-width: 768px) {
				#adv-search {
					width: 500px;
					margin: 0 auto;
				}
				.dropdown.dropdown-lg {
					position: static !important;
				}
				.dropdown.dropdown-lg .dropdown-menu {
					min-width: 500px;
				}
			}
			#adv-search {
				margin-top: 10px;
			}
		</style>
	</head>
	<body>
		<?php require('../templates/menubar.php'); ?>
		<?php require('../templates/widget_info.php'); ?>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="input-group" id="adv-search">
						<input form="advanced-search-form" type="text" class="form-control" placeholder="Search titles or tags" id="search" name="search" value="<?php echo(isset($_GET['search']) && !empty($_GET['search']) ? validate($_GET['search']) : ''); ?>" />
						<div class="input-group-btn">
							<div class="btn-group" role="group">
								<div class="dropdown dropdown-lg">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span></button>
									<div class="dropdown-menu dropdown-menu-right" role="menu">
										<form class="form-horizontal" id="advanced-search-form">
											<div class="form-group">
												<p>Order By:</p>
												<select class="form-control" name="orderby">
													<option value="0">Time posted (newest first)</option>
													<option value="1" <?php echo($orderby == '1' ? 'selected' : ''); ?>>Time posted (oldest first)</option>
												</select>
											</div>
											<div class="form-group">
												<p>Search within the tag:</p>
												<select class="form-control" name="tag">
													<option value="anytagname" selected>Any</option>
													<?php
														//load the available tags
														$rows = runQueryPrepared($pdo, 'SELECT tagname FROM tags GROUP BY tagname ORDER BY tagname asc;', array());
														//echo as options
														foreach($rows as $row) {
															echo('
													<option value="'.$row['tagname'].'" '.($tagFilter == $row['tagname'] ? 'selected' : '').'>'.$row['tagname'].'</option>
															');
														}
													?>
												</select>
											</div>
											<div class="form-group">
												<p>Results per page:</p>
												<input class="form-control" type="number" id="limit" name="limit" min="5" value="<?php echo($limit); ?>" max="50" step="5">
												<input type="range" min="5" value="<?php echo($limit); ?>" max="50" step="5" onchange="updateLimit(this.value);">
												<script>function updateLimit(val) {document.getElementById('limit').value=val; }</script>
											</div>
											<button type="submit" class="btn btn-primary btn-submit-adv-search"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
										</form>
									</div>
								</div>
								<button type="submit" class="btn btn-primary btn-submit-adv-search" form="advanced-search-form"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<h1>Blog Posts Search</h1>
			<br><?php echo($pageLinksHtml); ?><br>
			<div class="">
				<?php echo($resultHtml); ?>
			</div>
			<br><?php echo($pageLinksHtml); ?><br>
		</div>
		<?php require('../templates/footer.php'); ?>
		<?php require('../templates/core_js.php'); ?>
	</body>
</html>
