#!/bin/bash
[[ ! -e design.php ]] && echo "Wrong directory." && exit 1

read -s password
php -r '$p=file_get_contents("php://stdin");echo hash("sha512",$p);' <<< "$password" > .password
