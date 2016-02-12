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
	} else {
		echo '<ul style="color:red">';
		foreach ($errors as $error)	{
			echo "<li>$error</li>";
		}
		echo '</ul>';
	}
}
?>

<h2>LOGIN</h2>
<form method="post" action="/service/index.php/login">
	<table>
		<tr>
			<td><label>username <input type="text" name="username" /></label></td>
		</tr>
		<tr>
			<td><label>password <input type="password" name="password" /></label></td>
		</tr>
		<tr>
			<td><input type="submit" value="LOGIN" /></td>
		</tr>
	</table>
</form>
