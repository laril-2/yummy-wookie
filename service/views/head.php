<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css"/>
	<title><?php echo isset($title) && is_string($title) ? $title : DEFAULT_TITLE ?></title>

	<style>
		.my-header {
			padding: 1em;
			background-color: #ddf;
			border-radius: 15px;
			margin: 1em;
			border: 1px solid black;
		}
	</style>

</head>
<body>
<?php

	if (isset($user_id) && intval($user_id) > 0)	{
		getDB();

		$q = $db->query("SELECT username FROM service_user WHERE id = " . intval($user_id));
		$row = $q->fetch(PDO::FETCH_ASSOC);
?>
<div class="pure-g">
	<div class="pure-u-2-3">
		<div class="my-header">
			<h2><?php echo isset($title) && is_string($title) ? $title : DEFAULT_TITLE; ?></h2>
		</div>
	</div>
	<div class="pure-u-1-3">
		<div class="my-header" align="center">
			<h4><?php echo $row ? 'Logged in as ' . $row['username'] : 'Not logged in'; ?></h4>
			<a class="pure-button pure-button-primary" href="logout">Log out</a>
		</div>
	</div>
</div>
<?php
	}
?>
