#!/bin/bash
slow_dir="./slow-"`./get_outdir.sh`
out_dir="./out-fast"
law_dir="./out_law"
min_zoom="0"
max_zoom="3"
out_file="tiles-comb.mbtiles"

git pull

if [ -f "access_token.txt" ]; then
  export MAPBOX_ACCESS_TOKEN=`cat access_token.txt`
else
  echo "No access token found for mapbox upload"
  exit
fi

echo "Moving pending to active, deleting any stale tilesets"
for pending_delete in `python3 promote_staged_tiles.py`; do
  echo "Deleting stale tiles: $pending_delete"
  tilesets delete -f $pending_delete
done

mkdir -p $out_dir

echo "Collecting base data FAST DELTA from database"
python3 db_to_base.py --out_dir $out_dir --user_src="`python3 get_user_src.py fast`"

echo "Adding slow dir $slow_dir to fast dir $out_dir"
python3 add_base_a_to_b.py --in_dir $slow_dir --out_dir $out_dir

echo "Converting base data into geojson"
python3 base_to_binned.py --in_dir $out_dir --out_dir $out_dir

echo "Converting binned data to geojson"
python3 binned_to_geojson.py --in_dir $out_dir --out_dir $out_dir

echo "Generating tiles for zooms [$min_zoom, $max_zoom]"
for i in `seq $min_zoom $max_zoom`; do
  tippecanoe -z$i -Z$i -o $out_dir/z0$i/tiles.mbtiles $out_dir/z0$i/cells.json --force
done

echo "Combining tiles into single file"
tile-join -o $out_dir/$out_file $out_dir/z0*/tiles.mbtiles --force

echo "Writing stats to database"
python3 write_stats_to_db.py $out_dir

upload_name=`python3 get_upload_name.py`
echo "Uploading new tileset: "$upload_name
mapbox upload $upload_name $out_dir/$out_file

echo "Recording these tiles as staged"
python3 set_staged_tiles.py $upload_name
