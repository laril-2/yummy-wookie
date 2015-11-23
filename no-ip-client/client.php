<?php

$cmd = "/sbin/ifconfig";

$output = array();
exec($cmd, $output);

$log = array(date('Y-m-d H:i:s'));

$ethernet = false;
foreach ($output as $row)	{
	if ($ethernet)	{
		$matches = array();
		if (preg_match('/(addr:)([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $row, $matches))	{
			$ip = $matches[2];
		}
		break;
	}
	if (substr($row, 0, 4) == 'eth0')	{
		$ethernet = true;
	}
	else {
		$ethernet = false;
	}
}
unset($output);

if (isset($ip))	{
	$log[] = "current IP $ip";
	$last_ip = file_get_contents('/home/lartsake/no-ip-client/last-ip');	// full path for cron jobs
	$log[] = "last IP $last_ip";
	if (trim($ip) !== trim($last_ip))	{
		$log[] = "IP changed";

		$curl = curl_init();
		$auth_string = base64_encode("daslartsake:hv5.dp/f0");

		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_CONNECTTIMEOUT => 2,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_USERAGENT => 'test-client',
			CURLOPT_URL => 'http://dynupdate.no-ip.com/nic/update?hostname=daslartsake.ddns.net&myip=' . $ip,
			CURLOPT_HTTPHEADER => array(
				'Host: dynupdate.no-ip.com',
				'Authorization: Basic ' . $auth_string,
				'User-Agent: DasLartsake Update Client Debian/v1.0 lari.lunden@gmail.com' 
			)
		));

		$result = curl_exec($curl);
		curl_close($curl);

		list($message, $new_ip) = explode(' ', $result);
		if ($message == 'good')	{
			file_put_contents('last-ip', $new_ip);
		}
		$log[] = $result;

	}
	else {
		$log[] = 'no request';
	}
}

file_put_contents('/tmp/laril.log', implode("\n", $log) . "\n", FILE_APPEND);

