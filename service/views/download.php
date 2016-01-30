<?php

$user_id = require_user_id();

$hash = !empty($_GET['hash']) && strlen($_GET['hash']) === 32 ? strtolower(preg_replace('/[\s\W]/', '', $_GET['hash'])) : null;

if (isset($hash) && strlen($hash) === 32)	{
	download($hash);
} else {
	echo '<h1>INVALID HASH</h1>';
}

echo '<h1>FILE NOT FOUND</h1>';
