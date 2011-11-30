<?php
require_once "design.php";
site_header("Settings");

$saved = false;
$xml = config_load();

//also specified in the daemon
const min_length = 1;
const max_length = 10;

if (isset($_REQUEST['save'])) {
	$length = $_REQUEST['length'];
	$start  = $_REQUEST['start'];
	$end    = $_REQUEST['end'];

	foreach ($xml->children() as $child) {
		if ($child->getName() == "settings") {
			$child->length = $length;
			$child->start  = str_replace("/","",$start);
			$child->end    = str_replace("/","",$end);
		}
	}

	config_save($xml);
	$saved = true;
}

$length = 3;
$device = "";
$start  = "";
$end    = "";

foreach ($xml->children() as $child) {
	if ($child->getName() == "settings") {
		foreach ($child->children() as $setting) {
			$name = $setting->getName();

			if ($name == "length")
				$length = $setting;
			else if ($name == "start")
				$start  = $setting;
			else if ($name == "end")
				$end    = $setting;
			else if ($name == "device")
				$device = $setting;
		}

		break;
	}
}
?>
<script type="text/javascript">
window.onload = function() {
<?php
//$fields = array("start", "end");
$fields = array("end", "start");

foreach ($fields as $field)
	echo jsDatePick($field);
?>

	setTimeout("document.getElementById('saved').style.display = 'none'", 1000)
};
</script>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php echo saved($saved); ?>
<table><tr>
	<td class="head">School Start<sup>1</sup></td>
	<td>
		<input type="text" name="start" id="start" value="<?php echo from_date($start, "Y/m/d"); ?>" /> 
	</td>
</tr><tr>
	<td class="head">School End<sup>1</sup></td>
	<td>
		<input type="text" name="end" id="end" value="<?php echo from_date($end, "Y/m/d"); ?>" /> 
	</td>
</tr><tr>
	<td class="head">Length</td>
	<td><select name="length" onchange="window.needToConfirm=true"><?php 
		for ($i = min_length; $i <= max_length; ++$i)
			echo "<option value='$i'" . (($i==$length)?" selected=\"selected\"":"") . ">$i</option>";
	?></select> seconds</td>
</tr><tr>
	<td class="head">Device<sup>2</sup></td>
	<td><input type="text" name="device" value="<?php echo $device; ?>" disabled="disabled" /></td>
</tr></table>
</form>

<br />
<p>
<sup>1</sup> The bell system will be enabled between these dates.<br />
<sup>2</sup> This is for reference; you can't change this from this web UI.
</p>
<?php site_footer(); ?>
