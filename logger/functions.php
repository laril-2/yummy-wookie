<?php

function getDB()	{
	global $db;
	
	if (!isset($db))	{
		$db_name = 'u827805111_db';
		$db_user = 'u827805111_user';
		$db_pass = 'vihtori_matti';
		$db_url = 'mysql.hostinger.fi';

		try {
			$db = new PDO("mysql:host=mysql.hostinger.fi;dbname={$db_name}", $db_user, $db_pass);
		}
		catch (PDOException $e)	{
			die($e->getMessage());
		}
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->exec("SET NAMES utf8");
	}
}

?>
