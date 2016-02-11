<?php
	$user_id = require_user_id();

	if ($_SERVER['REQUEST_METHOD'] == 'POST')	{
		if (!empty($_FILES['upload']))	{
			$error = $_FILES['upload']['error'];
			if ($error == 0)	{
				$hash = process_upload($_FILES['upload']);

				if (!empty($hash))	{
					header("Location: upload_status?hash=$hash");
					exit;
				}
				else {
					echo '<h1>PROCESS UPLOAD FAILED</h1>';	// TODO
				}
			} else {
				echo '<h1>ERRORS</h1>';	// TODO
			}
		}
	}

	include 'head.php';
?>

<div class="pure-g">
	<div class="pure-u-1-2">
		<div style="padding: 1em">
			<h1>UPLOAD FILES</h1>
		</div>
	</div>
	<div class="pure-u-1-2">
		<div style="padding: 1em">
			<form enctype="multipart/form-data" action="" method="POST">
				<input type="hidden" name="MAX_FILE_SIZE" value="10000" />
				Choose file to upload: <input name="upload" type="file" /><br/>
				<input type="submit" value="UPLOAD FILE" />
			</form>
		</div>
	</div>
</div>

<?php include 'tail.php'; ?>
