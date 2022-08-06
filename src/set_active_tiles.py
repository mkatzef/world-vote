import json
import sys
import datetime
import time

from db_common import DB_CNX

assert len(sys.argv) > 1, "expected args"
active = sys.argv[-1]

cursor = DB_CNX.cursor()

timestamp = datetime.datetime.fromtimestamp(time.time()).strftime('%Y-%m-%d %H:%M:%S')
cursor.execute('UPDATE tilesets SET is_active = 1 where mb_tile_id = "%s"' % active)
cursor.execute('UPDATE tilesets SET is_active = 0, last_written = "%s" WHERE mb_tile_id != "%s"' % (timestamp, active))
DB_CNX.commit()

cursor.close()
DB_CNX.close()
