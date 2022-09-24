import json
import sys
import datetime
import time

from db_common import DB_CNX

assert len(sys.argv) > 1, "expected args"
update_loop_speed = sys.argv[-1]

cursor = DB_CNX.cursor()

response = ""
if update_loop_speed == "slow":
    # "slow" for full and update most recent full
    cursor.execute("SELECT MAX(id) FROM users")
    last_daily_user_id = next(cursor)

    cursor.execute('UPDATE generals SET pvalue = "%d" WHERE property = "max_baseset_user_id"' % last_daily_user_id)
    DB_CNX.commit()

    response = 'users WHERE id <= %d' % last_daily_user_id
elif update_loop_speed == "fast":
    #"fast" for everything since most recent full
    cursor.execute('SELECT pvalue FROM generals WHERE property = "max_baseset_user_id"')
    last_daily_user_id = next(cursor)
    response = 'users WHERE id > %s' % last_daily_user_id

print(response)

cursor.close()
DB_CNX.close()
