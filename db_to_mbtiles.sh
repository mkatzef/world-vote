#!/bin/bash
out_dir="./out"
min_zoom="0"
max_zoom="4"
out_file="tiles-comb.mbtiles"


if [ -f "access_token.txt" ]; then
  export MAPBOX_ACCESS_TOKEN=`cat access_token.txt`
else
  echo "No access token found for mapbox upload"
  exit
fi

last_upload=`cat dblbuf.txt`
upload_id="tick"
if [ "$last_upload" = "tick" ]; then
  upload_id='tock'
fi
echo "Uploading tileset to mapbox as mkatzeff.vote$upload_id"
mapbox upload mkatzeff.vote$upload_id $out_dir/$out_file

echo "Setting double buffer to $upload_id"
echo $upload_id > dblbuf.txt

if [ -f "web/.env" ]; then
  echo "Setting local .env to use $upload_id"
  sed -i .bak 's/vote$last_upload/vote$upload_id/' web/.env
fi

echo "TODO: set as active in database"
