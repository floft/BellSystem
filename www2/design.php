<?php
$name        = "Bell System";
$root        = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;
$menu_file   = $root . "menu.xml";
$config_file = $root . "config.xml";

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
	global $config_file;
	$dom = new DOMDocument('1.0');
	$dom->formatOutput = true;
	$dom->preserveWhiteSpace = false;
	$simple = dom_import_simplexml($xml);
	$simple = $dom->importNode($simple, true);
	$simple = $dom->appendChild($simple);
	
	if ($f = fopen($config_file,"w"))
	{
		$return = fwrite($f, $dom->saveXML());
		fclose($f);
		return $return;
	} else  return false;

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
		return date_create_from_format("Ymd", $string)->format($format);
	else if ($len == 12)
		return date_create_from_format("YmdHi", $string)->format($format);
	else
		return false;
}

function jsDatePick($field)
{
	return <<<EOF
	new JsDatePick({
		useMode:2,
		target:"$field",
		dateFormat:"%Y/%m/%d",
		cellColorScheme:"beige"
	});

EOF;
}

function saved($bool)
{
	echo '<input type="submit" name="save" class="save" value="Save" onclick="window.needToConfirm=false" />';
	if ($bool) echo "<div class='saved'>Successfully Saved</div>";
}

function menu() {
global $menu_file;

$menu = "";
$xml = simplexml_load_file($menu_file) or die("could not get menu: $menu_file");
$links = count($xml);

for ($i=0;$i<$links;$i++) {
	$node = $xml->page[$i];
	$url = str_replace("index.php", "", $node["url"]);

	//is this the current page?
	if ($node["url"] == $_SERVER["PHP_SELF"])
		$menu .= "<a href='$url' id='current'>$node</a>";
	else
		$menu .= "<a href='$url'>$node</a>";
	
	$menu .= "\r\n";
}

return $menu;
}

function site_header($title) {
global $name;

$status = (enabled())?"Enabled":"Disabled";
$ip     = $_SERVER["SERVER_ADDR"];
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
	<script type="text/javascript" src="jsDatePick.min.1.3.js"></script>
	<script type="text/javascript" src="jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="jquery-ui-1.8.16.custom.min.js"></script>
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
	<tr><td><b>IP:</b></td><td>$ip</td></tr>
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
?>
