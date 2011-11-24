<?php
require_once "design.php";
site_header("Schedules");

const max_hours   = 23;
const max_minutes = 59;
$columns = 3;

$xml = config_load();
$saved = false;
$schedules = array();

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

	echo "</select> <a href=\"javascript:void(0)\" onclick=\"remove_time($separator$id$separator, $separator$key$separator)\">x</a></div>";
}

?>
<script type="text/javascript">
<!--
$(function() {
<?php
for ($i=0; $i < $total; ++$i) {
	$id=$schedules[$i][0];
	echo "\t$( \"#sortable_$id\" ).sortable();\n\t\$( \"#sortable_$id\" ).disableSelection();\n";
}
?>
});

function remove(id) {
	elem = document.getElementById(id)
	elem.parentNode.removeChild(elem)
}

function remove_schedule(id) {
	remove("schedule_" + id)
}

function remove_time(id, key) {
	remove("time_" + id + "_" + key)
}

function add_time(id) {
	container = document.getElementById("sortable_" + id)
	li = document.createElement('li')
	li.innerHTML = '<?php add_time("", "", 0, 0, ""); ?>'
	container.insertBefore(li, null)
}

function add_schedule() {
}
// -->
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
		<input type="text" name="name[$id]" value="$name" $change /> <a href="javascript:void(0)" onclick="remove_schedule('$id')">x</a>
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
	<div class="new"><a href="javascript:void(0)" onclick="add_time('$id')">+</a></div>
	</div>
</div>
EOF;
}
?>
<div class='schedule new_schedule'><a href="javascript:void(0)" onclick="add_schedule()">+</a></div></td>
</div>
</form>
<?php site_footer(); ?>
