<?php
require_once "design.php";
site_header("Schedules");

const max_hours   = 23;
const max_minutes = 59;
$columns = 3;

$xml = config_load();
$saved = false;
$schedules = array();

if (isset($_REQUEST['save'])) {
	$names = $_REQUEST['name'];
	$hours = (isset($_REQUEST['hour']))?$_REQUEST['hour']:null;
	$minutes = (isset($_REQUEST['minute']))?$_REQUEST['minute']:null;

	if (($hours == null && $minutes == null) || (is_array($hours) && is_array($minutes) && count($hours) == count($minutes))) {
		$config = array();

		$dom = dom_import_simplexml($xml->schedules);
		$dom->parentNode->removeChild($dom);
		$xml->addChild("schedules");

		foreach ($names as $name_key => $name) {
			$times = array();

			if (isset($hours[$name_key]) && $hours[$name_key] != null ) {
				foreach ($hours[$name_key] as $hour_key => $hour) {
					$times[] = $hours[$name_key][$hour_key] . ":" . $minutes[$name_key][$hour_key];
				}
			}

			//remove it??? we already did...

			foreach ($xml->children() as $child_key => $child) {
				if ($child->getName() == "schedules") {
					$new = $child->addChild("schedule");
					$new["id"] = $name_key;
					$new["name"] = addslashes(html_entity_decode($name));

					foreach ($times as $time_key => $time) {
						$new->addChild("time");
						$new->time[$time_key] = $time;
					}

					break;
				}
			}
		}

		config_save($xml);
		$saved = true;
	}
}

foreach ($xml->children() as $child) {
	if ($child->getName() == "schedules") {
		foreach ($child->children() as $schedule) {
			if ($schedule->getName() == "schedule") {
				$id    = $schedule["id"];
				$name  = $schedule["name"];
				$times = array();
				
				foreach ($schedule->children() as $time)
					$times[] = $time;

				$schedules[] = array($id, $name, $times);
			}
		}
		
		break;
	}
}

$total = count($schedules);
$change = "onchange=\"window.needToConfirm=true\"";

function add_time($id, $key, $hour, $minute, $separator="'") {
	global $change;
	
	echo "<div class=\"time\" id=\"time_${id}_${key}\"><span>::</span> <select name=\"hour[$id][$key]\" $change>";

	for ($i=0; $i <= max_hours; ++$i) {
		echo "<option value=\"$i\"" . (($i==$hour)?" selected=\"selected\"":"") . ">$i</option>";
	}

	echo "</select> : <select name=\"minute[$id][$key]\" $change>";

	for ($i=0; $i <= max_minutes; ++$i) {
		$is = sprintf("%02d", $i);
		echo "<option value=\"$is\"" . (($i==$minute)?" selected=\"selected\"":"") . ">$is</option>";
	}

	echo "</select> <a href=\"javascript:void(0)\" onclick=\"return remove_time($separator$id$separator, $separator$key$separator)\">x</a></div>";
}

?>
<script type="text/javascript">
window.onload = function() {
	//http://forums.devshed.com/javascript-development-115/javascript-get-all-elements-of-class-abc-24349.html
	if (document.getElementsByClassName == undefined) {
		document.getElementsByClassName = function(className) {
			var hasClassName = new RegExp("(?:^|\\s)" + className + "(?:$|\\s)");
			var allElements = document.getElementsByTagName("*");
			var results = [];

			var element;
			for (var i = 0; (element = allElements[i]) != null; i++) {
				var elementClass = element.className;
				if (elementClass && elementClass.indexOf(className) != -1 && hasClassName.test(elementClass))
					results.push(element);
			}

			return results;
		}
	}

	get_ids()
};

function get_ids() {
	divs  = document.getElementsByClassName("schedule")
	lists = new Array()
	window.schedule_times = new Array()

	for (i=0;i<divs.length;i++) {
		if (divs[i].id != "new_schedule") {
			//schedule ids
			id = divs[i].id.split("_")[1]
			lists.push(id)

			//time ids
			children = document.getElementById("sortable_" + id).getElementsByTagName("li")
			window.schedule_times[id]=(children.length-1)
		}
	}

	//get biggest schedule id
	biggest=1

	for (i=0;i<lists.length;i++) {
		if (parseInt(lists[i]) > biggest) biggest = lists[i]
	}

	window.maxid=biggest
}

$(function() {
<?php
for ($i=0; $i < $total; ++$i) {
	$id=$schedules[$i][0];
	echo "\t$( \"#sortable_$id\" ).sortable();\n\t\$( \"#sortable_$id\" ).disableSelection();\n";
}
?>
});

function remove_id(id) {
	elem = document.getElementById(id)
	elem.parentNode.removeChild(elem)
}

function remove_schedule(id) {
	remove_id("schedule_" + id)

	return false
}

function remove_time(id, key) {
	remove_id("time_" + id + "_" + key)

	return false
}

function add_time(id) {
	window.needToConfirm = true
	++window.schedule_times[id]
	new_id = window.schedule_times[id]

	container = document.getElementById("sortable_" + id)
	li = document.createElement('li')
	li.innerHTML = '<?php add_time("'+id+'", "'+new_id+'", 0, 0, ""); ?>'
	container.appendChild(li)

	return false
}

function add_schedule() {
	window.needToConfirm = true
	++window.maxid
	id=window.maxid

	window.schedule_times[id] = 0

	container = document.getElementsByClassName("schedules")[0]

	schedule = document.createElement('div')
	container.appendChild(schedule)
	schedule.className = "schedule"
	schedule.id    = "schedule_" + id

	namediv = document.createElement('div')
	schedule.appendChild(namediv)
	namediv.className = "name"

	input = document.createElement('input')
	namediv.appendChild(input)
	input.type = "text"
	input.name = "name[" + id + "]"

	remove = document.createElement('a')
	namediv.appendChild(remove)
	remove.href = "javascript:void(0)"
	remove.onclick = (function(id){
		return function() { return remove_schedule(id); }
	})(id)
	remove.innerHTML = " x"
	
	times = document.createElement('div')
	schedule.appendChild(times)
	times.className = "times"

	ul = document.createElement('ul')
	times.appendChild(ul)
	ul.id = "sortable_" + id

	new_link = document.createElement('div')
	schedule.appendChild(new_link)
	new_link.className = "new"

	link = document.createElement('a')
	new_link.appendChild(link)
	link.href = "javascript:void(0)"
	link.onclick = (function(id){
		return function() { return add_time(id); }
	})(id)
	link.innerHTML = "+"

	$(function() {
		$( "#sortable_" + id ).sortable();
		$( "#sortable_" + id ).disableSelection();
	});

	return false
}
</script>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php echo saved($saved); ?>
<div class="schedules">
<?php
for ($q=0; $q < $total; ++$q) {
	$id    = $schedules[$q][0];
	$name  = $schedules[$q][1];
	$times = $schedules[$q][2];

	echo <<<EOF
<div class='schedule' id='schedule_$id'>
	<div class="name">
		<input type="text" name="name[$id]" value="$name" $change /> <a href="javascript:void(0)" onclick="return remove_schedule('$id')">x</a>
	</div>
	<div class="times">
	<ul id="sortable_$id">
EOF;
	foreach ($times as $key=>$time) {
		$parts = explode(":", $time);
		echo "\n\t\t<li>";
		add_time($id, $key, $parts[0], $parts[1]);
		echo "</li>\n";
	}
echo <<<EOF
	</ul>
	<div class="new"><a href="javascript:void(0)" onclick="return add_time('$id')">+</a></div>
	</div>
</div>
EOF;
}
?>
</div>
<div class="new_schedule"><div class="schedule" id='new_schedule'><a href="javascript:void(0)" onclick="return add_schedule()">+</a></div></div>
</form>
<?php site_footer(); ?>
