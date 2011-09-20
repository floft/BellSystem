<?php
/*******************************************************************************
* login process
*******************************************************************************/

session_start();

//print_r($_SESSION);
//if (isset($_SiESSION['bell'])) echo "true";
//else echo "false";

$HOME = "index.php";
$configfile = "/home/bell/www/config.xml";
$_PHPSELF = $_SERVER['PHP_SELF'];
//$mycookie = md5($pass).sha1($pass);
$mycookie = "I need to redo this";

function startit() {
	echo "<html><head><title>SAA Bell System</title><script type=\"text/javascript\">
window.onload=function() { document.getElementById(\"pass\").focus(); }
</script></head><body>";
}

function exitit() {
	echo "</body></html>";
	exit;
}

if (isset($_SESSION['bell']) && !isset($_REQUEST['logout']) || (isset($daemon)&&$daemon==true))
{
	//continue;
}
else if (isset($_REQUEST['co']))
{
	if (md5($_REQUEST['co']).sha1($_REQUEST['co']) == $mycookie)
	{
		$_SESSION['bell'] = true;
		
		startit();
		echo "Logged in... <a href=\"$_PHPSELF\">Continue</a><script type=\"text/javascript\">
	<!--
		function relocate() {window.top.location=\"$_PHPSELF\";} setTimeout(\"relocate()\",500);
	// -->
	</script>";
		exitit();
	}
	else
	{
		startit();
		echo "<h2>Login - SAA Bell System</h2>Wrong password...<form action='$_PHPSELF' method='post'>Password: <input type='password' name='co' id=\"pass\" /> <input type='submit' value='Login' /></form>";
		exitit();
	}
}
else if (isset($_REQUEST['logout']))
{
	//unset($_SESSION['valid']);
	session_destroy();
	
	startit();
	echo "Logged out...<script type=\"text/javascript\">
	<!--
		function relocate() {window.top.location=\"$_PHPSELF\";} setTimeout(\"relocate()\",500);
	// -->
	</script>";
	exitit();
}
else
{
	startit();
	echo "<h2>Login - SAA Bell System</h2><form action='$_PHPSELF' method='post'>Password: <input type='password' name='co' id=\"pass\" /> <input type='submit' value='Login' /></form>";
	exitit();
}


/*******************************************************************************
* the actual design
*******************************************************************************/

function GetMenu($file) {
	global $_PHPSELF;
	$menu = "";
	$xml = simplexml_load_file($file);
	$links = count($xml);
	
	for ($i=0;$i<$links;$i++) {
		$node = $xml->item[$i];
		$url = $node["url"];
		
		//set the url to the root if it is "index"
		//if ($url == "index.php") $url = "./";
		
		//is this the current page?
		if ($url == basename(substr($_PHPSELF, 1))) $menu .= "<a href=\"" . $url . "\" id=\"current\">" . $node . "</a> ";
		else $menu .= "<a href=\"" . $url . "\">" . $node . "</a> ";
	}
	
	$menu = "<div class=\"contents\">
		<b>Bell System</b><br />
		$menu
		<br />
		<a href=\"index.php?logout\">Logout</a>
	</div>";
	
	return $menu;
}

function loadconfig()
{
	$defaults = <<<EOF
<?xml version="1.0" encoding="ISO-8859-1"?>
<bellsystem><settings><length>3</length><enable>1</enable><command><on></on><off></off></command></settings><schedules/><lists/></bellsystem>
EOF;
	global $configfile;
	if($xml = @simplexml_load_file("$configfile")) {
		return $xml;
	} else {
		$f=fopen("$configfile","w");
		fwrite($f,$defaults);
		fclose($f);
		$xml=simplexml_load_file("$configfile");
		return $xml;
	}
}
function saveconfig($xml)
{
	global $configfile;
	$return=null;
	$code=$xml->asXML();

	$f=fopen($configfile,"w");
	$return=fwrite($f,$code);
	fclose($f);

	return $return;
}

function isdisabled() {
	$xml=loadconfig();
	$disabled=false;
	
	foreach ($xml->children() as $child) {
		if ($child->getName()=="settings") {
			foreach ($child->children() as $setting) {
				if ($setting->getName() == "enable") {
					if ($setting=="0") $disabled=true;
				}
			}
		}
	}
	
	return $disabled;
}

function site_header($title)
{
	global $_PHPSELF;
	$prefix = "SAA Bell System";
	$title = ($title=="")?"$prefix":"$title - $prefix";
		
	$menu = GetMenu("menu.xml");

echo <<< END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
 <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en" dir="ltr">
 <head>
 	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 	<title>$title</title>
 	<style type="text/css">
 	div.left {float:left;font-size:80%;}
 	div.right {float:right;font-size:80%;}
 	div.contents {position:fixed;top:10px;right:10px;z-index:1;border:#FFA500 solid 2px;background-color:#FFE4B5;padding:10px;margin-left:10px;}
 	div.contents a {display:block;}
 	#current {font-style:italic;}
 	div.page {width:85%;}
 	img {padding:5px;border:0;}
 	</style>
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
	<h1>SAA Bell System</h1>
	
	<div class="page">
	$menu
END;
}

function site_footer() {
	global $_PHPSELF;
	$lastmod = date("D M d H:i:s T Y", getlastmod("$_PHPSELF"));
	
echo <<< END
	</div>
	
	<!--footer-->
	<hr />
	<p><a name="footer"></a></p>
	<div class="left">Copyright &copy; 2010 <a href="http://www.floft.net/">Floft</a> All Rights Reserved.</div>
	<div class="right"><a href="index.php">SAA Bell System</a> - Last Modified: $lastmod</div>
</body></html>
END;
}
?>
