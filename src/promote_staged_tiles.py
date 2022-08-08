import json
import sys
import datetime
import time

from db_common import DB_CNX

cursor = DB_CNX.cursor()

#cursor.execute('SELECT id, value FROM generals WHERE key = "active_tileset_id"')
cursor.execute('SELECT id, pvalue FROM generals WHERE property = "active_tileset_id"')
active_id_id, active_id_val = next(cursor)
cursor.execute('SELECT id, pvalue FROM generals WHERE property = "pending_tileset_id"')
pending_id_id, pending_id_val = next(cursor)

timestamp = datetime.datetime.fromtimestamp(time.time()).strftime('%Y-%m-%d %H:%M:%S')
cursor.execute('UPDATE generals SET pvalue = "%s", last_written = "%s" WHERE id = %d' % (pending_id_val, timestamp, active_id_id))
DB_CNX.commit()
print(active_id_val)  # to be deleted

cursor.close()
DB_CNX.close()
