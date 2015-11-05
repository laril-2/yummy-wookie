<?php
	require 'functions.php';
	
	$response = array(
		'succeed' => false,
		'message' => 'request method not supported'
	);
	$code = 404;
	
	switch ($_SERVER['REQUEST_METHOD'])	{
		case 'GET':
			getDB();
			
			$q = $db->prepare("SELECT * FROM test ORDER BY id DESC LIMIT 100");
			$q->execute();
			$count = $q->rowCount();
			
			$code = 200;
			$response['succeed'] = true;
			$response['message'] = 'returned ' . $count . ' row' . ($count == 1 ? '' : 's');
			$response['result'] = array();
			while ($row = $q->fetch())	{
				$response['result'][] = array(
					'id' => intval($row['id']),
					'value' => trim($row['value'], '"')
				);
			}
			break;
		case 'POST':		
			$headers = getallheaders();
			
			$auth = empty($headers['Authorization']) ? null : $headers['Authorization'];
			$body = file_get_contents('php://input');
			
			/*
				TODO authentication
			*/
			
			$body = json_decode($body, true);
			if (!isset($body) || !is_array($body))	{
				$response['message'] = 'invalid request body';
				break;
			}
			$keys = array_unique(array('value'));
			
			$all_keys_found = true;
			$entry = array();
			$missing_keys = array();
			foreach ($keys as $key)	{
				if (array_key_exists($key, $body))	{
					$entry[$key] = $body[$key];
				}
				else {
					$all_keys_found = false;
					$missing_keys[] = $key;
				}
			}
			
			if (!$all_keys_found)	{
				$response['message'] = 'missing keys: ' . implode(', ', $missing_keys);
				break;
			}
			
			getDB();
			
			$q = $db->prepare("INSERT INTO test VALUES (NULL, :value)");
			$q->execute(array(
				':value' => $entry['value']
			));
		
			$code = 200;
			$response['succeed'] = true;
			$response['message'] = 'entry saved';			
			break;
	}
	
	http_response_code($code);
	echo json_encode($response, JSON_PRETTY_PRINT);
?>
