#!/bin/bash
out_dir="./out"
min_zoom="0"
max_zoom="4"
out_file="tiles-comb.mbtiles"

mkdir -p $out_dir

echo "Collecting base data from database"
python3 src/db_to_base.py $out_dir

echo "Converting base data into geojson"
python3 src/base_to_geojson.py $out_dir

echo "Generating tiles for zooms [$1, $2]"
for i in `seq $min_zoom $max_zoom`; do
  tippecanoe -z$i -Z$i -o $out_dir/z0$i/tiles.mbtiles $out_dir/z0$i/cells.json --force
done

echo "Combining tiles into single file"
tile-join -o $out_dir/$out_file $out_dir/z0*/tiles.mbtiles --force

echo "Writing stats to database"
python3 src/write_stats_to_db.py $out_dir

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

echo "Set as active in database"
python3 set_active_tiles.py mkatzeff.vote$upload_id
