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

function redirect($url)	{
	header('Location: ' . BASE_URL . "/$url");
	exit;
}

function require_user_id()	{
	if (empty($_COOKIE['token']))	{
		redirect('login');
	}

	global $db;
	getDB();

	$stm = $db->prepare("SELECT user_id FROM service_token WHERE token = :token LIMIT 1");
	$stm->execute(array(':token' => $_COOKIE['token']));

	if ($id = $stm->fetchColumn())	{
		return intval($id);
	}

	redirect('login');
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
	return "/service/index.php/download?hash=$hash";
}

function logger($msg)	{
	if (empty($msg))	{
		return;
	}

	if (is_array($msg))	{
		$msg = implode("\n", $msg);
	}

	file_put_contents('/tmp/laril.log', "$msg\n", FILE_APPEND);
}

function process_upload($data)	{
	global $db;

	$name = empty($data['name']) ? null : htmlspecialchars($data['name']);
	$tmp_name = empty($data['tmp_name']) ? null : $data['tmp_name'];

	if (is_null($data) || is_null($tmp_name) || !file_exists($tmp_name))	{
		return 0;
	}


	$hash = md5_file($tmp_name);
	getDB();

	$stm = $db->prepare("SELECT EXISTS(SELECT hash FROM file WHERE hash = :hash)");
	$stm->execute(array(
		':hash' => $hash
	));

	$exists = $stm->fetchColumn();
	logger(var_export($exists, true));
	if ($exists === 'f' || $exists === false)	{
		$dir = FILES_PATH . '/' . substr($hash, 0, 2);
		if (!file_exists($dir))	{
			mkdir($dir, 0755);
		}

		$temp = $dir . '/_' . $hash;
		file_put_contents($temp, file_get_contents($tmp_name));
		rename($temp, $dir . '/' . $hash);
		unlink($tmp_name);

		$stm = $db->prepare("INSERT INTO file (hash, name) VALUES (:hash, :name)");
		$stm->execute(array(
			':hash' => $hash,
			':name' => $name
		));
	}

	return $hash;
}
