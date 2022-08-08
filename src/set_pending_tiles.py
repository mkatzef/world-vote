import json
import sys
import datetime
import time

from db_common import DB_CNX

assert len(sys.argv) > 1, "expected args"
pending_id_val = sys.argv[-1]

cursor = DB_CNX.cursor()

timestamp = datetime.datetime.fromtimestamp(time.time()).strftime('%Y-%m-%d %H:%M:%S')
cursor.execute('UPDATE generals SET pvalue = "%s", last_written = "%s" WHERE property = "pending_tileset_id"' % (pending_id_val, timestamp))
DB_CNX.commit()

cursor.close()
DB_CNX.close()
