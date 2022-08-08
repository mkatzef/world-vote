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

echo "Moving pending to active"
pending_delete=`python3 promote_staged_tiles.py`
echo "Previously active: $pending_delete"

mkdir -p $out_dir

echo "Collecting base data from database"
python3 db_to_base.py --out_dir $out_dir

echo "Converting base data into geojson"
python3 base_to_geojson.py --out_dir $out_dir

echo "Generating tiles for zooms [$1, $2]"
for i in `seq $min_zoom $max_zoom`; do
  tippecanoe -z$i -Z$i -o $out_dir/z0$i/tiles.mbtiles $out_dir/z0$i/cells.json --force
done

echo "Combining tiles into single file"
tile-join -o $out_dir/$out_file $out_dir/z0*/tiles.mbtiles --force

echo "Writing stats to database"
python3 write_stats_to_db.py $out_dir

upload_name=`mapbox upload $out_dir/$out_file | python3 get_from_json.py tileset`
python3 set_staged_tiles.py $upload_name

echo "Deleting previously active tiles $pending_delete"
tilesets delete -f $pending_delete
