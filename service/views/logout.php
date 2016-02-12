<?php

	$user_id = require_user_id();

	getDB();
	$q = $db->query("DELETE FROM service_token WHERE user_id = $user_id");

	redirect('login');
?>
