#!/bin/bash
out_dir="./out_debug"
extra_bases_dir="./law_base"
min_zoom="0"
max_zoom="3"
out_file="tiles-comb.mbtiles"

if [ -f "access_token.txt" ]; then
  export MAPBOX_ACCESS_TOKEN=`cat access_token.txt`
else
  echo "No access token found for mapbox upload"
  exit
fi

mkdir -p $out_dir

echo "Collecting base data from database"
python3 db_to_base.py --out_dir $out_dir

python3 add_base_a_to_b.py --in_dir $extra_bases_dir --out_dir $out_dir --missing_only

echo "Converting base data into geojson"
python3 base_to_binned.py --in_dir $out_dir --out_dir $out_dir
python3 binned_to_geojson.py --in_dir $out_dir --out_dir $out_dir

echo "Generating tiles for zooms [$1, $2]"
for i in `seq $min_zoom $max_zoom`; do
  tippecanoe -z$i -Z$i -o $out_dir/z0$i/tiles.mbtiles $out_dir/z0$i/cells.json --force
done

echo "Combining tiles into single file"
tile-join -o $out_dir/$out_file $out_dir/z0*/tiles.mbtiles --force
