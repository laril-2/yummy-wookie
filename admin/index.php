<?php

	require '../includes/config.php';
	require '../includes/functions.php';
			
	$uri = $_SERVER['REQUEST_URI'];
	
	$uri = explode('?', $uri)[0];
	$uri = explode('/', $uri);
	$uri = $uri[count($uri) - 1];
	
	
	if (file_exists('../views/' . $uri . '.php'))	{
		include '../views/' . $uri . '.php';
	}	else {
		http_response_code(404);
		echo 'page not found';
	}
	
	http_response_code(200);	
?>
