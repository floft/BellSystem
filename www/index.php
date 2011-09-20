<?php
include_once "design.php";
site_header("Home");

echo (isdisabled() == true)?"<p><b>WARNING:</b> the bell system is currently disabled.</p>":"";
?>
<p>
	<b>Home</b><br />
	Welcome to the SAA Bell System website! You have probably come here wanting to configure the bell system, so let us not waste any time. In order to configure this system, you should have a list of the times you want the bell to ring, what days, etc. There are three different parts to making the bell system work properly.
</p>
<ul><li><b>Create a List</b><br /><ol>
	<li>Go to the <a href="lists.php">Bell Lists</a> page and click "Add Another List."</li>
	<li>Type in a descriptive name for the list (e.g. "Friday-Morning Half").</li>
	<li>Click the "Add Another Ring" link and then select the time you want the bell to ring. This is in 24-hour time. Repeat this step for each time you want the bell to ring that day.
	<li>Once you are done, click the "Save" button.</li>
</ol><br /></li><li><b>Create a Schedule</b><br /><ol>
	<li>Go to the <a href="schedules.php">Schedules</a> page and click "Add Another Schedule."</li>
	<li>Click the "Quiet Period" drop-down box and select the name you chose in step 2 (the list above).</li>
	<li>Click the "Please Select" drop-down box. The options are described below.<br /><ul>
			<li><b>Weekly</b> - Select one or more days of the week to apply that schedule (e.g. every Friday).</li>
			<li><b>Monthly</b> - Select a range of days in a month to apply that schedule (e.g. the 1st and 2nd of every month).</li>
			<li><b>Yearly</b> - Select certian days of the year to apply that schedule (e.g. December 5-10).</li>
			<li><b>One Time</b> - Select a certian day to apply that schedule (e.g. January 1, 1969) and select whether to override all the other schedules for that day (e.g. a half-day schedule overrides the normal one).</li>
			</ul>	</li>
	<li>Once you are done, click the "Save" button.</li>
</ol><br /><b>Note:</b> If the schedule "Quite Period" is selected, this will overrule all other schedules applied to that day, and if any other days overlap, the bell will ring according to both schedules (e.g. if there is a bell at 10:15 in one schedule and 10:25 in another, both will ring).<br /><br />
</li><li><b>Finished Setup</b><br /><ol>
	<li>Go to the <a href="settings.php">Settings</a> page.</li>
	<li>Make sure that the system is enabled (click the radio button next to "On").</li>
	<li>Set the length of the bell ring. (Default: 3 seconds)</li>
	<li>Press the "Save" button.</li>
</ol><br /></li></ul>
<p>If you have any questions, please feel free to <a href="http://www.floft.net/contact?subject=SAA+Bell+System&amp;require">email me</a>.</p>
<?php
site_footer();
?>
