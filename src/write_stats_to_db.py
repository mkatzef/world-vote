import mysql.connector
import numpy as np
import json
import os
import sys

from common import *
from db_common import DB_CNX

out_dir = sys.argv[-1]
cursor = DB_CNX.cursor()

data = np.load(os.path.join(out_dir, "_counts.npy"), allow_pickle=True).tolist()
counts_raw = data['counts']  # pid: 2D array of vote counts (n_tags+1, MAX_VOTE_STEP + 1)
tag_key = data['tag_key']

def format_counts(c):
    """
    c is an individual vote count array
    (n_tags+1, MAX_VOTE_STEP + 1)
    """
    ret = {}
    for tag_i, tag_slug in [(0, 'all')]:  # Limiting to aggregate data; else: enumerate(tag_key):
        prompt_tag_counts = c[:, tag_i]
        prompt_tag_counts[prompt_tag_counts < THRESHOLD_COUNT] = 0  # Filter entried with low numbers
        divisor = prompt_tag_counts.max()
        if divisor == 0:
            divisor = 1
        ret[tag_slug] = list(prompt_tag_counts / divisor)
    return json.dumps(compress_for_json(ret, 2))

sql = "UPDATE prompts SET count_ratios = '%s' WHERE id = %s"
for p_id, counts in counts_raw.items():
    vals = (format_counts(counts), str(p_id))
    cursor.execute(sql % vals)
DB_CNX.commit()

cursor.close()
DB_CNX.close()
