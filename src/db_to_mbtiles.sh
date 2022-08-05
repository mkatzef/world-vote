#!/bin/bash
out_dir="./out"
min_zoom="0"
max_zoom="4"
out_file="tiles-comb.mbtiles"

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
