import json
import sys
import datetime
import time

from db_common import DB_CNX
from promote_staged_tiles import MAX_N_TILES_KEPT


if __name__ == "__main__":
    cursor = DB_CNX.cursor()

    cursor.execute('SELECT id, pvalue FROM generals WHERE property = "to_be_deleted_tileset_ids"')
    val_id, prev_tile_id = next(cursor)
    curr_tile_id = (int(prev_tile_id) + 1) % max(1, (MAX_N_TILES_KEPT + 1))
    print("mkatzeff.%s" % curr_tile_id)
    cursor.execute('UPDATE generals SET pvalue = "%d" WHERE id = %s' % (curr_tile_id, val_id))
    DB_CNX.commit()

    cursor.close()
    DB_CNX.close()
