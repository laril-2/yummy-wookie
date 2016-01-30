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

function require_user_id()	{
	if (empty($_COOKIE['token']))	{
		header('Location: login');
		exit;
	}

	global $db;
	getDB();

	$stm = $db->prepare("SELECT user_id FROM service_token WHERE token = :token LIMIT 1");
	$stm->execute(array(':token' => $_COOKIE['token']));

	if ($id = $stm->fetchColumn())	{
		return intval($id);
	}

	header('Location: login');
	exit;
}

function download($hash)	{
	global $db;
	getDB();

	$stm = $db->prepare("SELECT * FROM file WHERE hash = :hash LIMIT 1");
	$stm->execute(array(':hash' => $hash));

	$row = $stm->fetch(PDO::FETCH_ASSOC);
	if ($row)	{
		$file = BASE_PATH . '/files/' . substr($hash, 0, 2) . "/$hash";
		if (file_exists($file))	{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $row['name'] . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			exit;
		}
	}
}

function get_download_link($hash)	{
	return "/index.php/download?hash=$hash";
}
