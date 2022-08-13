#!/bin/bash
out_dir="./slow-"`./get_tmpdir.sh`
min_zoom="0"
max_zoom="3"
out_file="tiles-comb.mbtiles"

if [ -f "access_token.txt" ]; then
  export MAPBOX_ACCESS_TOKEN=`cat access_token.txt`
else
  echo "No access token found for mapbox upload"
  exit
fi

echo "Working in "$out_dir
mkdir -p $out_dir

echo "Collecting base data from database"
python3 db_to_base.py --out_dir $out_dir --user_src="`python3 get_user_src.py slow`"

./toggle_outdir.sh
