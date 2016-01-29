<?php

function getDB()	{
	global $db;
	
	if (!isset($db))	{
		try {
			$db = new PDO('pgsql:host=localhost;dbname=' . DB_NAME . ';user=' . DB_USER . ';password=' . DB_PASSWORD);
		}
		catch (PDOException $e)	{
			die($e->getMessage());
		}
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
}
