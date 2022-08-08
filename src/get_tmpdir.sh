
STATE_FILE="out_dir.state"
STATE_0="tick"
STATE_1="tock"

if ! [ -f $STATE_FILE ]; then
  echo $STATE_0 > $STATE_FILE
fi

state=`cat $STATE_FILE`
if [ "$state" == "$STATE_0" ]; then
  echo $STATE_1
elif [ "$state" == "$STATE_1" ]; then
  echo $STATE_0
else
  echo "UNEXPECTED VALUE IN STATE!"
fi

