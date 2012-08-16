<?php
require_once "design.php";
site_header("Settings");

$saved = false;

//also specified in the daemon
define("min_length", 1);
define("max_length", 10);

if (isset($_REQUEST['save'])) {
	$length = $_REQUEST['length'];
	$start  = $_REQUEST['start'];
	$end    = $_REQUEST['end'];

	$xml->settings->length = $length;
	$xml->settings->start  = str_replace("/","",$start);
	$xml->settings->end    = str_replace("/","",$end);

	config_save($xml);
	$saved = true;
}

$length = 3;
$device = "";
$gpio   = false;
$start  = "";
$end    = "";

foreach ($xml->settings->children() as $setting) {
	$name = $setting->getName();

	if ($name == "length")
		$length = $setting;
	else if ($name == "start")
		$start  = $setting;
	else if ($name == "end")
		$end    = $setting;
	else if ($name == "device")
		$device = $setting;
	else if ($name == "gpio")
		$gpio = ($setting == "True" || $setting == "TRUE" || $setting == "true" || $setting == "1");
}
?>
<script type="text/javascript">
<!--
window.onload = function() {
<?php
//$fields = array("start", "end");
$fields = array("end", "start");

foreach ($fields as $field)
	echo jsDatePick($field);
?>

	setTimeout("document.getElementById('saved').style.display = 'none'", 1000)
};

function check() {
	start = document.getElementById("start").value
	end   = document.getElementById("end").value

	if ( /[^0-9\/]/.test(start) ||
	     /[^0-9\/]/.test(end)   ||
	     start.length != 10     ||
	     end.length   != 10     )
	{
		alert("Please use valid values (e.g. 2000/01/01)")
		return false
	}

	return true
}
// -->
</script>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" onsubmit="return check()">
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
</tr><tr>
	<td class="head">Use GPIO<sup>2</sup></td>
	<td><input type="text" name="gpio" value="<?php echo ($gpio)?"True":"False"; ?>" disabled="disabled" /></td>
</tr></table>
</form>

<br />
<p>
<sup>1</sup> The bell system will be enabled between these dates.<br />
<sup>2</sup> This is for reference; you can't change this from this web UI.
</p>
<?php site_footer(); ?>
