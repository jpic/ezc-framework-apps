#!/bin/sh

scanPath="."
if [[ $1 != "" ]]; then
    scanPath=$1;
fi;

echo "<?php";
echo "return array(";
echo `find $scanPath -name "*php"  | xargs grep "^class " | sed -e "s@\([^:]*\):class \([^ ]*\).*@    '\2' => '\1',\
@"`;
echo ");";
echo "?>";
