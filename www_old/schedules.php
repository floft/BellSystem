<?php
include_once "design.php";
site_header("Bell Schedules");

$xml = loadconfig();

$message = "";

if (isset($_REQUEST['submitted'])) {
	$exes = $_REQUEST['execute'];
	$basis = (isset($_REQUEST['basis']))?$_REQUEST['basis']:null;
	
	//weekly basis
	$week = (isset($_REQUEST['week']))?$_REQUEST['week']:null;
	//montly basis
	$month_day_start = (isset($_REQUEST['month_day_start']))?$_REQUEST['month_day_start']:null;
	$month_day_end = (isset($_REQUEST['month_day_end']))?$_REQUEST['month_day_end']:null;
	//yearly basis
	$year_day_start = (isset($_REQUEST['year_day_start']))?$_REQUEST['year_day_start']:null;
	$year_day_end = (isset($_REQUEST['year_day_end']))?$_REQUEST['year_day_end']:null;
	$year_month_start = (isset($_REQUEST['year_month_start']))?$_REQUEST['year_month_start']:null;
	$year_month_end = (isset($_REQUEST['year_month_end']))?$_REQUEST['year_month_end']:null;
	//one time event
	$once_month = (isset($_REQUEST['once_month']))?$_REQUEST['once_month']:null;
	$once_day = (isset($_REQUEST['once_day']))?$_REQUEST['once_day']:null;
	$once_year = (isset($_REQUEST['once_year']))?$_REQUEST['once_year']:null;
	$once_override = (isset($_REQUEST['once_override']))?$_REQUEST['once_override']:null;
	
	//delete everything
	if (isset($xml->schedules)) {
		$dom=dom_import_simplexml($xml->schedules);
		$dom->parentNode->removeChild($dom);
	}
	
	$xml->addChild("schedules");
	
	foreach ($exes as $exe_key => $exe) {
		//add the new one
		foreach ($xml->children() as $num => $child) {
			if ($child->getName()=="schedules") {
				$content = "";
				
				//create data
				if ($basis[$exe_key] == "week") {
					$days = $week[$exe_key];
					$days_new = array();
					
					foreach ($days as $day_key => $day) {
						$days_new[] = $day_key;
					}
					
					$content = implode(",", $days_new);
				} else if ($basis[$exe_key] == "month") {
					$content = "{$month_day_start[$exe_key]}-{$month_day_end[$exe_key]}";
				} else if ($basis[$exe_key] == "year") {
					$content = "{$year_month_start[$exe_key]}.{$year_day_start[$exe_key]}-{$year_month_end[$exe_key]}.{$year_day_end[$exe_key]}";
				} else if ($basis[$exe_key] == "once") {
					$content = "{$once_month[$exe_key]}.{$once_day[$exe_key]}.{$once_year[$exe_key]}.{$once_override[$exe_key]}";
				}
				
				//add the data
				if ($content != "") {
					$newchild = $child->addChild("when",$content);
					$newchild["id"] = $exe_key;
					$newchild["exec"] = $exe;
					$newchild["basis"] = $basis[$exe_key];
				}
			}
		}
	}
	
	saveconfig($xml);
	
	$message = " - <i>Settings Saved</i>";
}

function genContent($id,$basis,$exec,$when) {
	global $xml;
	
	$return="";
	
	$week_display = "none";
	$month_display = "none";
	$year_display = "none";
	$once_display = "none";
	
	$selected=" selected=\"selected\"";
	$checked=" checked=\"checked\"";
	
	if ($basis == "week") {
		$week_display = "inline";
		$days = split(",", $when);
	} else if ($basis == "month") {
		$month_display = "inline";
		$days = split("-",$when);
	} else if ($basis == "year") {
		$year_display = "inline";
		$parts = split("-",$when); // 0 -> 12.15, 1 -> 12.25
		
		$start = split("\.",$parts[0]); // 12 -> month, 15 -> day
		$end = split("\.",$parts[1]);
	} else if ($basis == "once") {
		$once_display = "inline";
		$parts = split("\.",$when); // 12 -> month, 15 -> day, 2010 -> year, 0|1 -> use only "One Time" schedules
	}
	
	$return .= "<tr id=\"sch_$id\"><td><b>$id</b>. Execute <select name=\"execute[$id]\">";
	$return .= "<option value=\"0\" ".(($exec==0)?$selected:"").">Quiet Period</option>";
	foreach ($xml->lists->children() as $child) {
		$child_id=(string)$child["id"];
		$child_name=$child["name"];
		
		$return .= "<option value=\"$child_id\" ".(($exec==$child_id)?$selected:"").">$child_name</option>";
	}
	$return .= "</select> on a <select name=\"basis[$id]\" onchange=\"showbasis(this,'$id')\">
	<option value=\"none\">Please Select</option>
	<option value=\"week\"".(($basis=="week")?$selected:"").">Weekly</option>
	<option value=\"month\"".(($basis=="month")?$selected:"").">Monthly</option>
	<option value=\"year\"".(($basis=="year")?$selected:"").">Yearly</option>
	<option value=\"once\"".(($basis=="once")?$selected:"").">One Time</option>
	</select> basis -- 
	<!-- weekly -->
	<span id=\"week_$id\" style=\"display:$week_display\">
	<input type=\"checkbox\" name=\"week[$id][0]\" value=\"1\" ".((($basis=="week")&&in_array("0",$days))?$checked:"")."/> Sunday 
	<input type=\"checkbox\" name=\"week[$id][1]\" value=\"1\" ".((($basis=="week")&&in_array("1",$days))?$checked:"")."/> Monday 
	<input type=\"checkbox\" name=\"week[$id][2]\" value=\"1\" ".((($basis=="week")&&in_array("2",$days))?$checked:"")."/> Tuesday
	<input type=\"checkbox\" name=\"week[$id][3]\" value=\"1\" ".((($basis=="week")&&in_array("3",$days))?$checked:"")."/> Wednesday 
	<input type=\"checkbox\" name=\"week[$id][4]\" value=\"1\" ".((($basis=="week")&&in_array("4",$days))?$checked:"")."/> Thursday 
	<input type=\"checkbox\" name=\"week[$id][5]\" value=\"1\" ".((($basis=="week")&&in_array("5",$days))?$checked:"")."/> Friday  
	<input type=\"checkbox\" name=\"week[$id][6]\" value=\"1\" ".((($basis=="week")&&in_array("6",$days))?$checked:"")."/> Saturday 
	</span>
	<!-- monthly -->
	<span id=\"month_$id\" style=\"display:$month_display\">
	Days of month: <select name=\"month_day_start[$id]\">";
	for ($i=1;$i<=31;$i++) {
		if ($basis=="month"&&$i==$days[0]) $return .= "<option value=\"$i\" $selected>$i</option>";
		else $return .= "<option value=\"$i\">$i</option>";
	}
	$return .= "</select> through <select name=\"month_day_end[$id]\">";
	for ($i=1;$i<=31;$i++) {
		if ($basis=="month"&&$i==$days[1]) $return .= "<option value=\"$i\" $selected>$i</option>";
		else $return .= "<option value=\"$i\">$i</option>";
	}
	$return .= "</select>
	</span>
	<!-- yearly -->
	<span id=\"year_$id\" style=\"display:$year_display\">
	<select name=\"year_month_start[$id]\">
	<option value=\"1\" ".((($basis=="year")&&$start[0]=="1")?$selected:"").">January</option>
	<option value=\"2\" ".((($basis=="year")&&$start[0]=="2")?$selected:"").">February</option>
	<option value=\"3\" ".((($basis=="year")&&$start[0]=="3")?$selected:"").">March</option>
	<option value=\"4\" ".((($basis=="year")&&$start[0]=="4")?$selected:"").">April</option>
	<option value=\"5\" ".((($basis=="year")&&$start[0]=="5")?$selected:"").">May</option>
	<option value=\"6\" ".((($basis=="year")&&$start[0]=="6")?$selected:"").">June</option>
	<option value=\"7\" ".((($basis=="year")&&$start[0]=="7")?$selected:"").">July</option>
	<option value=\"8\" ".((($basis=="year")&&$start[0]=="8")?$selected:"").">August</option>
	<option value=\"9\" ".((($basis=="year")&&$start[0]=="9")?$selected:"").">September</option>
	<option value=\"10\" ".((($basis=="year")&&$start[0]=="10")?$selected:"").">October</option>
	<option value=\"11\" ".((($basis=="year")&&$start[0]=="11")?$selected:"").">November</option>
	<option value=\"12\" ".((($basis=="year")&&$start[0]=="12")?$selected:"").">December</option>
	</select>
	<select name=\"year_day_start[$id]\">";
	for ($i=1;$i<=31;$i++) {
		$return .= "<option value=\"$i\" ".((($basis=="year")&&$start[1]==$i)?$selected:"").">$i</option>";
	}
	$return .= "</select> through <select name=\"year_month_end[$id]\">
	<option value=\"1\" ".((($basis=="year")&&$end[0]=="1")?$selected:"").">January</option>
	<option value=\"2\" ".((($basis=="year")&&$end[0]=="2")?$selected:"").">February</option>
	<option value=\"3\" ".((($basis=="year")&&$end[0]=="3")?$selected:"").">March</option>
	<option value=\"4\" ".((($basis=="year")&&$end[0]=="4")?$selected:"").">April</option>
	<option value=\"5\" ".((($basis=="year")&&$end[0]=="5")?$selected:"").">May</option>
	<option value=\"6\" ".((($basis=="year")&&$end[0]=="6")?$selected:"").">June</option>
	<option value=\"7\" ".((($basis=="year")&&$end[0]=="7")?$selected:"").">July</option>
	<option value=\"8\" ".((($basis=="year")&&$end[0]=="8")?$selected:"").">August</option>
	<option value=\"9\" ".((($basis=="year")&&$end[0]=="9")?$selected:"").">September</option>
	<option value=\"10\" ".((($basis=="year")&&$end[0]=="10")?$selected:"").">October</option>
	<option value=\"11\" ".((($basis=="year")&&$end[0]=="11")?$selected:"").">November</option>
	<option value=\"12\" ".((($basis=="year")&&$end[0]=="12")?$selected:"").">December</option>
	</select>
	<select name=\"year_day_end[$id]\">";
	for ($i=1;$i<=31;$i++) {
		$return .= "<option value=\"$i\" ".((($basis=="year")&&$end[1]==$i)?$selected:"").">$i</option>";
	}
	$return .= "</select>
	</span>
	<!-- one time -->
	<span id=\"once_$id\" style=\"display:$once_display\">
	<select name=\"once_override[$id]\">
	<option value=\"0\" ".((($basis=="once")&&$parts[3]=="0")?$selected:"").">Add to Current Schedules</option>
	<option value=\"1\" ".((($basis=="once")&&$parts[3]=="1")?$selected:"").">Override All Schedules</option>
	</select> on 
	<select name=\"once_month[$id]\">
	<option value=\"1\" ".((($basis=="once")&&$parts[0]=="1")?$selected:"").">January</option>
	<option value=\"2\" ".((($basis=="once")&&$parts[0]=="2")?$selected:"").">February</option>
	<option value=\"3\" ".((($basis=="once")&&$parts[0]=="3")?$selected:"").">March</option>
	<option value=\"4\" ".((($basis=="once")&&$parts[0]=="4")?$selected:"").">April</option>
	<option value=\"5\" ".((($basis=="once")&&$parts[0]=="5")?$selected:"").">May</option>
	<option value=\"6\" ".((($basis=="once")&&$parts[0]=="6")?$selected:"").">June</option>
	<option value=\"7\" ".((($basis=="once")&&$parts[0]=="7")?$selected:"").">July</option>
	<option value=\"8\" ".((($basis=="once")&&$parts[0]=="8")?$selected:"").">August</option>
	<option value=\"9\" ".((($basis=="once")&&$parts[0]=="9")?$selected:"").">September</option>
	<option value=\"10\" ".((($basis=="once")&&$parts[0]=="10")?$selected:"").">October</option>
	<option value=\"11\" ".((($basis=="once")&&$parts[0]=="11")?$selected:"").">November</option>
	<option value=\"12\" ".((($basis=="once")&&$parts[0]=="12")?$selected:"").">December</option>
	</select>
	<select name=\"once_day[$id]\">";
	for ($i=1;$i<=31;$i++) {
		$return .= "<option value=\"$i\" ".((($basis=="once")&&$parts[1]==$i)?$selected:"").">$i</option>";
	}
	$return .= "</select>
	<select name=\"once_year[$id]\">";
	$year=date("Y");
	for ($i=($year-10);$i<=($year+25);$i++) {
		$return .= "<option value=\"$i\" ".(((($basis=="once")&&$parts[2]==$i)||($basis==""&&$year==$i))?$selected:"").">$i</option>";
	}
	$return .= "</select>
	</span>
	(<a href=\"javascript:void(0)\" onclick=\"delsch('{$id}'); return false;\">Delete</a>)</td></tr>";
	
	return $return;
}
?>
<script type="text/javascript">
<!--
window.schedules=0
window.onload=getkeys

function getkeys() {
	table = document.getElementById("schedules").getElementsByTagName("TBODY")[0];
	schedules=new Array()
	
	for (i=0;i<table.rows.length;i++) {
		name=table.rows[i].id.split("_")[1]
		schedules.push(name)
	}
	
	biggest=1
	
	for (i=0;i<schedules.length;i++) {
		if (parseInt(schedules[i]) > biggest) biggest = schedules[i]
	}
	
	window.schedules=biggest
}
function showbasis(elem, id) {
	value=elem.value
	bases=new Array()
	bases.push("week")
	bases.push("month")
	bases.push("year")
	bases.push("once")
	
	for (i=0;i<bases.length;i++) {
		document.getElementById(bases[i]+"_"+id).style.display="none"
	}
	
	if (document.getElementById(value+"_"+id) != undefined) document.getElementById(value+"_"+id).style.display="inline"
}
function delsch(id) {
	window.needToConfirm=true

	try {
		document.getElementById("sch_"+id).removeNode(true)
	} catch (e) {
		Node1 = document.getElementById("schedules").getElementsByTagName("TBODY")[0];
		var len = Node1.childNodes.length;
		
		for(var i = 0; i < len; i++) {
			if(Node1.childNodes[i].id == "sch_"+id) {
				Node1.removeChild(Node1.childNodes[i])
				break
			}
		}
	}
}
function addrow() {
	window.needToConfirm=true
	
	window.schedules++
	key=window.schedules
	content="<?php echo str_replace("<keygoeshere>","\"+key+\"", str_replace("\"","\\\"", str_replace("\n","",genContent("<keygoeshere>","","","")))); ?>"

	tbody = document.getElementById("schedules").getElementsByTagName("TBODY")[0];
	row = document.createElement("TR")
	row.id = "sch_"+key
	td1 = document.createElement("TD")
	td1.appendChild(document.createTextNode(""))
	row.appendChild(td1);
	tbody.appendChild(row);
	
	tbodyrows=tbody.rows
	for (i=0;i<tbodyrows.length;i++) {
		if (tbodyrows[i].id == "sch_"+key) {
			tbodyrows[i].cells[0].innerHTML = content
			break
		}
	}
}
// -->
</script>
<form action="schedules.php" method="post">
	<?php echo (isdisabled() == true)?"<b>WARNING:</b> the bell system is currently disabled. <br /><br />":""; ?>
	<b>Bell Schedules</b><?php echo $message; ?><br />
	
	<table style="margin-left:30px" id="schedules"><tbody>
	<?php
	foreach ($xml->children() as $child) {
		if ($child->getName()=="schedules") {
			foreach ($child->children() as $key=>$sch) {
				$id=$sch['id'];
				$basis=$sch['basis'];
				$exec = $sch['exec'];
				$when = (string)$sch;
				
				echo genContent($id,$basis,$exec,$when);
			}
		}
	}
	?>
	</tbody></table><a href="javascript:void(0);" onclick="addrow(); return false;" style="margin-left:30px">Add Another Schedule</a><br /><br />
	<input name="submitted" type="submit" value="Save" onclick="window.needToConfirm=false;" />
</form>
<?php
site_footer();
?>
