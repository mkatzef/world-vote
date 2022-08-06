import json
import sys

from db_common import DB_CNX

assert len(sys.argv) > 1, "expected args"
active = sys.argv[-1]

cursor = DB_CNX.cursor()

cursor.execute('UPDATE tilesets SET is_active = 1 where mb_tile_id = "%s"' % active)
cursor.execute('UPDATE tilesets SET is_active = 0 WHERE mb_tile_id != "%s"' % active)
DB_CNX.commit()

cursor.close()
DB_CNX.close()
