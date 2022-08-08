STATE_FILE="out_dir.state"
STATE_0="tick"
STATE_1="tock"

if ! [ -f $STATE_FILE ]; then
  echo $STATE_0 > $STATE_FILE
fi

cat $STATE_FILE
