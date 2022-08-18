#!/bin/bash
law_dir="./out_law"
inlaw_dir="./law_data"

mkdir -p $law_dir

echo "Collecting base data from database"
python3 law_to_base.py $inlaw_dir $law_dir

#echo "Converting law base data into binned"
python3 base_to_binned.py --in_dir $law_dir --out_dir $law_dir
