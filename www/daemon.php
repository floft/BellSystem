#!/usr/bin/env php
<?php
//this program should be run once a minute as root via the crontab
//note: root is required to turn on the bell

$daemon=true;
require_once "design.php";
$xml = loadconfig() or die("Failed to load config file!");

//should the bell ring?
$ring=false;

//some settings
$length=null;
$on=null;
$off=null;
$enabled=null;
$quiet=false;
$override=false;

//get the settings
foreach ($xml->children() as $child) {
	if ($child->getName()=="settings") {
		foreach ($child->children() as $setting) {
			if ($setting->getName() == "length") {
				$length = $setting;
			} else if ($setting->getName() == "command") {
				$on = $setting->on;
				$off = $setting->off;
			} else if ($setting->getName() == "enable") {
				if ($setting=="0") $enabled=false;
				else $enabled=true;
			}
		}
	}
}

$config=array();

//get the lists
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

function scan_schedules($exec) {
	global $config;

	$ring = false; //don't ring by default
	
	$now_hour=date("G"); //0-23 (24-hour, no leading zero)
	$now_min=date("i"); //00-59 (minutes, w/ leading zero)
	
	//get rid of the leading zeros in the $now_min variable
	if (strlen($now_min)==2 && substr($now_min,0,1)=="0") $now_min=substr($now_min,1,1);
	
	//go through each list seeing if there is a time listed equal to now
	foreach ($config as $list_key=>$list) {
		$id=$list[0];
		$name=$list[1];
		$times=$list[2];
		
		//is this the right list of bell rings?
		if ((int)$id==(int)$exec) {
			//loop through the list seeing if there is a bell that is supposed to ring now
			foreach ($times as $time_key=>$time) {
				$parts = explode(":", $time);
				$hour = $parts[0];
				$minute = $parts[1];
				
				if ($hour==$now_hour&&$minute==$now_min) {
					$ring=true;
					break 2;
				} /*else {
					echo "It is not $hour:$minute\n";
				}*/
			}
			
			//no need to continue, already checked all the times that the bell should ring
			break;
		}
	}
	
	return $ring;
}

//valid arguments?
if (ctype_digit((string)$length) && (int)$length > 0 && (int)$length < 30 && //check the length arg
    str_replace("rm ","",$on)==$on && str_replace("rm ","",$off)==$off && //make sure there isn't any "rm" command in the on/off args
    $enabled==true) //make sure the system is enabled
{
	//check whether or not there are any "override" schedules
	foreach ($xml->children() as $child) {
		if ($child->getName()=="schedules") {
			foreach ($child->children() as $key=>$sch) {
				$sched_id=$sch['id'];
				$basis=$sch['basis'];
				$exec = $sch['exec'];
				$when = (string)$sch;
				
				if ($basis=="once") {
					$today=date("n.j.Y").".1"; //month.day.year.override (e.g. 6.27.2010.1)
					//note: the .1 above is verifying that this is an overriding schedule... otherwise it doesn't matter
					
					if ($when==$today) {
						$override=true;
						if ($exec==0) { $quiet=true; break 2; } //is this a quiet period?
						else if($ring!=true) $ring=scan_schedules($exec); //see if the bell should ring now (if it isn't already supposed to ring)
					}
				}
			}
		}
	}
	
	//if there aren't any override schedules... proceed as normal
	if ($override==false) {
		foreach ($xml->children() as $child) {
			if ($child->getName()=="schedules") {
				foreach ($child->children() as $key=>$sch) {
					$sched_id=$sch['id'];
					$basis=$sch['basis'];
					$exec = $sch['exec'];
					$when = (string)$sch;
					
					if ($basis=="week") {
						$weeks=explode(",",$when);
						$today=date("N"); //1-7 (Monday-Sunday)
						
						//convert $today into Sunday-Saturday (not Monday-Sunday)
						if ($today==7) $today=0;
						
						foreach ($weeks as $week) {
							//is this the right day of the week?
							if ($today==$week) {
								if ($exec==0) { $quiet=true; break 3; } //is this a quiet period?
								else if($ring!=true) $ring=scan_schedules($exec); //see if the bell should ring now (if it isn't already supposed to ring)
								
								//$ring=scan_schedules($exec);
								//if ($ring==true) break 3;
							}
						}
					} else if ($basis=="month") {
						$today=date("j"); //1-31, day of the month
						$days=explode("-",$when);
						
						if (count($days)==2) {
							$start=$days[0];
							$end=$days[1];
							
							if ($today>=$start && $today<=$end) {
								if ($exec==0) { $quiet=true; break 2; } //is this a quiet period?
								else if($ring!=true) $ring=scan_schedules($exec); //see if the bell should ring now (if it isn't already supposed to ring)
								
								//$ring=scan_schedules($exec);
								//if ($ring==true) break 2;
							}
						}
					} else if ($basis=="year") {
						$today_month=date("n"); //1-12, month of year
						$today_day=date("j"); //1-31, day of the month
						
						$then=explode("-",$when);
						if (count($then)==2) {
							$then_start=explode(".",$then[0]);
							$then_end=explode(".",$then[1]);
							
							if (count($then_start)==2&&count($then_end)==2) {
								$then_start_month=$then_start[0];
								$then_start_day=$then_start[1];
								$then_end_month=$then_end[0];
								$then_end_day=$then_end[1];
								
								if (($today_month>$then_start_month&&$today_month<$then_end_month) ||  //if this is a month inbetween the start and end months
								   (($today_month==$then_start_month&&$today_month!=$then_end_month&&$today_day>=$then_start_day) ||  //if this is the start month, not the end month, and the date is after the start day
								    ($today_month!=$then_start_month&&$today_month==$then_end_month&&$today_day<=$then_end_day) || //if this is the end month, not the start mont, and the date is before the end day
								    ($today_month==$then_start_month&&$today_month==$then_end_month&&$today_day>=$then_start_day&&$today_day<=$then_end_day))) //if the start and end month are this month and today is in between the start and end days     
								{
									if ($exec==0) { $quiet=true; break 2; } //is this a quiet period?
									else if($ring!=true) $ring=scan_schedules($exec); //see if the bell should ring now (if it isn't already supposed to ring)
	
									//if ($ring==true) break 2;
								}
							}
						}
					} else if ($basis=="once") {
						$today=date("n.j.Y").".0"; //month.day.year.override (e.g. 6.27.2010.0)
						//note: the .0 is the non-overriding schedules... the overriding ones got taken care of before this point
						
						if ($when==$today) {
							if ($exec==0) { $quiet=true; break 2; } //is this a quiet period?
							else if($ring!=true) $ring=scan_schedules($exec); //see if the bell should ring now (if it isn't already supposed to ring)
							
							//$ring=scan_schedules($exec);
							//if ($ring==true) break 2;
						}
					} else {
						//basis not found!
					}
				}
			}
		}
	}
} else echo "Invalid settings! Maybe you have an 'rm' in your on/off commands. =)";

//ring if it is supposed to be
if ($ring==true&&$quiet==false) {
	system($on);
	sleep((int)$length);
	system($off);
	echo "Yes\n";
}
else {
 echo "No\n";
 echo "len: $length; ".(($ring)?"true":"false")."; ".(($quiet)?"true":"false");
}
?>
