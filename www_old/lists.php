<?php
include_once "design.php";
site_header("Bell Lists");

$xml = loadconfig();

$message = "";

if (isset($_REQUEST['submitted'])) {
	$names = $_REQUEST['name'];
	$hours = (isset($_REQUEST['hour']))?$_REQUEST['hour']:null;
	$mins = (isset($_REQUEST['minute']))?$_REQUEST['minute']:null;
	
	if (($hours == null && $mins == null) || (is_array($hours) && is_array($mins) && count($hours) == count($mins))) {
		$config = array();
		
		$dom=dom_import_simplexml($xml->lists);
		$dom->parentNode->removeChild($dom);
		$xml->addChild("lists");
		
		foreach ($names as $key => $name) {
			$times = array();
			
			if (isset($hours[$key]) && $hours[$key] != null) {
				foreach ($hours[$key] as $hm_key => $hour) {
					$times[] = "{$hours[$key][$hm_key]}:{$mins[$key][$hm_key]}";
				}
			}
			
			//remove it...
			foreach ($xml->children() as $num => $child) {
				if ($child->getName()=="lists") {
					foreach ($child->children() as $list_num => $list) {
						if ($list["id"]== $key) {
							$dom=dom_import_simplexml($list);
							$dom->parentNode->removeChild($dom);
						}
					}
				}
			}
			
			//add the new one
			foreach ($xml->children() as $num => $child) {
				if ($child->getName()=="lists") {
					$newchild = $child->addChild("list");
					$newchild["id"] = $key;
					$newchild["name"] = addslashes(html_entity_decode($name));
					
					//add the new times
					foreach ($times as $time_key => $time) {
						$newtime = $newchild->addChild("time");
						$newchild->time[$time_key] = $time;
					}
				}
			}
		}
		
		saveconfig($xml);
		
		$message = " - <i>Settings Saved</i>";
	} else $message = " - <i>Error</i>";
}

$length="";
$port="";

$config = array();

//get the settings
foreach ($xml->children() as $child) {
	if ($child->getName()=="lists") {
		foreach ($child->children() as $key=>$list) {
			$id=$list['id'];
			$name=$list['name'];
			$times = array();
			
			foreach ($list->children() as $time) {
				$times[] = (string)$time;
			}
			
			$config[] = array($id,$name,$times);
		}
	}
}
?>
<script type="text/javascript">
<!--
window.keys=new Array()
window.lists=0
window.onload=getkeys

function getkeys() {
	tables=document.getElementsByTagName("table")
	lists = new Array()
	
	for (i=0;i<tables.length;i++) {
		name=tables[i].id.split("_")[1]
		lists.push(name)
		window.keys[name]=(tables[i].rows.length-1)
	}
	
	biggest=1
	
	for (i=0;i<lists.length;i++) {
		if (parseInt(lists[i]) > biggest) biggest = lists[i]
	}

	window.lists=biggest
}

function addrow(tbname) {
	window.needToConfirm=true
	
	window.keys[tbname]++
	key=window.keys[tbname]
	selectbox='<select name="hour['+tbname+']['+key+']"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option></select> : <select name="minute['+tbname+']['+key+']"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option></select> <a href=\"javascript:void(0)\" onclick=\"delrow(\''+tbname+'\',\''+key+'\'); return false;\">Delete</a>';
	
	tbody = document.getElementById("slist_"+tbname).getElementsByTagName("TBODY")[0];
	row = document.createElement("TR")
	row.id = "time_"+tbname+"_"+key
	td1 = document.createElement("TD")
	td1.appendChild(document.createTextNode(""))
	row.appendChild(td1);
	tbody.appendChild(row);
	
	tbodyrows=tbody.rows
	for (i=0;i<tbodyrows.length;i++) {
		if (tbodyrows[i].id == "time_"+tbname+"_"+key) {
			tbodyrows[i].cells[0].innerHTML = selectbox
			break
		}
	}
}

function delrow(tb,row) {
	window.needToConfirm=true
	
	try {
		document.getElementById("time_"+tb+"_"+row).removeNode(true)
	} catch (e) {
		Node1 = document.getElementById("slist_"+tb).getElementsByTagName("TBODY")[0];
		var len = Node1.childNodes.length;
		
		for(var i = 0; i < len; i++) {
			if(Node1.childNodes[i].id == "time_"+tb+"_"+row) {
				Node1.removeChild(Node1.childNodes[i])
				break
			}
		}
	}
}

function dellist(tb) {
	window.needToConfirm=true
	
	try {
		document.getElementById("slist_"+tb).removeNode(true)
	} catch (e) {
		Node1 = document.getElementById("lists");
		var len = Node1.childNodes.length;
		
		for(var i = 0; i < len; i++) {
			if(Node1.childNodes[i].id == "slist_"+tb) {
				Node1.removeChild(Node1.childNodes[i])
				break
			}
		}
	}
}

function addlist() {
	document.getElementById("savechanges").style.display="inline"
	window.needToConfirm=true
	
	window.lists++
	key=window.lists
	window.keys[key]=0
	
	document.getElementById("lists").innerHTML += "<span id=\"slist_"+key+"\"><p><b>"+key+".</b> Name of List: <input type=\"text\" name=\"name["+key+"]\" value=\"\" size=\"60\" /> <a href=\"javascript:void(0)\" onclick=\"dellist('"+key+"'); return false;\">Delete</a></p><table style=\"margin-left:30px\" id=\"list_"+key+"\"></p><table style=\"margin-left:30px\" id=\"list_"+key+"\"><tbody></tbody></table><a href=\"javascript:void(0)\" onclick=\"addrow('"+key+"'); return false;\" style=\"margin-left:30px\">Add Another Ring</a><br /><br /></span>"
}
// -->
</script>
<form action="lists.php" method="post">
	<?php echo (isdisabled() == true)?"<b>WARNING:</b> the bell system is currently disabled. <br /><br />":""; ?>
	<b>Bell Lists</b><?php echo $message; ?><br />
	<a href="javascript:void(0)" onclick="addlist(); return false;">Add Another List</a><span id="savechanges" style="display:none"> - <i>Please save your changes before adding another list.</i></span><br />
	
	<span id="lists">
	<?php
		foreach ($config as $list_key=>$list) {
			$id=$list[0];
			$name=$list[1];
			$times=$list[2];
			
			echo "<span id=\"slist_{$id}\"><p><b>{$id}.</b> Name of List: <input type=\"text\" name=\"name[$id]\" value=\"".htmlentities(stripslashes($name))."\" size=\"60\" /> <a href=\"javascript:void(0)\" onclick=\"dellist('{$id}'); return false;\">Delete</a></p><table style=\"margin-left:30px\" id=\"list_{$id}\"><tbody>";
			
			foreach ($times as $time_key=>$time) {
				$parts = explode(":", $time);
				$hour = $parts[0];
				$minute = $parts[1];
				
				echo "<tr id=\"time_{$id}_{$time_key}\"><td><select name=\"hour[$id][$time_key]\">";
					
				for ($i=0;$i<24;$i++) {
					if ($hour == $i) echo "<option value=\"$i\" selected=\"selected\">$i</option>";
					else echo "<option value=\"$i\">$i</option>";
				}
					
				echo "</select> : <select name=\"minute[$id][$time_key]\">";
				
				for ($i=0;$i<60;$i++) {
					if ($minute == $i) echo "<option value=\"$i\" selected=\"selected\">$i</option>";
					else echo "<option value=\"$i\">$i</option>";
				}
				
				echo "</select> <a href=\"javascript:void(0)\" onclick=\"delrow('{$id}','{$time_key}'); return false;\">Delete</a></td></tr>";
			}
				
			echo "</tbody></table><a href=\"javascript:void(0)\" onclick=\"addrow('{$id}'); return false;\" style=\"margin-left:30px\">Add Another Ring</a><br /><br /></span>";
		}
	?>
	</span>
	<input name="submitted" type="submit" value="Save" onclick="window.needToConfirm=false;" />
</form>
<?php
site_footer();
?>
