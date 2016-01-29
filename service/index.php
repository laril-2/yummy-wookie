<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

$uri = $_SERVER['REQUEST_URI'];

if (substr($uri, 0, 11) === '/index.php/' && strlen($uri) > 11)	{
	$subs = explode('/', $uri);
	$uri = implode('/', array_slice($subs, 2));
	$uri = str_replace('.', '', $uri);

	if (file_exists(BASE_PATH . "/views/{$uri}.php")) {
		include BASE_PATH . "/views/{$uri}.php";
	} else {
		echo '<h1>VIEW NOT FOUND</p>';
	}
} else {
	include BASE_PATH . '/views/list_files.php';
}
