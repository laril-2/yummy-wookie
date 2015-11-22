<?php

	require '../includes/config.php';
	require '../includes/functions.php';
	
	$uri = $_SERVER['REQUEST_URI'];
	$uri = explode('?', $uri)[0];
	$uri = explode('/', $uri);
	$uri = $uri[count($uri) - 1];
	
	$response = array(
		'succeed' => false,
		'message' => 'method not found'
	);
	$code = 404;
	
	$f = "public_$uri";
	if (function_exists($f))	{
		$code = $f($response);
	}
	
	http_response_code($code);
	echo json_encode($response, JSON_PRETTY_PRINT);
	
?>
