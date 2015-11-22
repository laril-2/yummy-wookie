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
<h1>ALL ALARMS</h1>
<a href="/admin/index.php/main">BACK</a>
<?php 

	$max_timestamp = 0;
	global $db;
	getDB();
	
	$q = $db->query("SELECT A.* FROM sensor_value A JOIN alarm B ON (A.id = B.sensor_value_id AND A.timestamp > $max_timestamp) ORDER BY A.timestamp DESC, A.sensor_id ASC LIMIT " . MAX_RETURN_COUNT);
	
	$values = extractTempValues($q);
	if (empty($values) || true)	{
		echo '<p id="message">NO ALARMS</p>';
	}
?>
<table id="values">
	<thead>
		<tr>
			<th>SENSOR ID</th>
			<th>TEMPERATURE</th>
			<th>TIMESTAMP</th>
		</tr>
	</thead>
	<tbody>
<?php foreach($values as $value): ?>
		<tr<?php
	if ($value['temperature'] > MAX_TEMPERATURE || $value['temperature'] < MIN_TEMPERATURE)	{
		echo ' class="alarm"';
	} elseif ($value['temperature'] > MAX_WARNING_TEMPERATURE || $value['temperature'] < MIN_WARNING_TEMPERATURE)	{
		echo ' class="warning"';	
	}
	if ($value['timestamp'] > $max_timestamp)	{
		$max_timestamp = $value['timestamp'];
	}
?>>
			<td><?= $value['sensor_id'] ?></td>
			<td><?= $value['temperature'] ?></td>
			<td><?= date('Y-m-d H:i:s', $value['timestamp']) ?></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>
<script>

	var last_fetched = <?= $max_timestamp ?>;
	
	function handleXHRResponse(response) {	
		response = JSON.parse(response);
		if (typeof response === 'object' && response.succeed)	{
			var table = $('#values');
			var prependees = [];
			$.each(response.result, function(key, value) {
				var new_value = '<tr';
				if (value.temperature > <?= MAX_TEMPERATURE ?> || value.temperature < <?= MIN_TEMPERATURE ?>)	{
					new_value += ' class="alarm"';
				} else if (value.temperature > <?= MAX_WARNING_TEMPERATURE ?> || value.temperature < <?= MIN_WARNING_TEMPERATURE ?>)	{
					new_value += ' class="warning"';
				}
				
				var date = new Date(value.timestamp * 1000);
				var formatted_date = date.getFullYear();
				formatted_date += '-' + (date.getMonth() + 1);
				formatted_date += '-' + date.getDate();
				formatted_date += ' ' + date.getHours();
				formatted_date += ':' + date.getMinutes();
				formatted_date += ':' + date.getSeconds();
				new_value += '><td>' + value.sensor_id + '</td><td>' + value.temperature + '</td><td>' + formatted_date + '</td></tr>';
				
				prependees.unshift(new_value);
				if (value.timestamp > last_fetched)	{
					last_fetched = value.timestamp;
				}
			});
			$.each(prependees, function(key, value) {
				table.children('tbody').prepend(value);
				$('#message').empty();
			});
		}
	}
	
	function renewXHRRequest()	{
		window.setTimeout(function() {
			var jqxhr = $.ajax('http://daslartsake.ddns.net/app/index.php/get_alarms?since=' + last_fetched).done(handleXHRResponse).always(renewXHRRequest);
		}, 30000);
	}

	$(document).ready(function() {
		var timeout = window.setTimeout(function() {
			var jqxhr = $.ajax('http://daslartsake.ddns.net/app/index.php/get_alarms?since=' + last_fetched).done(handleXHRResponse).always(renewXHRRequest);
		}, 30000);
	});
</script>
</body>
</html>
