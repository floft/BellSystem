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
window.box_id    = -1
window.quiets    = 0
window.overrides = 0

function populate() {
	inputs = document.getElementsByTagName("input")

	for (i=0; i<inputs.length; ++i)
	{
		parts = inputs[i].id.split("_")
		name  = parts[0]
		id    = parseInt(parts[1])

		if (name == "quiet")
			if (window.quiets < id)
				window.quiets = id
		else if (name == "override")
			if (window.overrides < id)
				window.overrides = id
	}
}

function close() {
	window.fb.fancybox.close(document.getElementById("fancybox-close"))
}
function box_cancel() {
	close()
	return false
}

function box_save() {
	window.needToConfirm = true

	date_start        = document.getElementsByName("start")[0].value
	date_end          = document.getElementsByName("end")[0].value
	time_start        = document.getElementsByName("hour_start")[0].value + ":" + document.getElementsByName("minute_start")[0].value
	time_end          = document.getElementsByName("hour_end")[0].value   + ":" + document.getElementsByName("minute_end")[0].value

	input = date_start.replace(/\//g,"")
	text  = date_start

	if (date_end != "")
	{
		input += "-" + date_end.replace(/\//g,"")
		text  += " - " + date_end
	}

	if (time_start != "0:00" || time_end != "23:59")
	{
		input += "@" + time_start + "-" + time_end
		text  += " from " + time_start + "-" + time_end
	}
	
	document.getElementById(window.box_id).value               = input
	document.getElementById(window.box_id + "_link").innerHTML = text
	
	close()

	window.box_id = -1

	return false
}

function box_open(input_id) {
	window.box_id = input_id

	start		= ""
	end		= ""
	hour_start	= "0"
	minute_start	= "00"
	hour_end	= "23"
	minute_end	= "59"

	parts = document.getElementById(input_id).value.split("@")

	if (parts.length == 2)
	{
		time_parts  = parts[1].split("-")
		start_parts = time_parts[0].split(":")
		end_parts   = time_parts[1].split(":")

		hour_start   = start_parts[0]
		minute_start = start_parts[1]
		hour_end     = end_parts[0]
		minute_end   = end_parts[1]
	}
	
	date_parts  = parts[0].split("-")
	start       = date_parts[0].substr(0,4) + "/" + date_parts[0].substr(4,2) + "/" + date_parts[0].substr(6,2)

	if (date_parts.length == 2)
		end = date_parts[1].substr(0,4) + "/" + date_parts[1].substr(4,2) + "/" + date_parts[1].substr(6,2)

	document.getElementsByName("start")[0].value        = start
	document.getElementsByName("end")[0].value          = end
	document.getElementsByName("hour_start")[0].value   = hour_start
	document.getElementsByName("minute_start")[0].value = minute_start
	document.getElementsByName("hour_end")[0].value     = hour_end
	document.getElementsByName("minute_end")[0].value   = minute_end
}

function print_date(slashes) {
	now   = new Date()
	year  = now.getFullYear()
	month = now.getMonth()
	day   = now.getDate()

	if (month.length == 1)
		month = "0" + month
	if (day.length == 1)
		day = "0" + day
	
	if (slashes)
		return year + "/" + month + "/" + day
	else
		return "" + year + month + day
}

function remove_item(id) {
	window.needToConfirm = true

	elem = document.getElementById(id).parentNode
	elem.parentNode.removeChild(elem)
}

function add_quiet() {
	window.needToConfirm = true
	++window.quiets
	id = window.quiets

	container = document.getElementById("quiets")

	li = document.createElement('li')
	container.appendChild(li)

	div = document.createElement('div')
	li.appendChild(div)
	div.id = "quiet_" + id + "_div"

	input = document.createElement('input')
	div.appendChild(input)
	input.type = "hidden"
	input.name = "quiet[" + id + "]"
	input.id = "quiet_" + id
	input.value = print_date(false)

	del = document.createElement('a')
	div.appendChild(del)
	del.href = "javascript:void(0)"
	del.className = "x"
	del.onclick = (function(id){
		return function() { return remove_item("quiet_" + id + "_div"); }
	})(id)
	del.innerHTML = "x "

	link = document.createElement('a')
	div.appendChild(link)
	link.className = "period"
	link.id = "quiet_" + id + "_link"
	link.href = "#box"
	link.onclick = (function(id){
		return function() { return box_open("quiet_" + id + "_div"); }
	})(id)
	link.innerHTML = print_date(true)
	
	$(link).fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});

	return false
}

function add_override() {
	window.needToConfirm = true

	add_period(document.getElementById("overrides"))

	return false
}

window.onload = function() {
	$("#quiet_0_link").fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});
	$("#quiet_1_link").fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});
	$("#quiet_2_link").fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});

	populate()

	$( "#quiets" ).sortable();
	$( "#quiets" ).disableSelection();
	$( "#overrides" ).sortable();
	$( "#overrides" ).disableSelection();

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

<div class="section">Quiet Periods &ndash; <a href="javascript:void(0)" onclick="return add_quiet()">Add</a></div>
<ul id="quiets">
	<li><div id="quiet_0_div">
		<input type="hidden" name="quiet[0]" id="quiet_0" value="20111123-20111125" />
		<a class="x" href="javascript:void(0)" onclick="return remove_item('quiet_0_div')">x</a>
		<a class="period" id="quiet_0_link" href="#box" onclick="return box_open('quiet_0')">2011/11/23 - 2011/11/25</a>
	</div></li>
	<li><div id="quiet_1_div">
		<input type="hidden" name="quiet[1]" id="quiet_1" value="20111123-20111125@7:00-7:30" />
		<a class="x" href="javascript:void(0)" onclick="return remove_item('quiet_1_div')">x</a>
		<a class="period" id="quiet_1_link" href="#box" onclick="return box_open('quiet_1')">2011/11/23 - 2011/11/25 from 7:00 - 7:30</a>
	</div></li>
	<li><div id="quiet_2_div">
		<input type="hidden" name="quiet[2]" id="quiet_2" value="20111225" />
		<a class="x" href="javascript:void(0)" onclick="return remove_item('quiet_2_div')">x</a>
		<a class="period" id="quiet_2_link" href="#box" onclick="return box_open('quiet_2')">2011/12/25</a>
	</div></li>
</ul><br />

<div class="section">Override Schedules &ndash; <a href="javascript:void(0)" onclick="return add_override()">Add</a></div>
<div id="overrides">

</div>

<div style="display:none;">
	<div id="box">
		<table>
		<tr>
			<td class="section" colspan="5">Time Period</td>
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
		</table>

		<div class="note">
			<sup>1</sup> Don't specify if this is for a single day.<br />
			<sup>2</sup> The time during each of the selected days.
		</div>
		<div class="buttons">
			<input type="submit" class="buttons" value="Okay" onclick="return box_save()" />
			<input type="submit" class="buttons" value="Cancel" onclick="return box_cancel()" />
		</div>
	</div>
</div>
</form>
<?php site_footer(); ?>
