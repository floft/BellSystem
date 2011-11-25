<?php
require_once "design.php";
site_header("Calendar");
?>
<script type="text/javascript">
window.onload = function() {
	$("#various1").fancybox({
		'titlePosition'         : 'inside',
		'transitionIn'          : 'none',
		'transitionOut'         : 'none'
	});
}
</script>
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
<a id="various1" href="#inline1">5:30 on 11/23/2011 - 6:30 on 11/25/2011 from 7:00 - 7:30</a><br />
<br />

<div class="section">Override Schedules</div>
</form>

<div style="display:none;">
	<div id="inline1" style="width:400px;height:100px;overflow:auto;">
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam quis mi eu elit tempor facilisis id et neque. Nulla sit amet sem sapien. Vestibulum imperdiet porta ante ac ornare. Nulla et lorem eu nibh adipiscing ultricies nec at lacus. Cras laoreet ultricies sem, at blandit mi eleifend aliquam. Nunc enim ipsum, vehicula non pretium varius, cursus ac tortor. Vivamus fringilla congue laoreet. Quisque ultrices sodales orci, quis rhoncus justo auctor in. Phasellus dui eros, bibendum eu feugiat ornare, faucibus eu mi. Nunc aliquet tempus sem, id aliquam diam varius ac. Maecenas nisl nunc, molestie vitae eleifend vel, iaculis sed magna. Aenean tempus lacus vitae orci posuere porttitor eget non felis. Donec lectus elit, aliquam nec eleifend sit amet, vestibulum sed nunc.
	</div>
</div>
<?php site_footer(); ?>
