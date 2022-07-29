import mysql.connector
import json
import sys

assert len(sys.argv) > 1, "expected args"
active = sys.argv[-1]

env = ".env-remote"
with open(env, 'r') as infile:
    cnx = mysql.connector.connect(**json.load(infile))

cursor = cnx.cursor()

cursor.execute('UPDATE tilesets SET is_active = 1 where mb_tile_id = "%s"' % active)
cursor.execute('UPDATE tilesets SET is_active = 0 WHERE mb_tile_id != "%s"' % active)
cnx.commit()

cursor.close()
cnx.close()
