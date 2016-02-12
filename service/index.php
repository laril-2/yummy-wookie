<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

$url = parse_url($_SERVER['REQUEST_URI']);
$path = $url['path'];

if (substr($path, 0, 19) === '/service/index.php/' && strlen($path) > 19)	{
	$subs = explode('/', $path);
	$path = implode('/', array_slice($subs, 3));
	$path = str_replace('.', '', $path);

	if (file_exists(BASE_PATH . "/views/{$path}.php")) {
		include BASE_PATH . "/views/{$path}.php";
	} else {
		echo "<h1>VIEW NOT FOUND: '$path'</p>";
	}
} else {
	redirect('list_files');
}
