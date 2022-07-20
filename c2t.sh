#!/bin/bash
echo "Generating tiles for args [$1, $2]"
for i in `seq $1 $2`; do
  tippecanoe -z$i -Z$i -o stats/tiles0$i.mbtiles stats/z0$i/cells.json --force
done

echo "Combining"
tile-join -o stats/tiles-comb.mbtiles stats/tiles0* --force
