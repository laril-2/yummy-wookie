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
</head>
<body>
<h1>MAIN VIEW</h1>
<h3><a href="/admin/index.php/common">COMMON DB</a></h3>
<h3><a href="/admin/index.php/alarms">ALL ALARMS</a></h3>
<ul>
<?php

	global $db;
	getDB();

	$alarms = array();
	$q = $db->query("SELECT A.sensor_id FROM sensor_value A JOIN alarm B ON (A.id = B.sensor_value_id AND A.timestamp > " . yesterday() . ")");
	while ($row = $q->fetch())	{
		$alarms[$row['sensor_id']] = true;
	}
	unset($q, $row);
	
	$q = $db->query("SELECT DISTINCT sensor_id FROM sensor_value WHERE timestamp > " . yesterday() . " LIMIT " . MAX_RETURN_COUNT);
	while ($row = $q->fetch())	{
		echo '<li' . (array_key_exists($row['sensor_id'], $alarms) ? ' class="alarm">' : '>');
		echo '<a href="/admin/index.php/by_sensor?id=' . $row['sensor_id'] . '">SENSOR ID ' . $row['sensor_id'] . '</a>';
		echo '</li>';
	}	
?>
</ul>
<h3><a href="/admin/index.php/settings">SETTINGS</a></h3>
</body>
</html>
