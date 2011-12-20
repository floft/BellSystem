<?php
require_once "design.php";

$saved = false;
$error = "";
define("MAX_SIZE", 1048576);

$error_messages = array(
	1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
	2=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
	3=>'The uploaded file was only partially uploaded.',
	4=>'No file was uploaded.',
	6=>'Missing a temporary folder.',
	7=>'Failed to write file to disk.',
	8=>'A PHP extension stopped the file upload.'
); 

if (isset($_REQUEST['backup']))
{
	header('Content-type: "text/xml"; charset="utf8"');
	header('Content-disposition: attachment; filename="bellsystem-config-'.date("YmdHi").'.xml"');

	$xml = config_load();
	echo $xml->saveXML();
	exit(0);
}
else if (isset($_FILES['restore']))
{
	if ($_FILES['restore']['error'] != UPLOAD_ERR_OK)
	{
		$error=$error_messages[$_FILES['restore']['error']];
	}
	else
	{
		if ($xml = simplexml_load_file($_FILES['restore']['tmp_name']))
		{
			config_save($xml);
			$saved = true;
		}
		else
		{
			$error = "Invalid XML";
		}
	}
}

site_header("Backup/Restore");
?>
<script type="text/javascript">
<!--
window.onload = function() {
	setTimeout("document.getElementById('saved').style.display = 'none'", 1000)
}
// -->
</script>
<form enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php echo saved($saved); ?>

<div class="section">Backup</div>
<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?backup">Download Config</a>
<br /><br />

<div class="section">Restore</div>
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_SIZE; ?>" />
<input type="file"   name="restore" />
</form>

<?php if ($error != "") echo "<br /><div class='red'>Error: $error</div>"; ?>
<?php site_footer(); ?>
