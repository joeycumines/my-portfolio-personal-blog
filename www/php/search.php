<?php
	/*
		Helper library for searching posts.
		
		Depends on php/db_connection.php
	*/
	
	/**
		Helper method for searching the database for posts.
		
		All results are returned as arrays of id (posts table), use mysql
		queries to get further data.
		
		We support limits and pagination as optional parameters.
		
		Essentially just a query builder that uses prepared statements.
		
		params
			$params:
				- array
				- All the parameters for the prepared statement(s)
					- as in, $filters and $sort 
			$filters:
				- prepared string for the WHERE clause
			$sort:
				- prepared string for the ORDER BY clause
			$limit:
				- the max results returned, also used for pagination
			$pagination:
				- The current item. Starts at 1.
			$pdo:
				- optional, to reuse a PDO object
			
		"With two arguments, the first argument specifies the offset of the
		first row to return, and the second specifies the maximum number of rows to return. 
		The offset of the initial row is 0 (not 1):"
		LIMIT 5,10;  # Retrieve rows 6-15
	*/
	function search_posts($params, $filters = null, $sort = null, $limit = null, $pagination = null, $pdo = null) {
		try {
			//create a new pdo if we need
			if ($pdo == null)
				$pdo = getNewPDO();
			
			//defensive copy on $params
			$prepare = array();
			foreach ($params as $key=>$value)
				$prepare[$key] = $value;
			
			$query = 'SELECT id FROM posts p1 WHERE published = 1'.(empty($filters) ? '' : ' AND ('.$filters.')').(empty($sort) ? '' : ' ORDER BY '.$sort);
			//if we have a paginated limit
			if (!empty($limit) && !empty($pagination)) {
				//add limit to the query
				$query .= ' LIMIT '.intval($pagination-1).', '.intval($limit);
			} else if (!empty($limit)) {
				//if we just have a limit
				$query .= ' LIMIT '.intval($limit);
			}
			
			//add the semicolon to the query
			$query .= ';';
			
			//get our result
			$rows = runQueryPrepared($pdo, $query, $prepare);
			$result = array();
			foreach ($rows as $row) {
				array_push($result, $row['id']);
			}
			return $result;
		} catch (Exception $e) {
			echo ($e->getMessage());
			return null;
		}
	}
	
	/**
		As posts search, but returns the total number of results.
	*/
	function search_totalResults($params, $filters = null, $limit = null, $pdo = null) {
		try {
			//create a new pdo if we need
			if ($pdo == null)
				$pdo = getNewPDO();
			
			//defensive copy on $params
			$prepare = array();
			foreach ($params as $key=>$value)
				$prepare[$key] = $value;
			
			$query = 'SELECT count(id) AS pages FROM posts p1 WHERE published = 1'.(empty($filters) ? '' : ' AND ('.$filters.')');
			if (!empty($limit)) {
				$query .= ' LIMIT '.intval($limit);
			}
			
			//add the semicolon to the query
			$query .= ';';
			
			//get our result
			$rows = runQueryPrepared($pdo, $query, $prepare);
			$result = array();
			foreach ($rows as $row) {
				return $row['pages']; 
			}
			return 0;
		} catch (Exception $e) {
			echo ($e->getMessage);
			return null;
		}
	}
?>