<?php
require_once "design.php";
site_header("Calendar");

$saved = false;
$defaults = array();
$schedules = array();
$quiets = array();
$overrides = array();

if (isset($xml->schedules->schedule))
	foreach ($xml->schedules->schedule as $schedule)
		$schedules[] = array((string)$schedule["id"], (string)$schedule["name"]);

if (isset($_REQUEST['save'])) {
	$defaults  = $_REQUEST['default'];
	$quiets    = (isset($_REQUEST['quiet']))?$_REQUEST['quiet']:array();
	$overrides = (isset($_REQUEST['override']))?$_REQUEST['override']:array();

	$dom = dom_import_simplexml($xml->calendar);
	$dom->parentNode->removeChild($dom);
	$xml->addChild("calendar");
	$xml->calendar->addChild("default");

	for ($i=0; $i<count($days_of_week); ++$i)
	{
		$xml->calendar->default->addChild("exec");

		if (isset($defaults[$i]))
			$xml->calendar->default->exec[$i] = $defaults[$i];
	}

	$xml->calendar->addChild("quiet");

	foreach ($quiets as $key => $quiet)
	{
		$parts = explode("@", (string)$quiet);

		$xml->calendar->quiet->addChild("when", $parts[0]);
		
		if (count($parts) == 2)
		{
			$times = explode("-", $parts[1]);

			if (count($times) == 2)
			{
				$xml->calendar->quiet->when[$key]["start"] = $times[0];
				$xml->calendar->quiet->when[$key]["end"]   = $times[1];
			}
		}
	}

	$xml->calendar->addChild("override");
	
	foreach ($overrides as $key => $override)
	{
		$exec_part = explode("#", (string)$override);

		if (count($exec_part) != 2)
			continue;

		//get rid of overrides that weren't modified after adding
		$exists = false;

		foreach ($schedules as $item)
		{
			if ($item[0] == $exec_part[0])
			{
				$exists = true;
				break;
			}
		}

		if ($exists == false)
			continue;

		$parts = explode("@", $exec_part[1]);

		$xml->calendar->override->addChild("when", $parts[0]);
		$xml->calendar->override->when[$key]["exec"] = $exec_part[0];
		
		if (count($parts) == 2)
		{
			$times = explode("-", $parts[1]);

			if (count($times) == 2)
			{
				$xml->calendar->override->when[$key]["start"] = $times[0];
				$xml->calendar->override->when[$key]["end"]   = $times[1];
			}
		}
	}

	$saved = true;
	config_save($xml);
}

//read defaults
if (isset($xml->calendar->default->exec))
	foreach ($xml->calendar->default->exec as $exec)
		$defaults[] = (string)$exec;

while (count($defaults) < 7)
	$defaults[] = array();

//read quiets
function get_whens($whens)
{
	global $schedules;

	$results = array();

	foreach ($whens as $when)
	{
		$value = (string)$when;
			
		$summary = "";
		$parts = explode("-", (string)$when);

		if (isset($when["exec"]))
		{
			foreach ($schedules as $item)
			{
				if ($item[0] == (string)$when["exec"])
				{
					$summary .= "<span>${item[1]}</span> &ndash; ";
					break;
				}
			}

			$value = "${when["exec"]}#$value";
		}

		$summary .= substr($parts[0], 0, 4) . "/" . substr($parts[0], 4, 2) . "/" . substr($parts[0], 6, 2);

		if (count($parts) == 2)
			$summary .= " - " . substr($parts[1], 0, 4) . "/" . substr($parts[1], 4, 2) . "/" . substr($parts[1], 6, 2);

		if (isset($when["start"]) && isset($when["end"]))
		{
			$value .= "@${when["start"]}-${when["end"]}";
			$summary .= " from ${when["start"]} - ${when["end"]}";
		}

		$results[] = array($value, $summary);
	}

	return $results;
}

function print_whens($name, $whens)
{
	foreach ($whens as $id => $when)
	{
		$text = $when[0];
		$summary = $when[1];

		echo "\t<li><div id=\"${name}_${id}_div\">\n";
		echo "\t\t<input type=\"hidden\" name=\"${name}[$id]\" id=\"${name}_$id\" value=\"$text\" />\n";
		echo "\t\t<a class=\"x\" href=\"javascript:void(0)\" onclick=\"return remove_item('${name}_${id}_div')\">x</a>\n";
		echo "\t\t<a class=\"period\" id=\"${name}_${id}_link\" href=\"#box\" onclick=\"return box_open('${name}_$id')\">$summary</a>\n";
		echo "\t</div></li>\n";
	}
}

if (isset($xml->calendar->quiet->when))
	$quiets = get_whens($xml->calendar->quiet->when);

if (isset($xml->calendar->override->when))
	$overrides = get_whens($xml->calendar->override->when);
?>
<script type="text/javascript">
window.exec_box  = false
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
		{
			if (window.quiets < id)
				window.quiets = id
		}
		else if (name == "override")
		{
			if (window.overrides < id)
				window.overrides = id
		}
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

	exec = document.getElementsByName("exec")[0]

	if (window.exec_box == true && exec.options[exec.selectedIndex].value == "")
	{
		alert("Please specify what schedule to use.")
		return false
	}

	date_start        = document.getElementsByName("start")[0].value
	date_end          = document.getElementsByName("end")[0].value
	time_start        = document.getElementsByName("hour_start")[0].value + ":" + document.getElementsByName("minute_start")[0].value
	time_end          = document.getElementsByName("hour_end")[0].value   + ":" + document.getElementsByName("minute_end")[0].value

	if ( /[^0-9\/]/.test(date_start) ||
	     /[^0-9\/]/.test(date_end)   ||
	     /[^0-9:]/.test(time_start)  ||
	     /[^0-9:]/.test(time_end))
	{
		alert("Please use valid values (e.g. 2000/01/01)")
		return false
	}

	if (window.exec_box == true)
	{
		exec_value = exec.options[exec.selectedIndex].value
		exec_text  = exec.options[exec.selectedIndex].text
		
		input = exec_value + "#"
		text  = "<span>" + exec_text + "</span> &ndash; "
	}
	else
	{
		input = ""
		text  = ""
	}

	input += date_start.replace(/\//g,"")
	text  += date_start

	if (date_end != "")
	{
		input += "-" + date_end.replace(/\//g,"")
		text  += " - " + date_end
	}

	if (time_start != "0:00" || time_end != "23:59")
	{
		input += "@" + time_start + "-" + time_end
		text  += " from " + time_start + " - " + time_end
	}
	
	document.getElementById(window.box_id).value               = input
	document.getElementById(window.box_id + "_link").innerHTML = text
	
	close()

	window.box_id   = -1
	window.exec_box = false

	return false
}

function box_open(input_id) {
	window.box_id = input_id

	exec            = ""
	start		= ""
	end		= ""
	hour_start	= "0"
	minute_start	= "00"
	hour_end	= "23"
	minute_end	= "59"

	exec_parts = document.getElementById(input_id).value.split("#")

	if (exec_parts.length == 2)
	{
		window.exec_box = true
		exec = exec_parts[0]
		parts = exec_parts[1].split("@")
	}
	else
	{
		window.exec_box = false
		parts = exec_parts[0].split("@")
	}

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

	if (exec_parts.length == 2)
	{
		elem = document.getElementsByName("exec")[0]

		for (i=0; i < elem.options.length; ++i)
		{
			if (elem.options[i].value == exec)
			{
				elem.options[i].selected = "selected"
				break
			}
		}

		document.getElementById("exec_head").style.display    = ""
		document.getElementById("exec_section").style.display = ""
	}
	else
	{
		document.getElementById("exec_head").style.display    = "none"
		document.getElementById("exec_section").style.display = "none"
	}
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

	return false
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
	input.className = "hidden"
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
		return function() { return box_open("quiet_" + id); }
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
	++window.overrides
	id = window.overrides

	container = document.getElementById("overrides")

	li = document.createElement('li')
	container.appendChild(li)

	div = document.createElement('div')
	li.appendChild(div)
	div.id = "override_" + id + "_div"

	input = document.createElement('input')
	div.appendChild(input)
	input.className = "hidden"
	input.name = "override[" + id + "]"
	input.id = "override_" + id
	input.value = "#" + print_date(false)

	del = document.createElement('a')
	div.appendChild(del)
	del.href = "javascript:void(0)"
	del.className = "x"
	del.onclick = (function(id){
		return function() { return remove_item("override_" + id + "_div"); }
	})(id)
	del.innerHTML = "x "

	link = document.createElement('a')
	div.appendChild(link)
	link.className = "period"
	link.id = "override_" + id + "_link"
	link.href = "#box"
	link.onclick = (function(id){
		return function() { return box_open("override_" + id); }
	})(id)
	link.innerHTML = "<span>unknown</span> &ndash; " + print_date(true)
	
	$(link).fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});

	return false
}

window.onload = function() {
<?php
foreach ($quiets as $id => $quiet) 
{
	echo "\t$(\"#quiet_${id}_link\").fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});\n";
}

foreach ($overrides as $id => $override) 
{
	echo "\t$(\"#override_${id}_link\").fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});\n";
}
?>

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

	setTimeout("document.getElementById('saved').style.display = 'none'", 1000)
}
</script>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php echo saved($saved); ?>
<div class="section">Default Schedule</div>
<table>
<?php
foreach ($days_of_week as $key=>$dow)
{
	echo "<tr>\n\t<td class=\"head\">$dow</td>\n\t<td><select name=\"default[$key]\" onchange=\"window.needToConfirm=true\">\n\t";
	echo "\t<option value=''>     </option>\n\t";
	for ($i=0; $i<count($schedules); ++$i) {
		$selected = ($defaults[$key] === $schedules[$i][0])?" selected=\"selected\"":"";
		
		echo "\t<option value='{$schedules[$i][0]}'$selected>{$schedules[$i][1]}</option>\n\t";
	}
	echo "</select></td>\n</tr>";
}
?>
</table>
<br />

<div class="section">Quiet Periods &ndash; <a href="javascript:void(0)" onclick="return add_quiet()">Add</a></div>
<ul id="quiets">
<?php print_whens("quiet", $quiets); ?>
</ul><br />

<div class="section">Override Schedules &ndash; <a href="javascript:void(0)" onclick="return add_override()">Add</a></div>
<ul id="overrides">
<?php print_whens("override", $overrides); ?>
</ul>

<div style="display:none;">
	<div id="box">
		<table>
		<tr>
			<td class="section" colspan="5">Time</td>
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
			<td class="middle_horizontal"></td>
			<td class="head">End time<sup>2</sup></td>
			<td><?php time_select("_end", max_hours, max_minutes); ?></td>
		</tr>
		<tr>
			<td class="middle_vertical"></td>
		</tr>
		<tr id="exec_head">
			<td class="section" colspan="5">Schedule</td>
		</tr>
		<tr id="exec_section">
			<td colspan="5">
			<select name="exec" width="300" style="width:300px">
				<option value=''>Please select one</option>
				<?php
				for ($i=0; $i<count($schedules); ++$i)
					echo "\t<option value='{$schedules[$i][0]}'>{$schedules[$i][1]}</option>\n\t";
				?>
			</select>
			</td>
		</tr>
		</table>

		<div class="note">
			<sup>1</sup> Don't specify if this is for a single day.<br />
			<sup>2</sup> The time during <i>each</i> of the selected days.
		</div>
		<div class="buttons">
			<input type="submit" class="buttons" value="Okay" onclick="return box_save()" />
			<input type="submit" class="buttons" value="Cancel" onclick="return box_cancel()" />
		</div>
	</div>
</div>
</form>
<?php site_footer(); ?>
