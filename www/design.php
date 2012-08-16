<?php
$name        = "Bell System";
$root        = getcwd() . DIRECTORY_SEPARATOR;
$menu_file   = $root . "menu.xml";
$config_file = $root . "config.xml";
$password    = file_get_contents($root . ".password") or die("could not get password");

define("allow_save",  true); //for online web UI demo
define("max_hours",   23);
define("max_minutes", 59);
define("bell_session", "bell_system_2011");

$days_of_week = array(
	"Sunday",
	"Monday",
	"Tuesday",
	"Wednesday",
	"Thursday",
	"Friday",
	"Saturday"
);

function config_load()
{
	global $config_file;
	$xml = simplexml_load_file($config_file) or die("could not open config: $config_file");
	return $xml;
}

function config_save($xml)
{
	if (allow_save == true)
	{
		global $config_file;
		
		if ($f = fopen($config_file,"w"))
		{
			$return = fwrite($f, $xml->saveXML());
			fclose($f);
			return $return;
		} else  return false;
	}
}

function enabled() {
	$enabled = false;

	$xml = config_load();
	$start = "";
	$end   = "";

	foreach ($xml->children() as $child) {
		if ($child->getName() == "settings") {
			foreach ($child->children() as $setting) {
				if ($setting->getName() == "start")
					$start = $setting;
				else if ($setting->getName() == "end")
					$end   = $setting;
			}

			break;
		}
	}

	//YYYYMMDD
	if (strlen($start) == 8 && strlen($end) == 8)
	{
		$now = time();

		if ($now >= from_date($start) && $now <= from_date($end))
			$enabled = true;
	}

	return $enabled;
}

function from_date($string, $format="U")
{
	$len = strlen($string);

	if ($len == 8)
	{
		if (function_exists("date_create_from_format"))
		{
			return date_create_from_format("Ymd", $string)->format($format);
		}
		else
		{
			return date($format, strtotime($string));
		}
	}
	else if ($len == 12)
	{
		if (function_exists("date_create_from_format"))
		{
			return date_create_from_format("YmdHi", $string)->format($format);
		}
		else	
		{
			$date      = substr($string, 0, 8);
			$time_hour = substr($string, 8, 2);
			$time_min  = substr($string, 10, 2);
			return date($format, strtotime("$date $time_hour:$time_min"));
		}
	}
	else
	{
		return false;
	}
}

function jsDatePick($field)
{
	return <<<EOF
	new JsDatePick({
		useMode:2,
		target:"$field",
		dateFormat:"%Y/%m/%d",
		cellColorScheme:"beige",
		weekStartDay:0
	});

EOF;
}

function saved($bool)
{
	echo '<input type="submit" name="save" class="save" value="Save" onclick="window.needToConfirm=false" />';
	if ($bool) echo "<div class='saved' id='saved'>Successfully Saved</div>";
	else	   echo "<div class='saved' id='saved' style=\"display:none\">Successfully Saved</div>";
}

function time_select($str="", $hour=-1, $minute=-1) {
	echo "<select name=\"hour$str\" onchange=\"window.needToConfirm=true\">";
	
	for ($i=0; $i <= max_hours; ++$i) {
		echo "<option value=\"$i\"" . (($i==$hour)?" selected=\"selected\"":"") . ">$i</option>";
	}

	echo "</select> : <select name=\"minute$str\" onchange=\"window.needToConfirm=true\">";
	
	for ($i=0; $i <= max_minutes; ++$i) {
		$is = sprintf("%02d", $i);
		echo "<option value=\"$is\"" . (($i==$minute)?" selected=\"selected\"":"") . ">$is</option>";
	}

	echo "</select>";
}

function validSha($str) {
	return (strlen($str) == 64);
}

function encrypt($key, $text) {
	$result = array();
	$length = strlen($text);

	for ($i=0; $i<$length; ++$i) {
		$result[] = $key^ord($text[$i]);
	}

	return json_encode($result);
}

function decrypt($key, $text) {
	$result  = "";
	$encoded = json_decode($text);

	if ($encoded !== false) {
		$length  = count($encoded);

		for ($i=0; $i<$length; ++$i) {
			$result .= chr($key^$encoded[$i]);
		}

		return $result;
	} else {
		return false;
	}
}

function menu() {
global $menu_file;

$menu = "";
$xml = simplexml_load_file($menu_file) or die("could not get menu: $menu_file");
$links = count($xml);

foreach ($xml->children() as $child)
{
	if ($child->getName() == "break")
	{
		$menu .= "<br />\n";
	}
	else
	{
		$url  = $child["url"];
		$self = basename($_SERVER["PHP_SELF"]);

		//is this the current page?
		if ($url == $self)
			$menu .= "<a href='$url' id='current'>$child</a>";
		else
			$menu .= "<a href='$url'>$child</a>";
		
		$menu .= "\n";
	}
}

return $menu;
}

function site_header($title) {
global $name;
global $xml;

$status = (enabled())?"Enabled":"Disabled";
$device = $xml->settings->device;
$menu   = menu();

if ($title == "Home")
	$title = $name;
else
	$title = "$title - $name";

echo <<<EOF
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>$title</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
	<link type="text/css" rel="stylesheet" media="all" href="style.css" />
	<link type="text/css" rel="stylesheet" media="all" href="jsDatePick_ltr.min.css" />
	<link type="text/css" rel="stylesheet" media="all" href="fancybox/jquery.fancybox-1.3.4.css" />
	<script type="text/javascript" src="jsDatePick.min.1.3.js"></script>
	<script type="text/javascript" src="jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="jquery-ui-1.8.16.custom.min.js"></script>
	<script type="text/javascript" src="fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
	<script type="text/javascript" src="fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<script type="text/javascript">
	<!--
	window.needToConfirm = false;
	window.onbeforeunload = confirmExit;
	function confirmExit()
	{
		if (window.needToConfirm) return "Are you sure you want to leave this page before saving?"; 
	}
	// -->
	</script>
</head>
<body>

<div class="status">
<table>
	<tr><td><b>Device:</b></td><td><i>$device</i></td></tr>
	<tr><td><b>Status:</b></td><td>$status</td></tr>
</table>
</div>
<div class="title">$name</div>

<div class="menu">
$menu</div>

<div class="content">

EOF;
}

function site_footer() {
echo <<<EOF
</div>
</body>
</html>
EOF;
}

function login_form($note="") {
	if ($note!="")
		$note="<div style='color:#FF0000'>$note</div><br />\n";
?>
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login - Bell System</title>
<link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
<script type="text/javascript" src="json2.js"></script>
<script type="text/javascript" src="sha256.js"></script>
<script type="text/javascript">
function http(url, func) {
	var con
	try       { con=new XMLHttpRequest(); }
	catch (e) { try { con=new ActiveXObject("Msxml2.XMLHTTP"); }
	catch (e) { try { con=new ActiveXObject("Microsoft.XMLHTTP"); }
	catch (e) { alert("Your browser does not support AJAX!"); return false; } } }

	con.open("GET",url,true)
	con.send(null)

	con.onreadystatechange=function() {
		if (con.readyState == 4) {
			func(con.responseText)
		}
	}
}

function get(e) {
	return document.getElementById(e)
}

function focus(t) {
	var e = (typeof(t) == 'string')?get(t):t

	e.focus()
		setTimeout(function() {
		e.focus()
	}, 200)
}

function encrypt(key, text) {
	var result = new Array()

	for (i=0; i < text.length; ++i) {
		result.push(key^text.charCodeAt(i))
	}

	return "[" + result.join(",") + "]"
}

function login() {
	var replace  = get("replace")
	var password = Sha256.hash(get("pass").value+'\n')

	get("login").style.display   = "none"
	get("invalid").style.display = "none"
	replace.innerHTML = "Signing in..."
	get("pass").value = ""

	var url = "?login"

	http(url, function(text1) {
		var key  = JSON.parse(text1)
		password = encrypt(key, password)

		url += "&p="+password

		http(url, function(text2) {
			var loggedin = JSON.parse(text2)
			replace.innerHTML=""

			if (loggedin[0] == true) {
				window.location = 'index.php'
			} else {
				get("invalid").style.display = "inline"
				get("login").style.display   = "inline"
				focus("pass")
			}
		})
	})
}

window.onload = function() {
	focus("pass")
}
</script>
</head>
<body>
<h2>Bell System Login</h2>
<?php echo $note; ?>
<div id="replace"></div>
<div id="login">
<div style='color:#FF0000; display:none;' id='invalid'>Incorrect password.<br /></div>
<form action='index.php' method='post' onsubmit='login(); return false'>
Password: <input type='password' name='pass' id='pass' />
          <input type='submit' value='Login' />
</form>
</div>
</body>
</html>
<?php
	exit();
}

//get onto the password stuff...
session_start();

if (isset($_REQUEST['logout']))
{
	session_destroy();
	login_form("Successfully logged out.");
}
//non-js
else if (isset($_REQUEST['pass']))
{
	$input = $_REQUEST['pass'] . "\n";

	if (hash("sha256",$input) == trim($password))
		$_SESSION[bell_session] = true;
	else
		login_form("Incorrect password.");
}
//js version
else if (isset($_REQUEST['login']))
{
	if (isset($_REQUEST['p'])) {
		$pass = $_REQUEST['p'];
		$key  = $_SESSION[bell_session."_key"];
		unset($_SESSION[bell_session."_key"]);
		$pass = decrypt($key, $pass);

		if (!validSha($pass)) {
			echo "[false]";
		} else {
			if ($pass == $password) {
				echo "[true]";
				$_SESSION[bell_session] = true;
			} else {
				echo "[false]";
			}
		}
	} else {
		$key = rand();

		$_SESSION[bell_session."_key"] = $key;
		echo $key;
	}

	exit();
}
else if (!isset($_SESSION[bell_session]))
	login_form();

$xml = config_load();
?>
