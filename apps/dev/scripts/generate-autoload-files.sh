#!/bin/sh
runpath=`pwd`
autoloadDir="autoload";

for dir in *; do
    if [[ $dir != $autoloadDir ]]; then
        dev/scripts/generate-autoload-file.sh $dir > "${dir}/${dir}_autoload.php";
    fi;
done;

mkdir -p $autoloadDir;
cd $autoloadDir;

for file in `find ../* -name "*_autoload.php"`; do
    ln -sfn $file .;
done;

cd $runpath;
