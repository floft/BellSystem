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

function remove_schedule(id) {
	elem = document.getElementById("schedule_" + id)
	elem.parentNode.removeChild(elem)
}

function add_time(id) {
	elem = document.getElementById(id)
}
// -->
</script>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php echo saved($saved); ?>

<table class='schedules'><tr>
<?php
for ($q=0; $q < $total; ++$q) {
	$num = $q + 1;
	$id    = $schedules[$q][0];
	$name  = $schedules[$q][1];
	$times = $schedules[$q][2];

	echo <<<EOF
<td><div class='schedule' id='schedule_$id'>
	<div class="name">
		<input type="text" name="name[$id]" value="$name" $change /> <a href="javascript:void(0)" onclick="remove_schedule('$id')">x</a>
	</div>
	<div class="times">
	<ul id="sortable_$id">
EOF;
	foreach ($times as $key=>$time) {
		$parts = explode(":", $time);

		echo "\n\t<li> <span>::</span> <select name='hour[$id][$key]' $change>\n\t\t";

		for ($i=0; $i <= max_hours; ++$i) {
			echo "<option value='$i'" . (($i==$parts[0])?" selected=\"selected\"":"") . ">$i</option>";
		}

		echo "\n\t</select> : <select name='minute[$id][$key]' $change>\n\t\t";

		for ($i=0; $i <= max_minutes; ++$i) {
			$is = sprintf("%02d", $i);
			echo "<option value='$is'" . (($i==$parts[1])?" selected=\"selected\"":"") . ">$is</option>";
		}

		echo "\n\t</select></li>\n";
	}
echo <<<EOF
	</ul>
	<div class="new"><a href="javascript:void(0)" onclick="add_time('$id')">+</a></div>
	</div>
</div></td>
EOF;

	if ($num%$columns == 0)
	{
		echo "</tr>";

		if ($q+1 < $total)
			echo "<tr>";
	}
}

if ($num%$columns == 0)
	echo "<tr><td><div class='schedule new_schedule'><a href=\"javascript:void(0)\" onclick=\"add_schedule()\">+</a></div></td>";
else
	echo "<td><div class='schedule new_schedule'><a href=\"javascript:void(0)\" onclick=\"add_schedule()\">+</a></div></td>";
++$q;

for (;$q%$columns != 0; ++$q)
{
	echo "<td></td>";

	if (($q+1)%$columns == 0)
		echo "</tr>";
}
?>

</table>
</form>
<?php site_footer(); ?>
