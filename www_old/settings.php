<?php
include_once "design.php";
site_header("Settings");

$xml = loadconfig();

$message = "";

if (isset($_REQUEST['length']) && isset($_REQUEST['on']) && isset($_REQUEST['off']) && isset($_REQUEST['enabled'])) {
	$length = $_REQUEST['length'];
	$on = $_REQUEST['on'];
	$off = $_REQUEST['off'];
	$enabled = $_REQUEST['enabled'];
	
	foreach ($xml->children() as $child) {
		if ($child->getName()=="settings") {
			$child->length = $length;
			$child->enable = $enabled;
			$child->command->on = $on;
			$child->command->off = $off;
		}
	}
	
	saveconfig($xml);
	
	$message = " - <i>Settings Saved</i>";
}

$length="";
$on="";
$off="";
$enabled1="";
$enabled2="";

//get the settings
foreach ($xml->children() as $child) {
	if ($child->getName()=="settings") {
		foreach ($child->children() as $setting) {
			if ($setting->getName() == "length") {
				$length = $setting;
			} else if ($setting->getName() == "command") {
				$on = $setting->on;
				$off = $setting->off;
			} else if ($setting->getName() == "enable") {
				$selected="checked=\"checked\"";
				if ($setting=="0") $enabled2=$selected;
				else $enabled1=$selected;
			}
		}
	}
}
?>
<form action="settings.php" method="post">
	<b>Settings</b><?php echo $message; ?><br />
	System Enabled: <input type="radio" name="enabled" value="1" <?php echo $enabled1; ?> /> On <input type="radio" name="enabled" value="0"  <?php echo $enabled2; ?> /> Off <br />
	Length of Ring: <input type="text" name="length" value="<?php echo htmlentities($length); ?>" /> seconds (will only work if 0&lt;length&lt;30)<br />
	<br /><b>Advanced Settings</b> - Please don't edit these unless you know what you are doing. =)<br />
	Turn ON command: <input type="text" name="on" value="<?php echo htmlentities($on); ?>" size="75" /><br />
	Turn OFF command: <input type="text" name="off" value="<?php echo htmlentities($off); ?>" size="75" /><br />
	<input type="submit" value="Save" />
</form>
<?php
site_footer();
?>
