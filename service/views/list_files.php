<?php

	$user_id = require_user_id();

	getDB();

	$q = $db->query("SELECT hash, name FROM file");

	if (!empty($_COOKIE['username']))	{
		echo '<h2>LOGGED IN AS ' . $_COOKIE['username'] . '</h2>';
	}

?>

<h1>FILES LIST</h1>
<table>
	<?php while ($row = $q->fetch(PDO::FETCH_ASSOC)): ?>
	<tr>
		<td><?= $row['name'] ?> <a href="<?= get_download_link($row['hash']) ?>">DOWNLOAD</a></td>
	</tr>
	<?php endwhile; ?>
</table>
