<?php

	$user_id = require_user_id();
	$title = 'My files';
	include 'head.php';

	getDB();

	$q = $db->query("SELECT hash, name FROM file");

?>

<div class="pure-g">
	<div class="pure-u-1-4">
		<div style="padding: 1em">
			<h1>FILES LIST</h1>
			<a class="pure-button pure-button-primary" href="upload">UPLOAD FILES</a>
		</div>
	</div>
	<div class="pure-u-3-4">
		<div style="padding: 1em">
			<table class="pure-table pure-table-horizontal">
				<thead>
					<tr>
						<th>Filename</th>
						<th>Download link</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($row = $q->fetch(PDO::FETCH_ASSOC)): ?>
					<tr>
						<td><?= $row['name'] ?></td>
						<td><a href="<?= get_download_link($row['hash']) ?>">DOWNLOAD</a></td>
					</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
	include 'tail.php';
?>
