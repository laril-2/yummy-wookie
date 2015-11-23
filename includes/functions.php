<?php

function getDB()	{
	global $db;
	
	if (!isset($db))	{

		try {
			$db = new PDO("pgsql:host=localhost;dbname=lartsake;user=lartsake;password=pamicaprio");
		}
		catch (PDOException $e)	{
			die($e->getMessage());
		}
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
}

function validateEntry(&$entry, &$keys)	{
	if (!is_array($entry))
		return false;
		
	foreach ($keys as $key => $type)	{
		if (empty($entry[$key]))
			return false;
		
		switch ($type)	{
		case 'int_positive':
			if (intval($entry[$key]) <= 0)
				return false;
			break;
		case 'float':
			if (!is_float($entry[$key]))
				return false;
			break;
		default:
			return false;
		}
	}
	
	return true;
}

function yesterday()	{
	return time() - 24 * 3600;
}

function extractTempValues($q)	{
	$values = array();
	if ($q instanceof PDOStatement)	{
		while ($row = $q->fetch())	{
			$values[] = array(
				'id' => $row['id'],
				'car_id' => $row['car_id'],
				'sensor_id' => $row['sensor_id'],
				'temperature' => $row['temperature'],
				'timestamp' => $row['timestamp']
			);
		}	
	}
	return $values;
}

function public_info(&$response)	{
	$response['succeed'] = true;
	$response['message'] = 'API endpoints';
	$response['result'] = array(
		'info' => 'this',
		'add' => 'add sensor value (sensorId, temperature, timestamp)',
		'get_all' => 'get all sensor values',
		'get_alarms' => 'get all alarms',
		'get_by_car?id=x' => 'get values by car ID x',
		'get_by_sensor?id=x' => 'get values by sensor ID x'
	);
	return 200;
}

function public_log(&$response)	{
	$log = file_get_contents('/tmp/laril.log');
	$response['succeed'] = true;
	$response['message'] = $log;
	return 200;
}

function public_get_alarms(&$response)	{
	$since = isset($_GET['since']) ? intval($_GET['since']) : 0;

	global $db;
	getDB();
	
	$q = $db->query("SELECT A.* FROM sensor_value A JOIN alarm B ON (A.id = B.sensor_value_id AND A.timestamp > $since) ORDER BY A.timestamp DESC, A.sensor_id ASC LIMIT " . MAX_RETURN_COUNT);
	
	$response['result'] = extractTempValues($q);
	
	$response['succeed'] = true;
	$response['message'] = 'returned ' . $q->rowCount() . ' entries';
}

function public_get_all(&$response)	{
	global $db;
	getDB();
	
	$q = $db->prepare("SELECT * FROM sensor_value ORDER BY timestamp DESC LIMIT " . MAX_RETURN_COUNT);	
	$q->execute();
	
	$response['result'] = extractTempValues($q);

	$response['succeed'] = true;
	$response['message'] = 'returned ' . $q->rowCount() . ' entries';
	return 200;
}

function public_get_by_car(&$response)	{

	$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	if ($car_id <= 0)	{
		$response['message'] = 'invalid id';
		return 200;
	}
	
	global $db;
	getDB();
	
	$q = $db->prepare("SELECT * FROM sensor_value WHERE car_id = :car_id ORDER BY timestamp DESC LIMIT " . MAX_RETURN_COUNT);
	$q->execute(array(
		':car_id' => $car_id
	));
	
	$response['result'] = extractTempValues($q);

	$response['succeed'] = true;
	$response['message'] = 'returned ' . $q->rowCount() . ' entries';
	return 200;
}

function public_get_by_sensor(&$response)	{

	$sensor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$since = isset($_GET['since']) ? intval($_GET['since']) : 0;
	if ($sensor_id <= 0)	{
		$response['message'] = 'invalid id';
		return 200;
	}
	
	global $db;
	getDB();
	
	$q = $db->prepare("SELECT * FROM sensor_value WHERE sensor_id = :sensor_id AND timestamp > $since ORDER BY timestamp DESC LIMIT " . MAX_RETURN_COUNT);
	$q->execute(array(
		':sensor_id' => $sensor_id
	));
	
	$response['result'] = extractTempValues($q);

	$response['succeed'] = true;
	$response['message'] = 'returned ' . $q->rowCount() . ' entries';
	return 200;
}


function public_add(&$response)	{
	if ($_SERVER['REQUEST_METHOD'] != 'POST')	{
		$response['message'] = 'method not supported';
		return 200;
	}

	$headers = getallheaders();
	
	$auth = empty($headers['Authorization']) ? null : $headers['Authorization'];
	$body = file_get_contents('php://input');
	$errors = array();
	
	/*
		TODO authentication
	*/
	
	$body = json_decode($body, true);
	if (!isset($body) || !is_array($body))	{
		$response['message'] = 'invalid request body';
		return 200;
	}
	
	$required_keys = array(
		'sensorId' => 'int_positive',
		'temperature' => 'float',
		'timestamp' => 'int_positive'
	);
	
	$valid_entries = array();
	foreach ($body as $entry)	{
		if (!is_array($entry))	{
			continue;
		}
		if (validateEntry($entry, $required_keys))	{
			$valid_entries[] = $entry;
		}
	}

	global $db;
	getDB();
	
	if (empty($valid_entries))	{
		$response['message'] = 'no valid entries';
		return 200;
	}

	// insert missing sensors	
	$sensors = array();
	foreach ($valid_entries as $entry)	{
		$sensors[intval($entry['sensorId'])] = true;
	}
	
	$q = $db->query("SELECT id FROM sensor WHERE id IN (" . implode(',', array_keys($sensors)) . ")");
	while ($row = $q->fetch())	{
		unset($sensors[intval($row['id'])]);
	}
	
	if (!empty($sensors))	{
		$db->query("INSERT INTO sensor (id) VALUES (" . implode('), (', array_keys($sensors)) . ")");
	}

	$added_entries = array();
	foreach ($valid_entries as $entry)	{
		try {
			$q = $db->prepare("INSERT INTO sensor_value (sensor_id, temperature, timestamp) VALUES (:sensor_id, :temperature, :timestamp)");
			$q->execute(array(
				':sensor_id' => $entry['sensorId'],
				':temperature' => $entry['temperature'],
				':timestamp' => $entry['timestamp']
			));
	
			$q = $db->query("SELECT LASTVAL()");
			$inserted = $q->fetchColumn();
		}
		catch (PDOException $e)	{
			$errors[] = $e->getMessage();
			continue;
		}
		$added_entries[] = $entry;
	
		try {
			$q = $db->prepare("UPDATE sensor SET timestamp = :timestamp WHERE id = :sensor_id AND (timestamp IS NULL OR timestamp < :timestamp)");
			$q->execute(array(
				':timestamp' => $entry['timestamp'],
				':sensor_id' => $entry['sensorId']
			));
		}
		catch (PDOException $e)	{
			$errors[] = $e->getMessage();
			continue;
		}
		
		$temperature = floatval($entry['temperature']);
		if ($temperature < MIN_TEMPERATURE || $temperature > MAX_TEMPERATURE)	{
			try {		
				$q = $db->prepare("INSERT INTO alarm (sensor_value_id) VALUES (:sensor_value_id)");
				$q->execute(array(
					':sensor_value_id' => $inserted
				));
			}
			catch (PDOException $e)	{
				$errors[] = $e->getMessage();
				continue;
			}
		}
		
	}

	$response['succeed'] = count($added_entries) > 0 ? true : false;
	$response['message'] = count($added_entries) . (count($added_entries) > 1 ? ' entries saved' : ' entry saved');
	$response['saved_entries'] = $added_entries;
	if (!empty($errors))	{
		$response['errors'] = $errors;
	}
	return 200;
}

function public_change_settings(&$response)	{
	if ($_SERVER['REQUEST_METHOD'] != 'POST')	{
		header('Location: /admin/index.php/settings');
		exit();
	}
	
	if (!isset($_POST['password']) || $_POST['password'] != 'gruppen8')	{	
		header('Location: /admin/index.php/settings');
		exit();
	}
	
	$settings = json_decode(file_get_contents('../includes/settings.json'), true);
	$changes = false;
	foreach ($settings as $key => &$value)	{
		if (!empty($_POST[$key]))	{
			if (is_float($value) && is_float($_POST[$key]))	{
				$value = floatval($_POST[$key]);
				$changes = true;
			} elseif (intval($value) > 0 && intval($_POST[$key]) > 0)	{
				$value = intval($_POST[$key]);				
				$changes = true;
			}
		}
	}
	unset($value);
	
	if ($changes)	{
		file_put_contents('../includes/tmp_settings.json', json_encode($settings, JSON_PRETTY_PRINT));
		rename('../includes/tmp_settings.json', '../includes/settings.json');
	}

	header('Location: /admin/index.php/settings');
	exit();
}

function public_echo(&$response)	{
	
	if ($_SERVER['REQUEST_METHOD'] != 'POST')	{
		$response['message'] = 'method not supported';
		return 200;
	}

	$body = file_get_contents('php://input');
		
	$response['succeed'] = true;
	$response['message'] = $body;
	return 200;
}

function public_nuke_everything(&$response)	{
	if ($_SERVER['REQUEST_METHOD'] != 'POST')	{
		header('Location: /admin/index.php/settings');
		exit();
	}
	
	if (!isset($_POST['password']) || $_POST['password'] != 'gruppen8')	{	
		header('Location: /admin/index.php/settings');
		exit();
	}
	
	global $db;
	getDB();
	
	$db->beginTransaction();
	$db->query("DELETE FROM alarm");
	$db->query("DELETE FROM common_value");
	$db->query("DELETE FROM sensor_value");
	$db->query("DELETE FROM sensor");
	$db->commit();

	header('Location: /admin/index.php/boom');
	exit();
}


?>
