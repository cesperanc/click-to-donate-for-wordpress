#/bin/bash
# 
# Compresses the javascript and CSS files with the .dev.* suffix
# @Author Cláudio Esperança <cesperanc@gmail.com>
#

for f in $(find $(pwd) -iname '*.dev.js' -or -iname '*.dev.css'); 
do
  filename=$(basename $f)
  extension=${filename##*.}
  type=${extension,,}
  dir="${f:0:${#f} - ${#filename}}"
  filename=${filename:0:${#filename} - (5+${#extension})}
  echo -n "Compressing '$f' to '$dir$filename.$extension'... " && java -jar $(dirname $0)/yuicompressor-*.jar $@ --charset=UTF-8 --type $type -o $dir$filename.$extension $f && echo "Done."
done
#read -p "Press [Enter] key to continue..."