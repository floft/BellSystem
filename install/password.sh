#!/bin/bash
dir=/usr/share/webapps/bellsystem
file="$dir/.password"

[[ ! -e $dir/design.php ]] && echo "Wrong directory." && exit 1

read -s password
php -r '$p=file_get_contents("php://stdin");echo hash("sha512",$p);' <<< "$password" > "$file"
