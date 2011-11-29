<?php
require_once "design.php";
site_header("Calendar");

$xml = config_load();
$saved = false;
$defaults = array();
$schedules = array();

if (isset($_REQUEST['save'])) {
	$defaults = $_REQUEST['default'];

	foreach ($defaults as $key => $default)
		$xml->calendar->default->exec[$key] = $default;

	$saved = true;
	config_save($xml);
}

if (isset($xml->schedules->schedule))
	foreach ($xml->schedules->schedule as $schedule)
		$schedules[] = array((string)$schedule["id"], (string)$schedule["name"]);

if (isset($xml->calendar->default->exec))
	foreach ($xml->calendar->default->exec as $exec)
		$defaults[] = (string)$exec;

while (count($defaults) < 7)
	$defaults[] = array();
?>
<script type="text/javascript">
function close() {
	document.getElementById("fancybox-close").click()
}
function box_cancel() {
	close()
	return false
}

function box_save() {
	date_start        = document.getElementsByName("start")[0].value
	date_end          = document.getElementsByName("end")[0].value
	time_start        = document.getElementsByName("hour_start")[0].value        + ":" + document.getElementsByName("minute_start")[0].value
	time_end          = document.getElementsByName("hour_end")[0].value          + ":" + document.getElementsByName("minute_end")[0].value
	time_period_start = document.getElementsByName("hour_period_start")[0].value + ":" + document.getElementsByName("minute_period_start")[0].value
	time_period_end   = document.getElementsByName("hour_period_end")[0].value   + ":" + document.getElementsByName("minute_period_end")[0].value

	if ((time_start != "0:00" || time_end != "23:59") && date_end == "") {
		alert("Please specify end date if you specify start/end times.")
	} else {
		close()
	}

	return false
}

window.onload = function() {
	$("#link_0").fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});

<?php
	$fields = array("end", "start");

	foreach ($fields as $field)
		echo jsDatePick($field);
?>
}
</script>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php echo saved($saved); ?>
<div class="section">Default Schedule</div>
<table>
<?php
foreach ($days_of_week as $key=>$dow)
{
	echo "<tr>\n\t<td class=\"head\">$dow</td>\n\t<td><select name=\"default[$key]\">\n\t";
	echo "\t<option value=''>     </option>\n\t";
	for ($i=0; $i<count($schedules); ++$i) {
		$selected = ($defaults[$key] === $schedules[$i][0])?" selected=\"selected\"":"";
		
		echo "\t<option value='{$schedules[$i][0]}'$selected>{$schedules[$i][1]}</option>\n\t";
	}
	echo "</select><td>\n</tr>";
}
?>
</table>
<br />

<div class="section">Quiet Periods</div>
<a id="link_0" href="#box">Edit</a> 11/23/2011 - 11/25/2011 from 00:00 - 23:59<br />
<a id="link_1" href="#box">Edit</a> 5:30 on 11/23/2011 - 6:30 on 11/25/2011 from 7:00 - 7:30<br />
<br />

<div class="section">Override Schedules</div>

<div style="display:none;">
	<div id="box">
		<table>
		<tr>
			<td class="section" colspan="5">Date Range</td>
		</tr>
		<tr>
			<td class="head">Start date</td>
			<td><input type="text" name="start" id="start" value="" /></td>
			<td class="middle_horizontal"></td>
			<td class="head">Start time<sup>2</sup></td>
			<td><?php time_select("_start"); ?></td>
		</tr>
		<tr>
			<td class="head">End date<sup>1</sup></td>
			<td><input type="text" name="end" id="end" value="" /></td>
			<td class="middle"></td>
			<td class="head">End time<sup>2</sup></td>
			<td><?php time_select("_end", max_hours, max_minutes); ?></td>
		</tr>
		<tr>
			<td class="middle_vertical" colspan="5"></td>
		</tr>
		<tr>
			<td class="section" colspan="5">Time During Period</td>
		</tr>
		<tr>
			<td colspan="5">
				<?php time_select("_period_start"); ?>
				&mdash;
				<?php time_select("_period_end", max_hours, max_minutes); ?>
			</td>
		</tr>
		</table>

		<div class="note">
			<sup>1</sup> Don't specify if this is for a single day.<br />
			<sup>2</sup> If specified, you must also have an end date.
		</div>
		<div class="buttons">
			<input type="submit" class="buttons" value="Okay" onclick="return box_save()" />
			<input type="submit" class="buttons" value="Cancel" onclick="return box_cancel()" />
		</div>
	</div>
</div>
</form>
<?php site_footer(); ?>
