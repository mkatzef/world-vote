#!/bin/bash
base_name="app"
num=131
srcdir="./web"
outdir="./web_uploads/"

while [ -f "$outdir$base_name$num.zip" ]; do
  num=$[ $num + 1 ];
done


echo "Changing to src directory"
cd $srcdir

echo "Writing zip..."
zip "../$outdir$base_name$num.zip" -r -xi * .platform/* .ebextensions/* -x "./vendor/*" -x "composer.lock"
echo "Finished writing to: $outdir$base_name$num.zip"
