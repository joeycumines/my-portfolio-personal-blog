<?php
	/*
		Menu bar for consistant look at feel and navigation.
	*/
	
	$requestUrlStripped = strpos($_SERVER['REQUEST_URI'], '?') !== false ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $_SERVER['REQUEST_URI'];
?>
		
		<nav class="navbar navbar-inverse">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?php echoRoot(); ?>contact/">myurl.me</a>
				</div>
				<div class="collapse navbar-collapse" id="myNavbar">
					<ul class="nav navbar-nav">
						<li class="<?php echo($requestUrlStripped == '/' ? 'active' : ''); ?>"><a href="<?php echoRoot(); ?>">Home</a></li>
						<li class="<?php echo($requestUrlStripped == '/about/' ? 'active' : ''); ?>"><a href="<?php echoRoot(); ?>about/">About</a></li>
						<li class="<?php echo($requestUrlStripped == '/blog/' || $requestUrlStripped == '/posts/' ? 'active' : ''); ?>"><a href="<?php echoRoot(); ?>search/">Blog</a></li>
						<li class="<?php echo($requestUrlStripped == '/search/' ? 'active' : ''); ?>"><a href="<?php echoRoot(); ?>search/">Search</a></li>
						<li class="<?php echo($requestUrlStripped == '/contact/' ? 'active' : ''); ?>"><a href="<?php echoRoot(); ?>contact/">Contact</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li>
							<?php
								if (isset($_SESSION['admin']) && $_SESSION['admin']) {
									echo('<a href="'.getRootUrl().'logout/?redirectUrl='.urlencode(getRootUrl()).'"><span class="glyphicon glyphicon-log-out"></span>&nbsp;logout</a>');
								} else {
									echo('<a href="'.getRootUrl().'login/"><span class="glyphicon glyphicon-log-in"></span>&nbsp;</a>');
								}
							?>
						</li>
					</ul>
				</div>
			</div>
		</nav>
