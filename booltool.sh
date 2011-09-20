#!/bin/bash
#INFO: This script will allow you to turn on and off a usb device.

device=""
action=""
port=""
app="/home/bell/turnonrelay"

while [ "$1" != "" ]; do
	case $1 in
		"-a") shift
			action="$1"
			;;
		"-d") shift
			device="$1"
			;;
		"--toggle") toggle=1
			;;
		"--list")
			for i in $(ls /dev/ | grep ttyS); do
				echo $i
			done
			
			exit 0
			;;
		*)
			echo "Usage:"
			echo -e "\t$0  --list"
			echo -e "\t$0 -a on/off/status -d \"device name\""
			exit 0
			;;
	esac
	shift
done

if [ "$device" == "" ]; then
	echo "Error: no specified device! Use the --list option to list devices."
	exit 1
fi

if [ "$action" != "on" ] && [ "$action" != "off" ] && [ "$action" != "status" ] &&  [ "$toggle" != "1" ]; then
	echo "Error: possible values for action (-a) option: on | off | status"
	exit 1
fi

if [ ! -e "/dev/$device" ]; then
	echo "Error: \"$device\" does not exist!"
else
	port="$device"
fi

function off() {
	killall -s 9 $app
}

function on() {
	if [ "$(ps -ef | grep -v grep | grep turnonrelay)" == "" ]; then
		$app /dev/$1 on >/dev/null 2>&1 &
	fi
}

if [ "$port" == "" ]; then
	echo "Error: \"$device\" not found!"
else
	if [ "$(id -u)" != "0" ] && [ "$action" != "status" ]; then
		echo "Error: not run as root!"
	else
		if [ "$action" == "on" ]; then
			on $port
		elif [ "$action" == "off" ]; then
			off $port
		elif [ "$action" == "status" ]; then
			if [ "$(ps -ef | grep -v grep | grep $app)" != "" ]; then
				echo "on"
			else
				echo "off"
			fi
		elif [ "$toggle" == "1" ]; then
			if [ "$(ps -ef | grep -v grep | grep $app)" != "" ]; then
				off $port
			else
				on $port
			fi
		fi
	fi
fi
