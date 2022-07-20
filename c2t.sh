#!/bin/bash
echo "Generating tiles for args [$1, $2]"
for i in `seq $1 $2`; do
  tippecanoe -z$i -Z$i -o data/tiles0$i.mbtiles data/cells0$i.json --force
done

echo "Combining"
tile-join -o data/tiles-comb.mbtiles data/tiles0*
