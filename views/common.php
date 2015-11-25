<!DOCTYPE html>
<html>
<head>
	<style>
		.warning {
			background-color: #ee0;
		}
		.alarm {
			background-color: #e00;
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
</head>
<body>
	<h1>COMMON DB</h1>
	<a href="/admin/index.php/main">BACK</a>
	<table id="values">
		<thead>
			<tr>
				<th>SensorID</th>
				<th>Temperature</th>
				<th>Timestamp</th>
			</tr>
		</thead>
		<tbody>
<?php

	$curl = curl_init();
	
	$body = 'Timestamp=' . date('Y-m-d H:i:s', yesterday());

	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_USERAGENT => 'gruppen8 post client',
		CURLOPT_URL => 'http://sulproj.esy.es/getAfterTime.php',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $body,
		CURLOPT_HTTPHEADER => array(
			'User-Agent: DasLartsake Get Client Debian/v1.0'
		)
	));

	$result = curl_exec($curl);
	curl_close($curl);
	
	$result = json_decode($result, true);
	
	if (!is_null($result))	{
		$result = array_reverse($result['Reply']);
		foreach ($result as $row)	{
			$id = $row['SensorID'];
			if (substr($id, 0, 2) != 'r8' || strpos($id, ':') === false)	{
				continue;
			}
			
			$id = explode(':', $id)[1];
			$temp = $row['Temperature'];

			if ($temp > MAX_TEMPERATURE || $temp < MIN_TEMPERATURE)	{
				echo '<tr class="alarm">';
			} elseif ($temp > MAX_WARNING_TEMPERATURE || $temp < MIN_WARNING_TEMPERATURE)	{
				echo '<tr class="warning">';				
			} else {
				echo '<tr>';							
			}
			echo '<td>' . $id . '</td>';
			echo '<td>' . $row['Temperature'] . '</td>';
			echo '<td>' . $row['Timestamp'] . '</td>';
			echo '</tr>';
		}
	}
	
?>
		</tbody>
	</table>
	
</body>
</html>

