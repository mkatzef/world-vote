#!/bin/bash
echo "Generating tiles for args [$1, $2]"
for i in `seq $1 $2`; do
  tippecanoe -z$i -Z$i -o stats/z0$i/tiles.mbtiles stats/z0$i/cells.json --force
done

echo "Combining"
tile-join -o stats/tiles-comb.mbtiles stats/z0*/tiles.mbtiles --force
