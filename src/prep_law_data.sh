#!/bin/bash
law_dir="./out_law"
inlaw_dir="./law_data"
min_zoom="0"
max_zoom="3"
law_mb_outfile="lawtiles-combined.mbtiles"
regen_locs=0
if [ $1 ]; then
  regen_locs=$1
fi
n_workers=8

if (( $regen_locs )); then
  for w_i in `seq 0 $(( $n_workers - 1 ))`; do
    python3 loc_parser.py $n_workers $w_i &
  done
fi

mkdir -p $law_dir

echo "Collecting base data from database"
python3 law_to_base.py $inlaw_dir $law_dir

echo "Converting law base data into binned"
python3 base_to_binned.py --in_dir $law_dir --out_dir $law_dir

echo "Converting binned data to geojson"
python3 binned_to_geojson.py --in_dir $law_dir --out_dir $law_dir

echo "Generating tiles for zooms [$min_zoom, $max_zoom]"
for i in `seq $min_zoom $max_zoom`; do
  tippecanoe -z$i -Z$i -o $law_dir/z0$i/tiles.mbtiles $law_dir/z0$i/cells.json --force --layer="laws"
done

echo "Combining tiles into single file"
tile-join -o $law_dir/$law_mb_outfile $law_dir/z0*/tiles.mbtiles --force
