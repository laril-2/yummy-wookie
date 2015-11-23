<?php

	if (file_exists('/tmp/asdfakl362running'))	{
		file_put_contents('/tmp/laril.log', date('Y-m-d H:i:s') . " process.php: another process running, exiting...\n", FILE_APPEND);
		exit();
	}
	
	file_put_contents('/tmp/asdfakl362running', 'running...');

	require '/home/lartsake/repos/yummy-wookie.git/trunk/includes/config.php';
	require '/home/lartsake/repos/yummy-wookie.git/trunk/includes/functions.php';

	global $db;
	getDB();
	
	$q = $db->query("SELECT id, sensor_id, temperature, timestamp FROM sensor_value WHERE id NOT IN (SELECT sensor_value_id FROM common_value) ORDER BY timestamp ASC LIMIT " . MAX_TRANSACTION_COUNT);
	
	$values = array();
	while ($row = $q->fetch())	{
		$values[] = array(
			'id' => $row['id'],
			'sensor_id' => $row['sensor_id'],
			'temperature' => $row['temperature'],
			'timestamp' => date('Y-m-d H:i:s', $row['timestamp']),
			'success' => false
		);
	}
	
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_USERAGENT => 'gruppen8 update client',
		CURLOPT_URL => 'http://sulproj.esy.es/post.php',
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array(
			'User-Agent: DasLartsake Gruppen8 Update Client' 
		)
	));	

	// can only pass one value per request..
	$errors = array();
	foreach ($values as &$value)	{
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'SensorID=r8:' . $value['sensor_id'] . '&Temperature=' . $value['temperature'] . '&Timestamp=' . $value['timestamp']);
				
		$response = curl_exec($curl);
		
		if ($response == 'Inserted data to database')	{
			$value['success'] = true;
		} else {
			$errors[] = 'ID ' . $value['id'] . ':' . $response;
			if (strpos($response, 'SQLSTATE[23000]') !== false)	{	// common DB already contains this (SensorID, Timestamp) combination
				$value['success'] = true;
			}
		}
	}
	unset($value);
	
	curl_close($curl);
	
	$q = $db->prepare("INSERT INTO common_value (sensor_value_id, group_id) VALUES (:id, :group)");
	
	$success_count = 0;
	foreach ($values as $value)	{
		if (!$value['success'])	{
			continue;
		}
		$q->execute(array(
			':id' => $value['id'],
			':group' => 'r8:' . $value['sensor_id']
		));
		
		$success_count++;
	}
	
	file_put_contents('/tmp/laril.log', date('Y-m-d H:i:s') . " process.php: inserted $success_count values\n", FILE_APPEND);
	if (!empty($errors))	{
		file_put_contents('/tmp/laril.log', "ERRORS\n" . implode("\n", $errors) . "\n", FILE_APPEND);
	}
	
	unlink('/tmp/asdfakl362running');
	
