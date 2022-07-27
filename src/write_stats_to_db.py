

import mysql.connector
import numpy as np
import json
import os
import sys

from common import *

if len(sys.argv) < 2:
    print("Insufficient arguments, needs out_dir")
    sys.exit(1)

out_dir = sys.argv[-1]
if not os.path.exists(out_dir):
    try:
        os.makedirs(out_dir)
    except:
        print("Could not open or create given directory:", out_dir)


cnx = mysql.connector.connect(user='root', password='',
                              host='127.0.0.1',
                              database='world_vote')
cursor = cnx.cursor()

data = np.load(os.path.join(out_dir, "_counts.npy"), allow_pickle=True).tolist()

sql = "UPDATE prompts SET count_ratios = %s WHERE id = %s"
for p_id, counts in data.items():
    vals = (json.dumps(list(counts / counts.max())), str(p_id))
    cursor.execute(sql, vals)
cnx.commit()

cursor.close()
cnx.close()
