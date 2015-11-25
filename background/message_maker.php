<?php

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_USERAGENT => 'gruppen8 update client',
		CURLOPT_URL => 'https://daslartsake.ddns.net/app/index.php/add',
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_POST => 1,
		CURLOPT_HTTPHEADER => array(
			'User-Agent: DasLartsake Gruppen8 Update Client' 
		)
	));

	$data = array(
		array(
			'sensorId' => 55,
			'temperature' => 15.0 + ((float) rand() / (float) getrandmax()) * 15.0,
			'timestamp' => time()
		)
	);
	$data = json_encode($data);
	

	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				
	$response = curl_exec($curl);
	echo $response . "\n";

	curl_close($curl);

	file_put_contents('/tmp/laril.log', date('Y-m-d H:i:s') . " message_maker.php: fired\n", FILE_APPEND);

