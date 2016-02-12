<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST')	{
	$errors = array();
	if (!empty($_POST['username']))	{
		$username = $_POST['username'];
	} else {
		$errors[] = 'USERNAME REQUIRED!';
	}
	if (!empty($_POST['password']))	{
		$password = $_POST['password'];
	} else {
		$errors[] = 'PASSWORD REQUIRED!';
	}

	if (empty($errors))	{
		getDB();

		$hash = md5($password);
		$stm = $db->prepare("SELECT id FROM service_user WHERE username = :username AND password = :hash LIMIT 1");
		$stm->execute(array(
			':username' => $username,
			':hash' => $hash
		));

		if ($row = $stm->fetch(PDO::FETCH_ASSOC))	{
			$id = intval($row['id']);
			$token = md5(date('Y-m-d H:i:s'));

			$db->query("DELETE FROM service_token WHERE user_id = $id");
			$db->query("INSERT INTO service_token (token, user_id) VALUES ('$token', $id)");
			setcookie('token', $token, time() + 3600);
		} else {
			$errors[] = 'INVALID USERNAME OR PASSWORD';
		}
	}

	if (empty($errors))	{
		redirect('list_files');
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css"/>
	<title>Login</title>

	<style>
		.my-header	{
			background: linear-gradient(to bottom right, red, yellow);
			height: 6em;
		}
		.my-content	{
			background: linear-gradient(#aaa, black);
		}
		.filler-head	{
			padding-top: 3em;
			height: 10em;
		}
		.filler-tail	{
			height: 50vh;
		}
		.my-form	{
			background-color: white;
			border-radius: 30px;
			border: 12px solid #ff6700;
			padding-top: 4em;
			height: 15em;
		}
	</style>

</head>
<body class="my-content">
<div class="pure-menu pure-menu-horizontal my-header" align="right">
	<a href="#" class="pure-menu-heading pure-menu-link" style="color: black;">REGISTER</a>
</div>
<div class="filler-head" align="center">
	<h1 style="font-size: 4em;">LOGIN</h1>
</div>
<div class="pure-g">
	<div class="pure-u-1-4"></div>
	<div class="pure-u-1-2 my-form">
		<form class="pure-form pure-form-aligned" method="post" action="/service/index.php/login">
			<fieldset>
				<div class="pure-control-group">
					<label for="username">Username</label>
					<input id="username" name="username" type="text" placeholder="Username"/>
				</div>
				<div class="pure-control-group">
					<label for="password">Password</label>
					<input id="password" name="password" type="password" placeholder="Password"/>
				</div>
				<div class="pure-controls">
					<button type="submit" class="pure-button pure-button-primary" style="background-color: green;">Log in</button>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="pure-u-1-4"></div>
<div>
<div class="filler-tail"></div>
</body>
