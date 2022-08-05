

import mysql.connector
import numpy as np
import json
import os
import sys

from common import *
from db_common import DB_CNX

out_dir = "./out"
if not os.path.exists(out_dir):
    try:
        os.makedirs(out_dir)
    except:
        print("Could not open or create given directory:", out_dir)

cursor = DB_CNX.cursor()

data = np.load(os.path.join(out_dir, "_counts.npy"), allow_pickle=True).tolist()
counts_raw = data['counts']
tag_key = data['tag_key']

def format_counts(c):
    ret = {}
    for tag_i, tag_slug in enumerate(tag_key):
        divisor = c[:, tag_i].max()
        if divisor == 0:
            divisor = 1
        ret[tag_slug] = list(c[:, tag_i] / divisor)
    return json.dumps(ret)

sql = "UPDATE prompts SET count_ratios = '%s' WHERE id = %s"
for p_id, counts in counts_raw.items():
    vals = (format_counts(counts), str(p_id))
    cursor.execute(sql % vals)
DB_CNX.commit()

cursor.close()
DB_CNX.close()
