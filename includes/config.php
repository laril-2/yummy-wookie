<?php

	ini_set('error_log', '/var/log/apache2/error.log');
	if (file_exists('/home/lartsake/repos/yummy-wookie.git/trunk/includes/settings.json'))	{	// full path for cron jobs
		$settings = json_decode(file_get_contents('/home/lartsake/repos/yummy-wookie.git/trunk/includes/settings.json'), true);
	}
	
	define('MAX_RETURN_COUNT', isset($settings['MAX_RETURN_COUNT']) ? $settings['MAX_RETURN_COUNT'] : 1000);
	define('MAX_TRANSACTION_COUNT', isset($settings['MAX_TRANSACTION_COUNT']) ? $settings['MAX_TRANSACTION_COUNT'] : 100);
	define('MAX_TEMPERATURE', isset($settings['MAX_TEMPERATURE']) ? $settings['MAX_TEMPERATURE'] : 8.0);
	define('MIN_TEMPERATURE', isset($settings['MIN_TEMPERATURE']) ? $settings['MIN_TEMPERATURE'] : 2.0);
	define('MAX_WARNING_TEMPERATURE', isset($settings['MAX_WARNING_TEMPERATURE']) ? $settings['MAX_WARNING_TEMPERATURE'] : 7.0);
	define('MIN_WARNING_TEMPERATURE', isset($settings['MIN_WARNING_TEMPERATURE']) ? $settings['MIN_WARNING_TEMPERATURE'] : 3.0);
	
?>
