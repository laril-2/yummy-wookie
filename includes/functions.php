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

function validateValue($value, $type)	{
	switch ($type)	{
	case 'int_positive':
		return intval($value) > 0 ? true : false;
		break;
	case 'float':
		return is_float($value) ? true : false;
		break;
	default:
		return false;
	}
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
	$response['message'] = 'info message';
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

function public_get_alarms(&$response)	{
	global $db;
	getDB();
	
	$q = $db->query("SELECT A.* FROM sensor_value A JOIN alarm B ON (A.id = B.sensor_value_id) ORDER BY A.timestamp LIMIT " . MAX_RETURN_COUNT);
	
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
	if ($sensor_id <= 0)	{
		$response['message'] = 'invalid id';
		return 200;
	}
	
	global $db;
	getDB();
	
	$q = $db->prepare("SELECT * FROM sensor_value WHERE sensor_id = :sensor_id ORDER BY timestamp DESC LIMIT " . MAX_RETURN_COUNT);
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
	$all_keys_found = true;
	$invalid_keys = array();
	
	foreach ($required_keys as $key => $type)	{
		if (empty($body[$key]))	{
			$invalid_keys[] = $key;
			$all_keys_found = false;
			continue;
		}
		if (!validateValue($body[$key], $type))	{
			$invalid_keys[] = $key;
			$all_keys_found = false;
		}
	}
	
	if (!$all_keys_found)	{
		$response['message'] = 'missing or invalid keys: ' . implode(', ', $invalid_keys);
		return 200;
	}

	global $db;
	getDB();

	$q = $db->prepare("SELECT EXISTS(SELECT id FROM sensor WHERE id = :sensor_id)");
	$q->execute(array(
		':sensor_id' => $body['sensorId']
	));
	$exists = $q->fetchColumn();
	
	if (!$exists)	{
		$q = $db->prepare("INSERT INTO sensor (id) VALUES (:sensor_id)");
		$q->execute(array(
			'sensor_id' => $body['sensorId']
		));
	}
	
	try {
		$q = $db->prepare("INSERT INTO sensor_value (sensor_id, temperature, timestamp) VALUES (:sensor_id, :temperature, :timestamp)");
		$q->execute(array(
			':sensor_id' => $body['sensorId'],
			':temperature' => $body['temperature'],
			':timestamp' => $body['timestamp']
		));
		
		$q = $db->query("SELECT LASTVAL()");
		$inserted = $q->fetchColumn();
	}
	catch (PDOException $e)	{
		$response['message'] = $e->getMessage();
		return 200;
	}
	
	try {
		$q = $db->prepare("UPDATE sensor SET timestamp = :timestamp WHERE id = :sensor_id AND (timestamp IS NULL OR timestamp < :timestamp)");
		$q->execute(array(
			':timestamp' => $body['timestamp'],
			':sensor_id' => $body['sensorId']
		));
	}
	catch (PDOException $e)	{
		$response['message'] = $e->getMessage();
		return 200;
	}
	
	$temperature = floatval($body['temperature']);
	if ($temperature < MIN_TEMPERATURE || $temperature > MAX_TEMPERATURE)	{
		try {		
			$q = $db->prepare("INSERT INTO alarm (sensor_value_id) VALUES (:sensor_value_id)");
			$q->execute(array(
				':sensor_value_id' => $inserted
			));
		}
		catch (PDOException $e)	{
			$response['message'] = $e->getMessage();
			return 200;
		}
	}

	$response['succeed'] = true;
	$response['message'] = 'entry saved';

	return 200;
}


?>
