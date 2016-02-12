<?php
	$user_id = require_user_id();

	if ($_SERVER['REQUEST_METHOD'] == 'POST')	{
		if (!empty($_FILES['upload']))	{
			$error = $_FILES['upload']['error'];
			if ($error == 0)	{
				$hash = process_upload($_FILES['upload']);

				if (!empty($hash))	{
					redirect("upload_status?hash=$hash");
				}
				else {
					echo '<h1>PROCESS UPLOAD FAILED</h1>';	// TODO
				}
			} else {
				echo '<h1>ERRORS</h1>';	// TODO
			}
		}
	}

	$title = "Upload files";
	include 'head.php';
?>

<form enctype="multipart/form-data" action="" method="POST">
	<input type="hidden" name="MAX_FILE_SIZE" value="10000" />
	<div class="pure-g">
		<div class="pure-u-1-5">
			<div class="my-header">
				<a href="list_files">BACK TO LIST VIEW</a>
			</div>
		</div>
		<div class="pure-u-1-5">
			<div class="my-header">
				<span>Choose file to upload:</span>
			</div>
		</div>
		<div class="pure-u-1-5">
			<div class="my-header">
				<input name="upload" type="file" />
			</div>
		</div>
		<div class="pure-u-1-5">
			<div class="my-header">
				<input type="submit" value="UPLOAD FILE" />
			</div>
		</div>
		<div class="pure-u-1-5"></div>
	</div>
</form>

<?php include 'tail.php'; ?>
