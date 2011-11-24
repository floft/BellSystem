<?php
require_once "design.php";
site_header("Calendar");
?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<?php echo saved(false); ?>
<div class="section">Default Schedule</div>
<table>
<?php
$schedules = array(
	array("id"=>"", "name"=>"none"),
	array("id"=>"cow", "name"=>"test 1"),
	array("id"=>"cow2", "name"=>"test 2")
);
foreach ($days_of_week as $key=>$dow)
{
	echo "<tr>\n\t<td class=\"head\">$dow</td>\n\t<td><select name=\"default_$key\">\n\t";
	for ($i=0; $i<count($schedules); ++$i)
		echo "\t<option value='{$schedules[$i]["id"]}'>{$schedules[$i]["name"]}</option>\n\t";
	echo "</select><td>\n</tr>";
}
?>
</table>
<br />

<div class="section">Quiet Periods</div>
11/23/2011 - 11/25/2011 from 00:00 - 23:59<br />
5:30 on 11/23/2011 - 6:30 on 11/25/2011 from 7:00 - 7:30<br />
<br />

<div class="section">Override Schedules</div>
</form>
<?php site_footer(); ?>
