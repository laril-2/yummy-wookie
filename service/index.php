<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

$url = parse_url($_SERVER['REQUEST_URI']);
$path = $url['path'];

if (substr($path, 0, 11) === '/index.php/' && strlen($path) > 11)	{
	$subs = explode('/', $path);
	$path = implode('/', array_slice($subs, 2));
	$path = str_replace('.', '', $path);

	if (file_exists(BASE_PATH . "/views/{$path}.php")) {
		include BASE_PATH . "/views/{$path}.php";
	} else {
		echo "<h1>VIEW NOT FOUND: '$path'</p>";
	}
} else {
	include BASE_PATH . '/views/list_files.php';
}
