<!DOCTYPE html>
<html>
<body>
<h1>SETTINGS</h1>
<a href="/admin/index.php/main">BACK</a>
<h3>CHANGE SETTINGS</h3>
<form action="/app/index.php/change_settings" method="POST">
	<table>
		<tr>
			<td><span>MAX_RETURN_COUNT</span></td>
			<td><input type="text" name="MAX_RETURN_COUNT" value="<?= MAX_RETURN_COUNT ?>"/></td>
		</tr>
		<tr>
			<td><span>MAX_TRANSACTION_COUNT</span></td>
			<td><input type="text" name="MAX_TRANSACTION_COUNT" value="<?= MAX_TRANSACTION_COUNT ?>"/></td>
		</tr>
		<tr>
			<td><span>MAX_TEMPERATURE</span></td>
			<td><input type="text" name="MAX_TEMPERATURE" value="<?= MAX_TEMPERATURE ?>"/></td>
		</tr>
		<tr>
			<td><span>MIN_TEMPERATURE</span></td>
			<td><input type="text" name="MIN_TEMPERATURE" value="<?= MIN_TEMPERATURE ?>"/></td>
		</tr>
		<tr>
			<td><span>MAX_WARNING_TEMPERATURE</span></td>
			<td><input type="text" name="MAX_WARNING_TEMPERATURE" value="<?= MAX_WARNING_TEMPERATURE ?>"/></td>
		</tr>
		<tr>
			<td><span>MIN_WARNING_TEMPERATURE</span></td>
			<td><input type="text" name="MIN_WARNING_TEMPERATURE" value="<?= MIN_WARNING_TEMPERATURE ?>"/></td>
		</tr>
	</table>
	<p>
		<span>PASSWORD</span>
		<input type="password" name="password"/>
		<input type="submit" value="SAVE CHANGES"/>
	</p>
</form>
<h3>CLEAR ALL TABLES</h3>
<form action="/app/index.php/nuke_everything" method="POST">
	<p>
		<span>PASSWORD</span>
		<input type="password" name="password"/>
		<input type="submit" value="NUKE EVERYTHING!"/>
	</p>
</form>
</body>
</html>

