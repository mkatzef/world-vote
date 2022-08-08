"""
Moves staged tileset to active
Moves active to index 0 of a list of previously active tiles
Prints any tile IDs over the MAX_N_TILES_KEPT (one tile ID per line)
These excess tile IDs are removed from the database and are presumed deleted by
the caller
"""

import json
import sys
import datetime
import time

from db_common import DB_CNX

MAX_N_TILES_KEPT = 3

if __name__ == "__main__":
    cursor = DB_CNX.cursor()

    #cursor.execute('SELECT id, value FROM generals WHERE key = "active_tileset_id"')
    cursor.execute('SELECT id, pvalue FROM generals WHERE property = "active_tileset_id"')
    active_id_id, active_id_val = next(cursor)
    cursor.execute('SELECT id, pvalue FROM generals WHERE property = "pending_tileset_id"')
    pending_id_id, pending_id_val = next(cursor)

    timestamp = datetime.datetime.fromtimestamp(time.time()).strftime('%Y-%m-%d %H:%M:%S')
    cursor.execute('UPDATE generals SET pvalue = "%s", last_written = "%s" WHERE id = %d' % (pending_id_val, timestamp, active_id_id))

    cursor.execute('SELECT id, extra FROM generals WHERE property = "to_be_deleted_tileset_ids"')
    tbd_id, stale_ids = next(cursor)
    stale_ids = json.loads(stale_ids)  # should be a list
    stale_ids.insert(0, active_id_val)
    kept_for_now = stale_ids[:MAX_N_TILES_KEPT]
    deleted_now = stale_ids[MAX_N_TILES_KEPT:]

    for tile_id in deleted_now:
        print(tile_id)  # to be deleted

    cursor.execute("UPDATE generals SET extra = '%s' WHERE id = %d" % (json.dumps(kept_for_now), tbd_id))

    DB_CNX.commit()
    cursor.close()
    DB_CNX.close()
